<?php

namespace App\Http\Controllers\Milipuko;

use App\Http\Controllers\Controller;
use App\Models\Milipuko\Duara;
use App\Models\Milipuko\KibaliChaMawe;
use App\Models\Milipuko\Msimamizi;
use App\Models\Milipuko\UzalishajiWaDuara;
use App\Services\SystemSettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use TCPDF2DBarcode;
use Yajra\DataTables\Facades\DataTables;

class VibaliVyaMaweController extends Controller
{
    public function index()
    {
        return view('milipuko.mawe.index');
    }

    public function data()
    {
        $query = KibaliChaMawe::forCompany(auth()->user()->company_id)
            ->select('vibali_vya_mawe.*')
            ->with(['duara', 'msimamizi']);

        return DataTables::eloquent($query)
            ->editColumn('tarehe', function (KibaliChaMawe $kibali) {
                return $kibali->tarehe?->format('d/m/Y') ?? '—';
            })
            ->addColumn('duara_namba', function (KibaliChaMawe $kibali) {
                return e($kibali->duara->namba ?? '—');
            })
            ->addColumn('aina', function (KibaliChaMawe $kibali) {
                return e($kibali->ainaLabel());
            })
            ->addColumn('msimamizi_jina', function (KibaliChaMawe $kibali) {
                return e($kibali->msimamizi->jina ?? '—');
            })
            ->addColumn('vitendo', function (KibaliChaMawe $kibali) {
                return '<a href="'.e(route('milipuko.mawe.show', $kibali)).'" class="btn btn-sm btn-outline-primary">Angalia</a>'
                    .' <a href="'.e(route('milipuko.mawe.edit', $kibali)).'" class="btn btn-sm btn-outline-warning">Hariri</a>';
            })
            ->filterColumn('tarehe', function ($query, $keyword) {
                $query->whereRaw("DATE_FORMAT(vibali_vya_mawe.tarehe, '%d/%m/%Y') like ?", ['%'.$keyword.'%']);
            })
            ->filterColumn('duara_namba', function ($query, $keyword) {
                $query->whereHas('duara', function ($q) use ($keyword) {
                    $q->where('namba', 'like', '%'.$keyword.'%');
                });
            })
            ->filterColumn('msimamizi_jina', function ($query, $keyword) {
                $query->whereHas('msimamizi', function ($q) use ($keyword) {
                    $q->where('jina', 'like', '%'.$keyword.'%');
                });
            })
            ->orderColumn('duara_namba', function ($query, $order) {
                $query->orderBy(
                    Duara::select('namba')->whereColumn('maduara.id', 'vibali_vya_mawe.duara_id')->limit(1),
                    $order
                );
            })
            ->orderColumn('msimamizi_jina', function ($query, $order) {
                $query->orderBy(
                    Msimamizi::select('jina')->whereColumn('wasimamizi.id', 'vibali_vya_mawe.msimamizi_id')->limit(1),
                    $order
                );
            })
            ->rawColumns(['vitendo'])
            ->removeColumn('duara', 'msimamizi')
            ->make(true);
    }

    public function maduara(Request $request)
    {
        $request->validate(['tarehe' => ['required', 'date']]);

        $maduara = Duara::forCompany(auth()->user()->company_id)
            ->with('wasimamizi')
            ->whereHas('uzalishaji', function ($query) use ($request) {
                $query->whereDate('tarehe', $request->input('tarehe'));
            })
            ->orderBy('namba')
            ->get()
            ->map(fn (Duara $duara) => [
                'id' => $duara->id,
                'namba' => $duara->namba,
                'wasimamizi' => $duara->wasimamizi->map(fn (Msimamizi $msimamizi) => [
                    'id' => $msimamizi->id,
                    'jina' => $msimamizi->jina,
                    'simu' => $msimamizi->simu,
                ])->values(),
            ])
            ->values();

        return response()->json(['maduara' => $maduara]);
    }

    public function create()
    {
        return view('milipuko.mawe.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $companyId = auth()->user()->company_id;

        $kibali = DB::transaction(function () use ($data, $companyId) {
            $max = DB::table('vibali_vya_mawe')
                ->where('company_id', $companyId)
                ->lockForUpdate()
                ->selectRaw('MAX(CAST(namba AS UNSIGNED)) as namba_kubwa')
                ->first()
                ?->namba_kubwa;

            return KibaliChaMawe::create([
                'company_id' => $companyId,
                'branch_id' => session('branch_id') ?: auth()->user()->branch_id,
                'namba' => str_pad((string) (((int) $max) + 1), 4, '0', STR_PAD_LEFT),
                'duara_id' => $data['duara_id'],
                'idadi_ya_mifuko' => $data['idadi_ya_mifuko'],
                'aina_ya_mzigo' => $data['aina_ya_mzigo'],
                'tarehe' => $data['tarehe'],
                'msimamizi_id' => $data['msimamizi_id'],
                'katibu' => $data['katibu'],
                'created_by' => auth()->id(),
            ]);
        });

        return redirect()
            ->route('milipuko.mawe.show', $kibali)
            ->with('success', 'Kibali cha mawe kimesajiliwa.');
    }

    public function show(KibaliChaMawe $kibali)
    {
        $kibali->load(['company', 'duara', 'msimamizi']);

        return view('milipuko.mawe.show', [
            'kibali' => $kibali,
            'qr' => $this->qrDataUri($kibali->thibitishoUrl()),
        ]);
    }

    public function chapisha(KibaliChaMawe $kibali)
    {
        $kibali->load(['company', 'duara', 'msimamizi']);

        return view('milipuko.mawe.chapisha', [
            'kibali' => $kibali,
            'qr' => $this->qrDataUri($kibali->thibitishoUrl()),
        ]);
    }

    public function thibitisha(string $token)
    {
        $kibali = KibaliChaMawe::where('uthibitisho_token', $token)
            ->with(['company', 'duara', 'msimamizi'])
            ->first();

        return view('milipuko.mawe.thibitisha', [
            'kibali' => $kibali,
            'appName' => SystemSettingService::get('app_name', 'M-ERP'),
        ]);
    }

    public function edit(KibaliChaMawe $kibali)
    {
        $kibali->load('msimamizi');

        return view('milipuko.mawe.edit', array_merge(
            $this->formData($kibali),
            compact('kibali')
        ));
    }

    public function update(Request $request, KibaliChaMawe $kibali)
    {
        $data = $this->validated($request, $kibali);

        $kibali->update([
            'duara_id' => $data['duara_id'],
            'idadi_ya_mifuko' => $data['idadi_ya_mifuko'],
            'aina_ya_mzigo' => $data['aina_ya_mzigo'],
            'tarehe' => $data['tarehe'],
            'msimamizi_id' => $data['msimamizi_id'],
            'katibu' => $data['katibu'],
        ]);

        return redirect()
            ->route('milipuko.mawe.show', $kibali)
            ->with('success', 'Kibali cha mawe kimehaririwa.');
    }

    public function destroy(KibaliChaMawe $kibali)
    {
        $kibali->delete();

        return redirect()
            ->route('milipuko.mawe.index')
            ->with('success', 'Kibali cha mawe kimefutwa.');
    }

    private function formData(?KibaliChaMawe $kibali = null): array
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

        return [
            'company' => auth()->user()->company,
            'maduara' => $maduara,
        ];
    }

    private function validated(Request $request, ?KibaliChaMawe $kibali = null): array
    {
        $validated = $request->validate([
            'duara_id' => ['required', 'integer'],
            'msimamizi_id' => ['nullable', 'integer', 'required_without:msimamizi_jina'],
            'msimamizi_jina' => ['nullable', 'string', 'max:255', 'required_without:msimamizi_id'],
            'msimamizi_simu' => ['nullable', 'string', 'max:30', 'required_with:msimamizi_jina'],
            'idadi_ya_mifuko' => ['required', 'integer', 'min:1', 'max:100000'],
            'aina_ya_mzigo' => ['required', Rule::in(['mawe', 'chorongeo'])],
            'tarehe' => ['required', 'date'],
        ], [
            'duara_id.required' => 'Duara No. inahitajika.',
            'msimamizi_id.required_without' => 'Chagua msimamizi wa duara, au andika jina na simu.',
            'msimamizi_jina.required_without' => 'Andika jina la msimamizi wa duara.',
            'msimamizi_simu.required_with' => 'Andika simu ya msimamizi wa duara.',
            'aina_ya_mzigo.required' => 'Chagua aina ya mzigo.',
            'aina_ya_mzigo.in' => 'Aina ya mzigo ni Mawe au Chorongeo.',
            'idadi_ya_mifuko.required' => 'Idadi ya mifuko inahitajika.',
            'idadi_ya_mifuko.integer' => 'Idadi ya mifuko lazima iwe namba.',
            'idadi_ya_mifuko.min' => 'Idadi ya mifuko lazima iwe angalau moja.',
            'tarehe.required' => 'Tarehe inahitajika.',
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

        $limezalisha = UzalishajiWaDuara::forCompany($companyId)
            ->where('duara_id', $duara->id)
            ->whereDate('tarehe', $validated['tarehe'])
            ->exists();

        if (! $limezalisha) {
            throw ValidationException::withMessages([
                'duara_id' => 'Duara hili halijasajiliwa kuwa limezalisha tarehe ya kibali.',
            ]);
        }

        $aliyeko = $kibali && (int) $kibali->duara_id === (int) $duara->id ? (int) $kibali->msimamizi_id : null;
        $msimamizi = Msimamizi::chaguaAuAndika(
            $duara,
            $validated['msimamizi_id'] ?? null,
            $request->input('msimamizi_jina'),
            $request->input('msimamizi_simu'),
            $aliyeko
        );

        return [
            'duara_id' => $duara->id,
            'msimamizi_id' => $msimamizi->id,
            'idadi_ya_mifuko' => (int) $validated['idadi_ya_mifuko'],
            'aina_ya_mzigo' => $validated['aina_ya_mzigo'],
            'tarehe' => $validated['tarehe'],
            'katibu' => $katibu,
        ];
    }

    private function qrDataUri(string $url): string
    {
        $barcode = new TCPDF2DBarcode($url, 'QRCODE,H');
        $png = $barcode->getBarcodePngData(5, 5, [0, 0, 0]);

        return 'data:image/png;base64,'.base64_encode($png);
    }
}
