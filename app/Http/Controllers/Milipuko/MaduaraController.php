<?php

namespace App\Http\Controllers\Milipuko;

use App\Http\Controllers\Controller;
use App\Models\Milipuko\Duara;
use App\Models\Milipuko\Kibali;
use App\Models\Milipuko\Msimamizi;
use App\Models\Milipuko\Mwanachama;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MaduaraController extends Controller
{
    public function index()
    {
        $hali = request('hali');
        if (! in_array($hali, ['inafanya_kazi', 'imefungwa'], true)) {
            $hali = null;
        }

        $maduara = Duara::forCompany(auth()->user()->company_id)
            ->when($hali, fn ($query) => $query->where('hali', $hali))
            ->withCount(['wasimamizi', 'wanachama'])
            ->latest()
            ->get();

        return view('milipuko.maduara.index', compact('maduara', 'hali'));
    }

    public function create()
    {
        return view('milipuko.maduara.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $duara = DB::transaction(function () use ($data) {
            $duara = Duara::create([
                'company_id' => auth()->user()->company_id,
                'branch_id' => session('branch_id') ?: auth()->user()->branch_id,
                'namba' => $data['namba'],
                'maelezo' => $data['maelezo'],
                'hali' => $data['hali'],
                'created_by' => auth()->id(),
            ]);

            $this->syncWasimamizi($duara, $data['wasimamizi']);
            $this->syncWanachama($duara, $data['wanachama']);

            return $duara;
        });

        return redirect()
            ->route('milipuko.maduara.show', $duara)
            ->with('success', 'Duara limesajiliwa.');
    }

    public function show(Duara $duara)
    {
        $duara->load([
            'wasimamizi',
            'wanachama',
            'vibali' => fn ($query) => $query->with('mlipuzi')->orderByDesc('tarehe')->orderByDesc('id'),
        ]);

        return view('milipuko.maduara.show', compact('duara'));
    }

    public function edit(Duara $duara)
    {
        $duara->load(['wasimamizi', 'wanachama']);

        return view('milipuko.maduara.edit', compact('duara'));
    }

    public function update(Request $request, Duara $duara)
    {
        $data = $this->validated($request, $duara);

        DB::transaction(function () use ($duara, $data) {
            $duara->update([
                'namba' => $data['namba'],
                'maelezo' => $data['maelezo'],
                'hali' => $data['hali'],
            ]);

            $this->syncWasimamizi($duara, $data['wasimamizi']);
            $this->syncWanachama($duara, $data['wanachama']);
        });

        return redirect()
            ->route('milipuko.maduara.show', $duara)
            ->with('success', 'Duara limehaririwa.');
    }

    public function destroy(Duara $duara)
    {
        if (Kibali::where('duara_id', $duara->id)->exists()) {
            return redirect()
                ->route('milipuko.maduara.show', $duara)
                ->with('error', 'Huwezi kufuta duara hili kwa sababu lina vibali.');
        }

        $duara->delete();

        return redirect()
            ->route('milipuko.maduara.index')
            ->with('success', 'Duara limefutwa.');
    }

    private function validated(Request $request, ?Duara $duara = null): array
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'namba' => [
                'required',
                'string',
                'max:100',
                Rule::unique('maduara', 'namba')
                    ->where(fn ($query) => $query->where('company_id', $companyId))
                    ->ignore($duara?->id),
            ],
            'maelezo' => ['nullable', 'string', 'max:2000'],
            'hali' => ['required', Rule::in(['inafanya_kazi', 'imefungwa'])],
            'wasimamizi' => ['nullable', 'array'],
            'wasimamizi.*.jina' => ['nullable', 'string', 'max:255'],
            'wasimamizi.*.simu' => ['nullable', 'string', 'max:30'],
            'wanachama' => ['nullable', 'array'],
            'wanachama.*.jina' => ['nullable', 'string', 'max:255'],
            'wanachama.*.simu' => ['nullable', 'string', 'max:30'],
            'wanachama.*.hisa' => ['nullable', 'numeric', 'min:0'],
        ], [
            'hali.required' => 'Hali ya duara inahitajika.',
            'hali.in' => 'Hali ni Inafanya kazi au Imefungwa.',
            'namba.required' => 'Namba ya duara inahitajika.',
            'namba.unique' => 'Namba hii ya duara tayari imesajiliwa.',
            'wanachama.*.hisa.numeric' => 'Hisa lazima iwe namba.',
            'wanachama.*.hisa.min' => 'Hisa haiwezi kuwa chini ya sifuri.',
        ]);

        $wasimamizi = $this->filledPeople($validated['wasimamizi'] ?? [], false);
        $wanachama = $this->filledPeople($validated['wanachama'] ?? [], true);

        $this->assertPeopleComplete($request, 'wasimamizi', 'msimamizi');
        $this->assertPeopleComplete($request, 'wanachama', 'mwanachama');
        $this->assertUniquePhones($wasimamizi, 'wasimamizi', 'msimamizi');
        $this->assertUniquePhones($wanachama, 'wanachama', 'mwanachama');

        return [
            'namba' => trim($validated['namba']),
            'maelezo' => isset($validated['maelezo']) ? trim($validated['maelezo']) : null,
            'hali' => $validated['hali'],
            'wasimamizi' => $wasimamizi,
            'wanachama' => $wanachama,
        ];
    }

    private function filledPeople(array $rows, bool $withHisa): array
    {
        $people = [];

        foreach ($rows as $row) {
            $jina = trim((string) ($row['jina'] ?? ''));
            $simu = trim((string) ($row['simu'] ?? ''));
            $hisa = $withHisa ? trim((string) ($row['hisa'] ?? '')) : null;

            if ($jina === '' && $simu === '' && ($hisa === null || $hisa === '')) {
                continue;
            }

            $person = [
                'jina' => $jina,
                'simu' => $simu,
            ];

            if ($withHisa) {
                $person['hisa'] = $hisa === '' ? null : $hisa;
            }

            $people[] = $person;
        }

        return $people;
    }

    private function assertPeopleComplete(Request $request, string $key, string $label): void
    {
        foreach ($request->input($key, []) as $index => $row) {
            $jina = trim((string) ($row['jina'] ?? ''));
            $simu = trim((string) ($row['simu'] ?? ''));
            $hisa = trim((string) ($row['hisa'] ?? ''));

            if ($jina === '' && $simu === '' && $hisa === '') {
                continue;
            }

            if ($jina === '' || $simu === '') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "$key.$index.jina" => 'Jina na simu ya '.$label.' vinahitajika.',
                ]);
            }
        }
    }

    private function assertUniquePhones(array $people, string $key, string $label): void
    {
        $seen = [];

        foreach ($people as $index => $person) {
            $simu = $person['simu'];
            if (isset($seen[$simu])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "$key.$index.simu" => 'Simu ya '.$label.' imerudiwa kwenye duara hili.',
                ]);
            }
            $seen[$simu] = true;
        }
    }

    private function syncWasimamizi(Duara $duara, array $rows): void
    {
        $ids = [];

        foreach ($rows as $row) {
            $msimamizi = Msimamizi::updateOrCreate(
                [
                    'company_id' => $duara->company_id,
                    'simu' => $row['simu'],
                ],
                [
                    'jina' => $row['jina'],
                ]
            );
            $ids[] = $msimamizi->id;
        }

        $duara->wasimamizi()->sync($ids);
    }

    private function syncWanachama(Duara $duara, array $rows): void
    {
        $sync = [];

        foreach ($rows as $row) {
            $mwanachama = Mwanachama::updateOrCreate(
                [
                    'company_id' => $duara->company_id,
                    'simu' => $row['simu'],
                ],
                [
                    'jina' => $row['jina'],
                ]
            );
            $sync[$mwanachama->id] = [
                'hisa' => $row['hisa'],
            ];
        }

        $duara->wanachama()->sync($sync);
    }
}
