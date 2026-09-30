<?php

namespace App\Http\Controllers\Milipuko;

use App\Http\Controllers\Controller;
use App\Models\Milipuko\Kibali;
use App\Models\Milipuko\Kosa;
use App\Models\Milipuko\Mlipuzi;
use App\Models\Milipuko\MlipuziKosa;
use App\Services\SystemSettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use TCPDF2DBarcode;

class WalipuajiController extends Controller
{
    public function index()
    {
        $hali = request('hali');
        if (! in_array($hali, ['active', 'blocked'], true)) {
            $hali = null;
        }

        $walipuaji = Mlipuzi::forCompany(auth()->user()->company_id)
            ->when($hali, fn ($query) => $query->where('hali', $hali))
            ->with('mwenyeBc')
            ->withCount('wawasiliani')
            ->latest()
            ->get();

        return view('milipuko.walipuaji.index', compact('walipuaji', 'hali'));
    }

    public function create()
    {
        return view('milipuko.walipuaji.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $picha = $this->storePicha($request);

        try {
            $mlipuzi = DB::transaction(function () use ($data, $picha) {
                $mlipuzi = Mlipuzi::create([
                    'company_id' => auth()->user()->company_id,
                    'branch_id' => session('branch_id') ?: auth()->user()->branch_id,
                    'jina' => $data['jina'],
                    'hali' => $data['hali'],
                    'aina_ya_bc' => $data['aina_ya_bc'],
                    'bc_no' => $data['bc_no'],
                    'bc_ya_mlipuzi_id' => $data['bc_ya_mlipuzi_id'],
                    'simu' => $data['simu'],
                    'simu_mbadala' => $data['simu_mbadala'],
                    'picha' => $picha,
                    'mkoa' => $data['mkoa'],
                    'wilaya' => $data['wilaya'],
                    'eneo' => $data['eneo'],
                    'created_by' => auth()->id(),
                ]);

                $this->syncWawasiliani($mlipuzi, $data['wawasiliani']);

                return $mlipuzi;
            });
        } catch (\Throwable $exception) {
            $this->deletePicha($picha);
            throw $exception;
        }

        return redirect()
            ->route('milipuko.walipuaji.show', $mlipuzi)
            ->with('success', 'Mlipuaji amesajiliwa.');
    }

    public function show(Mlipuzi $mlipuzi)
    {
        $mlipuzi->load([
            'wawasiliani',
            'makosa.kosa',
            'mwenyeBc',
            'wanaotumiaBcYake' => fn ($query) => $query->orderBy('jina'),
        ]);

        return view('milipuko.walipuaji.show', [
            'mlipuzi' => $mlipuzi,
            'vibali' => $mlipuzi->vibaliAlivyohusika(),
        ]);
    }

    public function edit(Mlipuzi $mlipuzi)
    {
        $mlipuzi->load('wawasiliani');

        return view('milipuko.walipuaji.edit', array_merge(
            compact('mlipuzi'),
            $this->formData($mlipuzi)
        ));
    }

    public function update(Request $request, Mlipuzi $mlipuzi)
    {
        $data = $this->validated($request, $mlipuzi);
        $pichaMpya = $this->storePicha($request);

        try {
            DB::transaction(function () use ($mlipuzi, $data, $pichaMpya) {
                $pichaYaZamani = $mlipuzi->picha;

                $mlipuzi->update([
                    'jina' => $data['jina'],
                    'hali' => $data['hali'],
                    'aina_ya_bc' => $data['aina_ya_bc'],
                    'bc_no' => $data['bc_no'],
                    'bc_ya_mlipuzi_id' => $data['bc_ya_mlipuzi_id'],
                    'simu' => $data['simu'],
                    'simu_mbadala' => $data['simu_mbadala'],
                    'picha' => $pichaMpya ?: $mlipuzi->picha,
                    'mkoa' => $data['mkoa'],
                    'wilaya' => $data['wilaya'],
                    'eneo' => $data['eneo'],
                ]);

                $this->syncWawasiliani($mlipuzi, $data['wawasiliani']);

                if ($pichaMpya && $pichaYaZamani) {
                    $this->deletePicha($pichaYaZamani);
                }
            });
        } catch (\Throwable $exception) {
            $this->deletePicha($pichaMpya);
            throw $exception;
        }

        return redirect()
            ->route('milipuko.walipuaji.show', $mlipuzi)
            ->with('success', 'Taarifa za mlipuaji zimehifadhiwa.');
    }

    public function kitambulisho(Mlipuzi $mlipuzi)
    {
        $mlipuzi->load(['company', 'mwenyeBc']);
        $url = $mlipuzi->thibitishoUrl();

        return view('milipuko.walipuaji.kitambulisho', [
            'mlipuzi' => $mlipuzi,
            'thibitishoUrl' => $url,
            'qr' => $this->qrDataUri($url),
            'appName' => SystemSettingService::get('app_name', 'M-ERP'),
        ]);
    }

    public function thibitisha(string $token)
    {
        $mlipuzi = Mlipuzi::with(['company', 'mwenyeBc'])
            ->where('uthibitisho_token', $token)
            ->first();

        if (! $mlipuzi) {
            abort(404);
        }

        return view('milipuko.walipuaji.thibitisha', [
            'mlipuzi' => $mlipuzi,
            'appName' => SystemSettingService::get('app_name', 'M-ERP'),
        ]);
    }

    public function createKosa(Mlipuzi $mlipuzi)
    {
        return view('milipuko.walipuaji.makosa.form', [
            'mlipuzi' => $mlipuzi,
            'rekodi' => null,
            'makosaYaliyopo' => $this->makosaYaliyopo(),
        ]);
    }

    public function storeKosa(Request $request, Mlipuzi $mlipuzi)
    {
        $data = $this->validatedKosa($request);
        $barua = $request->file('barua')?->store('walipuaji/barua', 'public');

        try {
            $kosa = Kosa::updateOrCreate(
                ['company_id' => $mlipuzi->company_id, 'kosa' => $data['kosa']],
                []
            );

            $mlipuzi->makosa()->create([
                'kosa_id' => $kosa->id,
                'maelezo_ya_adhabu' => $data['maelezo_ya_adhabu'],
                'hali' => $data['hali'],
                'barua' => $barua,
            ]);
        } catch (\Throwable $exception) {
            $this->deletePicha($barua);
            throw $exception;
        }

        return redirect()
            ->route('milipuko.walipuaji.show', $mlipuzi)
            ->with('success', 'Kosa limeongezwa.');
    }

    public function editKosa(Mlipuzi $mlipuzi, int $rekodi)
    {
        $rekodi = $this->findRekodi($mlipuzi, $rekodi);

        return view('milipuko.walipuaji.makosa.form', [
            'mlipuzi' => $mlipuzi,
            'rekodi' => $rekodi,
            'makosaYaliyopo' => $this->makosaYaliyopo(),
        ]);
    }

    public function updateKosa(Request $request, Mlipuzi $mlipuzi, int $rekodi)
    {
        $rekodi = $this->findRekodi($mlipuzi, $rekodi);
        $data = $this->validatedKosa($request);
        $baruaMpya = $request->file('barua')?->store('walipuaji/barua', 'public');
        $baruaYaZamani = $rekodi->barua;

        try {
            $kosa = Kosa::updateOrCreate(
                ['company_id' => $mlipuzi->company_id, 'kosa' => $data['kosa']],
                []
            );

            $rekodi->update([
                'kosa_id' => $kosa->id,
                'maelezo_ya_adhabu' => $data['maelezo_ya_adhabu'],
                'hali' => $data['hali'],
                'barua' => $baruaMpya ?: $rekodi->barua,
            ]);
        } catch (\Throwable $exception) {
            $this->deletePicha($baruaMpya);
            throw $exception;
        }

        if ($baruaMpya && $baruaYaZamani) {
            $this->deletePicha($baruaYaZamani);
        }

        return redirect()
            ->route('milipuko.walipuaji.show', $mlipuzi)
            ->with('success', 'Kosa limehifadhiwa.');
    }

    public function destroyKosa(Mlipuzi $mlipuzi, int $rekodi)
    {
        $rekodi = $this->findRekodi($mlipuzi, $rekodi);
        $this->deletePicha($rekodi->barua);
        $rekodi->delete();

        return redirect()
            ->route('milipuko.walipuaji.show', $mlipuzi)
            ->with('success', 'Kosa limefutwa.');
    }

    public function destroy(Mlipuzi $mlipuzi)
    {
        if ($mlipuzi->wanaotumiaBcYake()->exists()) {
            return redirect()
                ->route('milipuko.walipuaji.show', $mlipuzi)
                ->with('error', 'Huwezi kufuta mlipuaji huyu kwa sababu wengine wanatumia BC No. yake.');
        }

        if (Kibali::where('mlipuzi_id', $mlipuzi->id)->exists()
            || DB::table('kibali_mchorongaji')->where('mlipuzi_id', $mlipuzi->id)->exists()) {
            return redirect()
                ->route('milipuko.walipuaji.show', $mlipuzi)
                ->with('error', 'Huwezi kufuta mlipuaji huyu kwa sababu yupo kwenye kibali.');
        }

        $mlipuzi->delete();

        return redirect()
            ->route('milipuko.walipuaji.index')
            ->with('success', 'Mlipuaji amefutwa.');
    }

    private function formData(?Mlipuzi $mlipuzi = null): array
    {
        $wenyeBc = Mlipuzi::forCompany(auth()->user()->company_id)
            ->where('aina_ya_bc', 'yake')
            ->when($mlipuzi, fn ($query) => $query->where('id', '!=', $mlipuzi->id))
            ->orderBy('bc_no')
            ->get();

        return [
            'mikoa' => get_tanzania_regions(),
            'wilayaKwaMkoa' => get_tanzania_districts(),
            'wenyeBc' => $wenyeBc,
        ];
    }

    private function validated(Request $request, ?Mlipuzi $mlipuzi = null): array
    {
        $companyId = auth()->user()->company_id;
        $wilayaKwaMkoa = get_tanzania_districts();

        $validated = $request->validate([
            'jina' => ['required', 'string', 'max:255'],
            'hali' => ['required', Rule::in(['active', 'blocked'])],
            'aina_ya_bc' => ['required', Rule::in(['yake', 'mtu'])],
            'bc_no' => [
                Rule::requiredIf($request->input('aina_ya_bc') === 'yake'),
                'nullable',
                'string',
                'max:100',
                Rule::unique('walipuaji', 'bc_no')
                    ->where(fn ($query) => $query->where('company_id', $companyId))
                    ->ignore($mlipuzi?->id),
            ],
            'bc_ya_mlipuzi_id' => [
                Rule::requiredIf($request->input('aina_ya_bc') === 'mtu'),
                'nullable',
                'integer',
                Rule::exists('walipuaji', 'id')->where(function ($query) use ($companyId, $mlipuzi) {
                    $query->where('company_id', $companyId)->where('aina_ya_bc', 'yake');
                    if ($mlipuzi) {
                        $query->where('id', '!=', $mlipuzi->id);
                    }
                }),
            ],
            'simu' => ['required', 'string', 'max:30'],
            'simu_mbadala' => ['nullable', 'string', 'max:30'],
            'picha' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'mkoa' => ['required', 'string', Rule::in(get_tanzania_regions())],
            'wilaya' => ['required', 'string', 'max:100'],
            'eneo' => ['nullable', 'string', 'max:2000'],
            'wawasiliani' => ['nullable', 'array'],
            'wawasiliani.*.jina' => ['nullable', 'string', 'max:255'],
            'wawasiliani.*.simu' => ['nullable', 'string', 'max:30'],
            'wawasiliani.*.uhusiano' => ['nullable', 'string', 'max:100'],
        ], [
            'jina.required' => 'Jina linahitajika.',
            'hali.required' => 'Chagua hali.',
            'hali.in' => 'Hali iliyochaguliwa haipo.',
            'aina_ya_bc.required' => 'Chagua kama anatumia BC No. yake au ya mtu.',
            'bc_no.required' => 'BC No. inahitajika.',
            'bc_no.unique' => 'BC No. hii tayari imesajiliwa.',
            'bc_ya_mlipuzi_id.required' => 'Chagua mwenye BC No.',
            'bc_ya_mlipuzi_id.exists' => 'BC No. uliyochagua haipo kwenye orodha ya zinazojitegemea.',
            'simu.required' => 'Simu inahitajika.',
            'picha.image' => 'Picha lazima iwe faili ya picha.',
            'picha.max' => 'Picha isizidi MB 2.',
            'mkoa.required' => 'Chagua mkoa.',
            'mkoa.in' => 'Mkoa uliochaguliwa haupo.',
            'wilaya.required' => 'Chagua wilaya.',
        ]);

        $wilayaZaMkoa = $wilayaKwaMkoa[$validated['mkoa']] ?? [];
        if (! in_array($validated['wilaya'], $wilayaZaMkoa, true)) {
            throw ValidationException::withMessages([
                'wilaya' => 'Wilaya haipo kwenye mkoa uliochaguliwa.',
            ]);
        }

        if (
            $mlipuzi
            && $mlipuzi->aina_ya_bc === 'yake'
            && $validated['aina_ya_bc'] === 'mtu'
            && $mlipuzi->wanaotumiaBcYake()->exists()
        ) {
            throw ValidationException::withMessages([
                'aina_ya_bc' => 'Huwezi kubadilisha BC hii kwa sababu walipuaji wengine wanaitumia.',
            ]);
        }

        $wawasiliani = $this->filledContacts($validated['wawasiliani'] ?? []);
        $this->assertContactsComplete($request);
        $this->assertUniquePhones($wawasiliani);

        $anatumiaYake = $validated['aina_ya_bc'] === 'yake';

        return [
            'jina' => trim($validated['jina']),
            'hali' => $validated['hali'],
            'aina_ya_bc' => $validated['aina_ya_bc'],
            'bc_no' => $anatumiaYake ? trim((string) $validated['bc_no']) : null,
            'bc_ya_mlipuzi_id' => $anatumiaYake ? null : (int) $validated['bc_ya_mlipuzi_id'],
            'simu' => trim($validated['simu']),
            'simu_mbadala' => trim((string) ($validated['simu_mbadala'] ?? '')) ?: null,
            'mkoa' => $validated['mkoa'],
            'wilaya' => $validated['wilaya'],
            'eneo' => trim((string) ($validated['eneo'] ?? '')) ?: null,
            'wawasiliani' => $wawasiliani,
        ];
    }

    private function filledContacts(array $rows): array
    {
        $contacts = [];

        foreach ($rows as $row) {
            $jina = trim((string) ($row['jina'] ?? ''));
            $simu = trim((string) ($row['simu'] ?? ''));
            $uhusiano = trim((string) ($row['uhusiano'] ?? ''));

            if ($jina === '' && $simu === '' && $uhusiano === '') {
                continue;
            }

            $contacts[] = [
                'jina' => $jina,
                'simu' => $simu,
                'uhusiano' => $uhusiano,
            ];
        }

        return $contacts;
    }

    private function assertContactsComplete(Request $request): void
    {
        foreach ($request->input('wawasiliani', []) as $index => $row) {
            $jina = trim((string) ($row['jina'] ?? ''));
            $simu = trim((string) ($row['simu'] ?? ''));
            $uhusiano = trim((string) ($row['uhusiano'] ?? ''));

            if ($jina === '' && $simu === '' && $uhusiano === '') {
                continue;
            }

            if ($jina === '' || $simu === '' || $uhusiano === '') {
                throw ValidationException::withMessages([
                    "wawasiliani.$index.jina" => 'Jina, simu na uhusiano vinahitajika.',
                ]);
            }
        }
    }

    private function assertUniquePhones(array $contacts): void
    {
        $seen = [];

        foreach ($contacts as $index => $contact) {
            if (isset($seen[$contact['simu']])) {
                throw ValidationException::withMessages([
                    "wawasiliani.$index.simu" => 'Simu imerudiwa kwenye orodha ya watu wa kuwasiliana nao.',
                ]);
            }
            $seen[$contact['simu']] = true;
        }
    }

    private function makosaYaliyopo()
    {
        return Kosa::query()
            ->where('company_id', auth()->user()->company_id)
            ->orderBy('kosa')
            ->pluck('kosa');
    }

    private function findRekodi(Mlipuzi $mlipuzi, int $rekodi): MlipuziKosa
    {
        return $mlipuzi->makosa()->with('kosa')->whereKey($rekodi)->firstOrFail();
    }

    private function validatedKosa(Request $request): array
    {
        $validated = $request->validate([
            'kosa' => ['required', 'string', 'max:255'],
            'maelezo_ya_adhabu' => ['nullable', 'string', 'max:2000'],
            'hali' => ['required', Rule::in(['linaendelea', 'limekwisha'])],
            'barua' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx', 'max:5120'],
        ], [
            'kosa.required' => 'Kosa linahitajika.',
            'hali.required' => 'Chagua hali ya kosa.',
            'hali.in' => 'Hali ya kosa iliyochaguliwa haipo.',
            'barua.mimes' => 'Barua iwe PDF, Word au picha.',
            'barua.max' => 'Barua isizidi MB 5.',
        ]);

        return [
            'kosa' => trim($validated['kosa']),
            'maelezo_ya_adhabu' => trim((string) ($validated['maelezo_ya_adhabu'] ?? '')) ?: null,
            'hali' => $validated['hali'],
        ];
    }

    private function syncWawasiliani(Mlipuzi $mlipuzi, array $rows): void
    {
        $mlipuzi->wawasiliani()->delete();

        if ($rows !== []) {
            $mlipuzi->wawasiliani()->createMany($rows);
        }
    }

    private function qrDataUri(string $url): string
    {
        $barcode = new TCPDF2DBarcode($url, 'QRCODE,H');
        $png = $barcode->getBarcodePngData(5, 5, [0, 0, 0]);

        return 'data:image/png;base64,'.base64_encode($png);
    }

    private function storePicha(Request $request): ?string
    {
        if (! $request->hasFile('picha')) {
            return null;
        }

        return $request->file('picha')->store('walipuaji', 'public');
    }

    private function deletePicha(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
