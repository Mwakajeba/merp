<?php

namespace App\Http\Controllers\Milipuko;

use App\Http\Controllers\Controller;
use App\Models\Milipuko\Duara;
use App\Models\Milipuko\Kibali;
use App\Models\Milipuko\Mlipuzi;
use App\Models\Milipuko\Msimamizi;
use App\Services\SystemSettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use TCPDF2DBarcode;

class VibaliController extends Controller
{
    public function index()
    {
        $vibali = Kibali::forCompany(auth()->user()->company_id)
            ->with(['duara', 'mlipuzi.mwenyeBc'])
            ->withCount('wachorongaji')
            ->latest()
            ->get();

        return view('milipuko.vibali.index', compact('vibali'));
    }

    public function create()
    {
        return view('milipuko.vibali.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $companyId = auth()->user()->company_id;

        $kibali = DB::transaction(function () use ($data, $companyId) {
            $max = DB::table('vibali')
                ->where('company_id', $companyId)
                ->lockForUpdate()
                ->selectRaw('MAX(CAST(namba AS UNSIGNED)) as namba_kubwa')
                ->first()
                ?->namba_kubwa;

            $kibali = Kibali::create([
                'company_id' => $companyId,
                'branch_id' => session('branch_id') ?: auth()->user()->branch_id,
                'namba' => str_pad((string) (((int) $max) + 1), 4, '0', STR_PAD_LEFT),
                'duara_id' => $data['duara_id'],
                'idadi_ya_matundu' => $data['idadi_ya_matundu'],
                'bc_no' => $data['bc_no'],
                'tarehe' => $data['tarehe'],
                'msimamizi_id' => $data['msimamizi_id'],
                'mlipuzi_id' => $data['mlipuzi_id'],
                'aina_ya_mlipuko' => $data['aina_ya_mlipuko'],
                'msimamizi_wa_idara' => $data['msimamizi_wa_idara'],
                'katibu' => $data['katibu'],
                'hali' => $data['hali'],
                'created_by' => auth()->id(),
            ]);

            $this->syncWachorongaji($kibali, $data['wachorongaji']);

            return $kibali;
        });

        return redirect()
            ->route('milipuko.vibali.show', $kibali)
            ->with('success', 'Kibali kimesajiliwa.');
    }

    public function show(Kibali $kibali)
    {
        $kibali->load(['company', 'duara', 'msimamizi', 'mlipuzi.mwenyeBc', 'wachorongaji']);

        return view('milipuko.vibali.show', [
            'kibali' => $kibali,
            'qr' => $this->qrDataUri($kibali->thibitishoUrl()),
        ]);
    }

    public function chapisha(Kibali $kibali)
    {
        $kibali->load(['company', 'duara', 'msimamizi', 'mlipuzi.mwenyeBc', 'wachorongaji']);

        return view('milipuko.vibali.chapisha', [
            'kibali' => $kibali,
            'qr' => $this->qrDataUri($kibali->thibitishoUrl()),
        ]);
    }

    public function thibitisha(string $token)
    {
        $imefungwaSasa = false;

        $kibali = DB::transaction(function () use ($token, &$imefungwaSasa) {
            $kibali = Kibali::where('uthibitisho_token', $token)->lockForUpdate()->first();

            if (! $kibali) {
                return null;
            }

            if ($kibali->imefungwa_at === null) {
                $kibali->imefungwa_at = now();
                $kibali->save();
                $imefungwaSasa = true;
            }

            return $kibali;
        });

        $kibali?->load(['company', 'duara', 'msimamizi', 'mlipuzi.mwenyeBc', 'wachorongaji']);

        return view('milipuko.vibali.thibitisha', [
            'kibali' => $kibali,
            'imefungwaSasa' => $imefungwaSasa,
            'appName' => SystemSettingService::get('app_name', 'M-ERP'),
        ]);
    }

    public function edit(Kibali $kibali)
    {
        if ($redirect = $this->kataKibaliKilichofungwa($kibali)) {
            return $redirect;
        }

        $kibali->load(['msimamizi', 'wachorongaji']);

        return view('milipuko.vibali.edit', array_merge(
            $this->formData($kibali),
            compact('kibali')
        ));
    }

    public function update(Request $request, Kibali $kibali)
    {
        if ($redirect = $this->kataKibaliKilichofungwa($kibali)) {
            return $redirect;
        }

        $data = $this->validated($request, $kibali);

        DB::transaction(function () use ($kibali, $data) {
            $kibali->update([
                'duara_id' => $data['duara_id'],
                'idadi_ya_matundu' => $data['idadi_ya_matundu'],
                'bc_no' => $data['bc_no'],
                'tarehe' => $data['tarehe'],
                'msimamizi_id' => $data['msimamizi_id'],
                'mlipuzi_id' => $data['mlipuzi_id'],
                'aina_ya_mlipuko' => $data['aina_ya_mlipuko'],
                'msimamizi_wa_idara' => $data['msimamizi_wa_idara'],
                'katibu' => $data['katibu'],
                'hali' => $data['hali'],
            ]);

            $this->syncWachorongaji($kibali, $data['wachorongaji']);
        });

        return redirect()
            ->route('milipuko.vibali.show', $kibali)
            ->with('success', 'Kibali kimehaririwa.');
    }

    public function destroy(Kibali $kibali)
    {
        $kibali->delete();

        return redirect()
            ->route('milipuko.vibali.index')
            ->with('success', 'Kibali kimefutwa.');
    }

    private function formData(?Kibali $kibali = null): array
    {
        $companyId = auth()->user()->company_id;

        $maduara = Duara::forCompany($companyId)
            ->with('wasimamizi')
            ->orderBy('namba')
            ->get();

        if ($kibali && $kibali->msimamizi && $kibali->duara_id) {
            $duara = $maduara->firstWhere('id', $kibali->duara_id);
            if ($duara && ! $duara->wasimamizi->contains('id', $kibali->msimamizi_id)) {
                $duara->wasimamizi->push($kibali->msimamizi);
            }
        }

        $walipuaji = Mlipuzi::forCompany($companyId)
            ->with('mwenyeBc')
            ->where('hali', 'active')
            ->orderBy('jina')
            ->get();

        if ($kibali) {
            $zilizopo = collect([$kibali->mlipuzi_id])
                ->merge($kibali->wachorongaji->pluck('id'))
                ->filter()
                ->unique()
                ->diff($walipuaji->pluck('id'));

            if ($zilizopo->isNotEmpty()) {
                $zaZiada = Mlipuzi::forCompany($companyId)
                    ->with('mwenyeBc')
                    ->whereIn('id', $zilizopo)
                    ->get();
                $walipuaji = $walipuaji->concat($zaZiada)->sortBy('jina')->values();
            }
        }

        return [
            'company' => auth()->user()->company,
            'maduara' => $maduara,
            'walipuaji' => $walipuaji,
        ];
    }

    private function validated(Request $request, ?Kibali $kibali = null): array
    {
        $validated = $request->validate([
            'hali' => ['required', Rule::in(['uzalishaji', 'ufreshiaji', 'ufukuziaji'])],
            'duara_id' => ['required', 'integer'],
            'msimamizi_id' => ['required', 'integer'],
            'idadi_ya_matundu' => ['required', 'integer', 'min:0', 'max:100000'],
            'tarehe' => ['required', 'date'],
            'mlipuzi_id' => ['required', 'integer'],
            'wachorongaji' => ['nullable', 'array'],
            'wachorongaji.*' => ['nullable', 'integer'],
            'aina_ya_mlipuko' => ['required', Rule::in(['cotex', 'dull_fuse'])],
            'msimamizi_wa_idara' => ['required', 'string', 'max:255'],
        ], [
            'hali.required' => 'Hali ya kibali inahitajika.',
            'hali.in' => 'Hali ni Uzalishaji, Ufreshiaji au Ufukuziaji.',
            'duara_id.required' => 'Duara No. inahitajika.',
            'msimamizi_id.required' => 'Jina la msimamizi wa duara linahitajika.',
            'idadi_ya_matundu.required' => 'Idadi ya matundu inahitajika.',
            'idadi_ya_matundu.integer' => 'Idadi ya matundu lazima iwe namba.',
            'tarehe.required' => 'Tarehe inahitajika.',
            'mlipuzi_id.required' => 'Jina la mlipuaji linahitajika.',
            'aina_ya_mlipuko.required' => 'Chagua aina ya mlipuko: COTEX au DULL FUSE.',
            'aina_ya_mlipuko.in' => 'Aina ya mlipuko ni COTEX au DULL FUSE.',
            'msimamizi_wa_idara.required' => 'Jina la msimamizi wa idara linahitajika.',
        ]);

        $katibu = trim((string) auth()->user()->name);
        if ($katibu === '') {
            throw ValidationException::withMessages([
                'katibu' => 'Jina la mtumiaji aliyeingia halipatikani.',
            ]);
        }

        $companyId = auth()->user()->company_id;
        $duara = Duara::forCompany($companyId)->with('wasimamizi')->find($validated['duara_id']);

        if (! $duara) {
            throw ValidationException::withMessages([
                'duara_id' => 'Chagua duara lililosajiliwa.',
            ]);
        }

        $msimamizi = Msimamizi::where('company_id', $companyId)->find($validated['msimamizi_id']);
        $niMsimamiziWaDuara = $msimamizi && $duara->wasimamizi->contains('id', $msimamizi->id);
        $niMsimamiziWaKibali = $kibali
            && $msimamizi
            && (int) $kibali->duara_id === (int) $duara->id
            && (int) $kibali->msimamizi_id === (int) $msimamizi->id;

        if (! $niMsimamiziWaDuara && ! $niMsimamiziWaKibali) {
            throw ValidationException::withMessages([
                'msimamizi_id' => 'Chagua msimamizi wa duara hili.',
            ]);
        }

        $mlipuzi = Mlipuzi::forCompany($companyId)->with('mwenyeBc')->find($validated['mlipuzi_id']);
        $niMlipuziWaKibali = $kibali && $mlipuzi && (int) $kibali->mlipuzi_id === (int) $mlipuzi->id;

        if (! $mlipuzi || ($mlipuzi->hali === 'blocked' && ! $niMlipuziWaKibali)) {
            throw ValidationException::withMessages([
                'mlipuzi_id' => 'Chagua mlipuaji ambaye hajafungwa.',
            ]);
        }

        return [
            'hali' => $validated['hali'],
            'duara_id' => $duara->id,
            'msimamizi_id' => $msimamizi->id,
            'idadi_ya_matundu' => (int) $validated['idadi_ya_matundu'],
            'bc_no' => $mlipuzi->bcInayotumika(),
            'tarehe' => $validated['tarehe'],
            'mlipuzi_id' => $mlipuzi->id,
            'aina_ya_mlipuko' => $validated['aina_ya_mlipuko'],
            'msimamizi_wa_idara' => trim($validated['msimamizi_wa_idara']),
            'katibu' => $katibu,
            'wachorongaji' => $this->wachorongajiWaliokubaliwa($request, $kibali),
        ];
    }

    /**
     * @return array<int, int>
     */
    private function wachorongajiWaliokubaliwa(Request $request, ?Kibali $kibali): array
    {
        $companyId = auth()->user()->company_id;
        $waliopo = $kibali ? $kibali->wachorongaji->pluck('id')->map(fn ($id) => (int) $id)->all() : [];
        $safu = [];
        $seen = [];

        foreach ($request->input('wachorongaji', []) as $nafasi => $id) {
            if ($id === null || $id === '') {
                continue;
            }

            $nafasi = (int) $nafasi;
            if ($nafasi < 1 || $nafasi > 5) {
                continue;
            }

            $mtu = Mlipuzi::forCompany($companyId)->find($id);
            $alikuwa = $mtu && in_array((int) $mtu->id, $waliopo, true);

            if (! $mtu || ($mtu->hali !== 'active' && ! $alikuwa)) {
                throw ValidationException::withMessages([
                    "wachorongaji.$nafasi" => 'Chagua mchorongaji aliye active.',
                ]);
            }

            if (isset($seen[$mtu->id])) {
                throw ValidationException::withMessages([
                    "wachorongaji.$nafasi" => 'Mchorongaji amerudiwa kwenye kibali hiki.',
                ]);
            }

            $seen[$mtu->id] = true;
            $safu[$nafasi] = $mtu->id;
        }

        ksort($safu);

        return $safu;
    }

    private function kataKibaliKilichofungwa(Kibali $kibali)
    {
        if (! $kibali->imefungwa()) {
            return null;
        }

        return redirect()
            ->route('milipuko.vibali.show', $kibali)
            ->with('error', 'Kibali hiki kimefungwa baada ya ukaguzi. Hakihaririwi tena.');
    }

    private function qrDataUri(string $url): string
    {
        $barcode = new TCPDF2DBarcode($url, 'QRCODE,H');
        $png = $barcode->getBarcodePngData(5, 5, [0, 0, 0]);

        return 'data:image/png;base64,'.base64_encode($png);
    }

    private function syncWachorongaji(Kibali $kibali, array $wachorongaji): void
    {
        $kibali->wachorongaji()->detach();

        foreach ($wachorongaji as $nafasi => $mlipuziId) {
            $kibali->wachorongaji()->attach($mlipuziId, ['nafasi' => $nafasi]);
        }
    }
}
