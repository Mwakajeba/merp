<?php

namespace App\Http\Controllers\Milipuko;

use App\Http\Controllers\Controller;
use App\Models\Milipuko\Kompresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class KompresaController extends Controller
{
    public function index()
    {
        return view('milipuko.kompresa.index');
    }

    public function data()
    {
        $query = Kompresa::forCompany(auth()->user()->company_id)
            ->select([
                'kompresa.id',
                'kompresa.jina',
                'kompresa.namba',
                'kompresa.simu',
                'kompresa.mkoa',
                'kompresa.wilaya',
                'kompresa.picha',
            ]);

        return DataTables::eloquent($query)
            ->addColumn('picha_html', function (Kompresa $kompresa) {
                $url = $kompresa->pichaUrl();
                if (! $url) {
                    return '<span class="text-muted">—</span>';
                }

                return '<img src="'.e($url).'" alt="'.e($kompresa->jina).'" width="48" height="48" class="rounded-circle object-fit-cover">';
            })
            ->addColumn('vitendo', function (Kompresa $kompresa) {
                return '<a href="'.e(route('milipuko.kompresa.show', $kompresa)).'" class="btn btn-sm btn-outline-primary">Angalia</a>'
                    .' <a href="'.e(route('milipuko.kompresa.cheti', $kompresa)).'" class="btn btn-sm btn-outline-secondary">Cheti</a>'
                    .' <a href="'.e(route('milipuko.kompresa.edit', $kompresa)).'" class="btn btn-sm btn-outline-warning">Hariri</a>';
            })
            ->rawColumns(['picha_html', 'vitendo'])
            ->removeColumn('id', 'picha')
            ->make(true);
    }

    public function create()
    {
        return view('milipuko.kompresa.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $picha = $this->storePicha($request);

        try {
            $kompresa = Kompresa::create([
                'company_id' => auth()->user()->company_id,
                'branch_id' => session('branch_id') ?: auth()->user()->branch_id,
                'jina' => $data['jina'],
                'mkoa' => $data['mkoa'],
                'wilaya' => $data['wilaya'],
                'mtaa' => $data['mtaa'],
                'maelezo' => $data['maelezo'],
                'simu' => $data['simu'],
                'simu_mbadala' => $data['simu_mbadala'],
                'namba' => $data['namba'],
                'picha' => $picha,
                'created_by' => auth()->id(),
            ]);
        } catch (\Throwable $exception) {
            $this->deletePicha($picha);
            throw $exception;
        }

        return redirect()
            ->route('milipuko.kompresa.cheti', ['kompresa' => $kompresa, 'chapisha' => 1])
            ->with('success', 'Kompresa imesajiliwa. Chapisha cheti.');
    }

    public function show(Kompresa $kompresa)
    {
        $kompresa->load('company');

        return view('milipuko.kompresa.show', compact('kompresa'));
    }

    public function cheti(Kompresa $kompresa)
    {
        $kompresa->load('company');

        return view('milipuko.kompresa.cheti', compact('kompresa'));
    }

    public function edit(Kompresa $kompresa)
    {
        return view('milipuko.kompresa.edit', array_merge(
            $this->formData(),
            ['kompresa' => $kompresa]
        ));
    }

    public function update(Request $request, Kompresa $kompresa)
    {
        $data = $this->validated($request, $kompresa);
        $pichaMpya = $this->storePicha($request);
        $pichaYaZamani = $kompresa->picha;

        try {
            $kompresa->update([
                'jina' => $data['jina'],
                'mkoa' => $data['mkoa'],
                'wilaya' => $data['wilaya'],
                'mtaa' => $data['mtaa'],
                'maelezo' => $data['maelezo'],
                'simu' => $data['simu'],
                'simu_mbadala' => $data['simu_mbadala'],
                'namba' => $data['namba'],
                'picha' => $pichaMpya ?: $pichaYaZamani,
            ]);

            if ($pichaMpya && $pichaYaZamani) {
                $this->deletePicha($pichaYaZamani);
            }
        } catch (\Throwable $exception) {
            $this->deletePicha($pichaMpya);
            throw $exception;
        }

        return redirect()
            ->route('milipuko.kompresa.show', $kompresa)
            ->with('success', 'Taarifa za kompresa zimehifadhiwa.');
    }

    public function destroy(Kompresa $kompresa)
    {
        $kompresa->delete();

        return redirect()
            ->route('milipuko.kompresa.index')
            ->with('success', 'Usajili wa kompresa umefutwa.');
    }

    private function formData(): array
    {
        return [
            'mikoa' => get_tanzania_regions(),
            'wilayaKwaMkoa' => get_tanzania_districts(),
        ];
    }

    private function validated(Request $request, ?Kompresa $kompresa = null): array
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'jina' => ['required', 'string', 'max:255'],
            'mkoa' => ['required', 'string', Rule::in(get_tanzania_regions())],
            'wilaya' => ['required', 'string', 'max:100'],
            'mtaa' => ['nullable', 'string', 'max:255'],
            'maelezo' => ['nullable', 'string', 'max:2000'],
            'simu' => ['required', 'string', 'max:30'],
            'simu_mbadala' => ['nullable', 'string', 'max:30'],
            'namba' => [
                'required',
                'string',
                'max:100',
                Rule::unique('kompresa', 'namba')
                    ->where(fn ($query) => $query->where('company_id', $companyId))
                    ->ignore($kompresa?->id),
            ],
            'picha' => [$kompresa ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'jina.required' => 'Jina la mmiliki linahitajika.',
            'mkoa.required' => 'Chagua mkoa.',
            'mkoa.in' => 'Mkoa uliochaguliwa haupo.',
            'wilaya.required' => 'Chagua wilaya.',
            'simu.required' => 'Simu inahitajika.',
            'namba.required' => 'Namba ya kompresa inahitajika.',
            'namba.unique' => 'Namba hii ya kompresa tayari imesajiliwa.',
            'picha.required' => 'Picha ya mmiliki inahitajika.',
            'picha.image' => 'Picha iwe JPEG, PNG au WebP.',
            'picha.max' => 'Picha isizidi MB 2.',
        ]);

        $wilayaZaMkoa = get_tanzania_districts()[$validated['mkoa']] ?? [];
        if (! in_array($validated['wilaya'], $wilayaZaMkoa, true)) {
            throw ValidationException::withMessages([
                'wilaya' => 'Wilaya haipo kwenye mkoa uliochaguliwa.',
            ]);
        }

        return [
            'jina' => trim($validated['jina']),
            'mkoa' => $validated['mkoa'],
            'wilaya' => $validated['wilaya'],
            'mtaa' => trim((string) ($validated['mtaa'] ?? '')) ?: null,
            'maelezo' => trim((string) ($validated['maelezo'] ?? '')) ?: null,
            'simu' => trim($validated['simu']),
            'simu_mbadala' => trim((string) ($validated['simu_mbadala'] ?? '')) ?: null,
            'namba' => trim($validated['namba']),
        ];
    }

    private function storePicha(Request $request): ?string
    {
        if (! $request->hasFile('picha')) {
            return null;
        }

        return $request->file('picha')->store('kompresa', 'public');
    }

    private function deletePicha(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
