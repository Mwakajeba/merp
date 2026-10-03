<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryLocation;
use App\Models\LoginAttempt;
use App\Models\Milipuko\Duara;
use App\Models\Milipuko\Kibali;
use App\Models\Milipuko\KibaliChaMawe;
use App\Models\Milipuko\Mlipuzi;
use App\Models\Milipuko\Msimamizi;
use App\Models\Milipuko\UzalishajiWaDuara;
use App\Models\User;
use App\Services\PinService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class MilipukoMobileController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $this->hakikishaRoleYaVerifier();

        if (LoginAttempt::isLockedOut($request->phone)) {
            $remaining = LoginAttempt::getRemainingLockoutTime($request->phone);

            return response()->json([
                'success' => false,
                'message' => "Akaunti imefungwa kwa muda. Jaribu tena baada ya dakika {$remaining}.",
            ], 429);
        }

        $user = find_user_by_phone($request->phone);

        if (! $user || ! Hash::check($request->password, $user->password)) {
            LoginAttempt::record($request->phone, $request->ip(), $request->userAgent(), false);

            return response()->json([
                'success' => false,
                'message' => 'Namba ya simu au nenosiri si sahihi.',
            ], 401);
        }

        if (isset($user->is_active) && ! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akaunti hii haijawezeshwa.',
            ], 403);
        }

        LoginAttempt::record($request->phone, $request->ip(), $request->userAgent(), true);

        return $this->tokenResponse($user);
    }

    public function loginPin(Request $request): JsonResponse
    {
        $request->validate([
            'pin' => 'required|digits:4',
        ], [
            'pin.required' => 'Weka PIN.',
            'pin.digits' => 'PIN ni tarakimu 4.',
        ]);

        $this->hakikishaRoleYaVerifier();

        $lockoutKey = 'pin:'.$request->ip();
        if (LoginAttempt::isLockedOut($lockoutKey)) {
            $remaining = LoginAttempt::getRemainingLockoutTime($lockoutKey);

            return response()->json([
                'success' => false,
                'message' => "Majaribio mengi yameshindikana. Jaribu tena baada ya dakika {$remaining}.",
            ], 429);
        }

        $user = app(PinService::class)->findUserByPin($request->pin);

        if (! $user) {
            LoginAttempt::record($lockoutKey, $request->ip(), $request->userAgent(), false);

            return response()->json([
                'success' => false,
                'message' => 'PIN si sahihi. Jaribu tena.',
            ], 401);
        }

        LoginAttempt::record($lockoutKey, $request->ip(), $request->userAgent(), true);

        return $this->tokenResponse($user);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Umetoka.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ['user' => $this->formatUser($request->user())],
        ]);
    }

    public function fomu(Request $request): JsonResponse
    {
        $this->kataMthibitishaji($request->user());
        $companyId = $request->user()->company_id;

        $maduara = Duara::forCompany($companyId)
            ->with('wasimamizi')
            ->when($request->filled('tarehe'), function ($query) use ($request) {
                $query->whereHas('uzalishaji', function ($uzalishaji) use ($request) {
                    $uzalishaji->whereDate('tarehe', $request->input('tarehe'));
                });
            }, function ($query) {
                $query->where('hali', 'inafanya_kazi');
            })
            ->orderBy('namba')
            ->get()
            ->map(fn (Duara $duara) => [
                'id' => $duara->id,
                'namba' => $duara->namba,
                'wasimamizi' => $duara->wasimamizi->map(fn (Msimamizi $m) => [
                    'id' => $m->id,
                    'jina' => $m->jina,
                    'simu' => $m->simu,
                ])->values(),
            ]);

        $walipuaji = Mlipuzi::forCompany($companyId)
            ->with('mwenyeBc')
            ->where('hali', 'active')
            ->orderBy('jina')
            ->get()
            ->map(fn (Mlipuzi $mtu) => $this->mlipuziFupi($mtu));

        return response()->json([
            'success' => true,
            'data' => [
                'maduara' => $maduara,
                'walipuaji' => $walipuaji,
                'katibu' => $request->user()->name,
            ],
        ]);
    }

    public function storeKibali(Request $request): JsonResponse
    {
        $this->kataMthibitishaji($request->user());
        $data = $this->validatedKibali($request);
        $user = $request->user();
        $companyId = $user->company_id;

        $kibali = DB::transaction(function () use ($data, $companyId, $user, $request) {
            $max = DB::table('vibali')
                ->where('company_id', $companyId)
                ->lockForUpdate()
                ->selectRaw('MAX(CAST(namba AS UNSIGNED)) as namba_kubwa')
                ->first()
                ?->namba_kubwa;

            $kibali = Kibali::create([
                'company_id' => $companyId,
                'branch_id' => $this->branchId($request, $user),
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
                'created_by' => $user->id,
            ]);

            foreach ($data['wachorongaji'] as $nafasi => $mlipuziId) {
                $kibali->wachorongaji()->attach($mlipuziId, ['nafasi' => $nafasi]);
            }

            return $kibali->load(['company', 'duara', 'mlipuzi', 'msimamizi', 'wachorongaji']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Kibali '.$kibali->namba.' kimekatwa.',
            'data' => ['kibali' => $this->kibaliMaelezo($kibali)],
        ], 201);
    }

    public function storeMawe(Request $request): JsonResponse
    {
        $this->kataMthibitishaji($request->user());
        $user = $request->user();
        $validated = $request->validate([
            'duara_id' => ['required', 'integer'],
            'msimamizi_id' => ['nullable', 'integer', 'required_without:msimamizi_jina'],
            'msimamizi_jina' => ['nullable', 'string', 'max:255', 'required_without:msimamizi_id'],
            'msimamizi_simu' => ['nullable', 'string', 'max:30', 'required_with:msimamizi_jina'],
            'idadi_ya_mifuko' => ['required', 'integer', 'min:1', 'max:100000'],
            'aina_ya_mzigo' => ['required', Rule::in(['mawe', 'chorongeo'])],
            'tarehe' => ['required', 'date'],
        ]);

        $katibu = trim((string) $user->name);
        if ($katibu === '') {
            throw ValidationException::withMessages([
                'katibu' => 'Jina la mtumiaji aliyeingia halipatikani.',
            ]);
        }

        $duara = Duara::forCompany($user->company_id)->with('wasimamizi')->find($validated['duara_id']);
        if (! $duara) {
            throw ValidationException::withMessages([
                'duara_id' => 'Chagua duara lililosajiliwa.',
            ]);
        }

        $limezalisha = UzalishajiWaDuara::forCompany($user->company_id)
            ->where('duara_id', $duara->id)
            ->whereDate('tarehe', $validated['tarehe'])
            ->exists();

        if (! $limezalisha) {
            throw ValidationException::withMessages([
                'duara_id' => 'Duara hili halijasajiliwa kuwa limezalisha tarehe ya kibali.',
            ]);
        }

        $msimamizi = Msimamizi::chaguaAuAndika(
            $duara,
            $validated['msimamizi_id'] ?? null,
            $request->input('msimamizi_jina'),
            $request->input('msimamizi_simu')
        );

        $kibali = DB::transaction(function () use ($validated, $user, $request, $katibu, $msimamizi) {
            $max = DB::table('vibali_vya_mawe')
                ->where('company_id', $user->company_id)
                ->lockForUpdate()
                ->selectRaw('MAX(CAST(namba AS UNSIGNED)) as namba_kubwa')
                ->first()
                ?->namba_kubwa;

            return KibaliChaMawe::create([
                'company_id' => $user->company_id,
                'branch_id' => $this->branchId($request, $user),
                'namba' => str_pad((string) (((int) $max) + 1), 4, '0', STR_PAD_LEFT),
                'duara_id' => $validated['duara_id'],
                'idadi_ya_mifuko' => $validated['idadi_ya_mifuko'],
                'aina_ya_mzigo' => $validated['aina_ya_mzigo'],
                'tarehe' => $validated['tarehe'],
                'msimamizi_id' => $msimamizi->id,
                'katibu' => $katibu,
                'created_by' => $user->id,
            ])->load(['company', 'duara', 'msimamizi']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Kibali cha mawe '.$kibali->namba.' kimekatwa.',
            'data' => ['mawe' => $this->maweMaelezo($kibali)],
        ], 201);
    }

    public function walipuaji(Request $request): JsonResponse
    {
        $this->kataMthibitishaji($request->user());

        $walipuaji = Mlipuzi::forCompany($request->user()->company_id)
            ->with('mwenyeBc')
            ->orderBy('jina')
            ->get()
            ->map(fn (Mlipuzi $mtu) => $this->mlipuziFupi($mtu));

        return response()->json([
            'success' => true,
            'data' => ['walipuaji' => $walipuaji],
        ]);
    }

    public function picha(Request $request, int $mlipuzi): JsonResponse
    {
        $this->kataMthibitishaji($request->user());
        $request->validate([
            'picha' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'picha.required' => 'Chagua picha.',
            'picha.image' => 'Picha lazima iwe faili ya picha.',
            'picha.max' => 'Picha isizidi MB 2.',
        ]);

        $mtu = Mlipuzi::forCompany($request->user()->company_id)->find($mlipuzi);
        if (! $mtu) {
            return response()->json([
                'success' => false,
                'message' => 'Mlipuaji hajapatikana.',
            ], 404);
        }

        $mpya = $request->file('picha')->store('walipuaji', 'public');
        $zamani = $mtu->picha;
        $mtu->picha = $mpya;
        $mtu->save();

        if ($zamani && Storage::disk('public')->exists($zamani)) {
            Storage::disk('public')->delete($zamani);
        }

        return response()->json([
            'success' => true,
            'message' => 'Picha ya '.$mtu->jina.' imesasishwa.',
            'data' => ['mlipuzi' => $this->mlipuziFupi($mtu->fresh('mwenyeBc'))],
        ]);
    }

    public function scan(Request $request): JsonResponse
    {
        $code = trim((string) $request->query('code', ''));
        if ($code === '') {
            return response()->json([
                'success' => false,
                'message' => 'Weka msimbo ulioscan.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => $this->somaMsimbo($code, $request->user()->company_id),
        ]);
    }

    public function tumia(Request $request): JsonResponse
    {
        $code = trim((string) $request->input('code', ''));
        $parsed = $this->gawaMsimbo($code);
        if ($parsed['aina'] !== 'kibali') {
            return response()->json([
                'success' => false,
                'message' => 'Msimbo huu si kibali cha kulipua.',
            ], 422);
        }

        $kibali = Kibali::forCompany($request->user()->company_id)
            ->where('uthibitisho_token', $parsed['token'])
            ->first();

        if (! $kibali) {
            return response()->json([
                'success' => false,
                'message' => 'Kibali hakijatambulika. Si halali.',
            ], 404);
        }

        if ($kibali->imefungwa()) {
            return response()->json([
                'success' => false,
                'message' => 'Kibali hiki kimetumika tayari.',
                'data' => $this->somaMsimbo($code, $request->user()->company_id),
            ], 409);
        }

        $kibali->imefungwa_at = now();
        $kibali->save();

        return response()->json([
            'success' => true,
            'message' => 'Kibali '.$kibali->namba.' kimewekwa kimetumika.',
            'data' => $this->somaMsimbo($kibali->thibitishoUrl(), $request->user()->company_id),
        ]);
    }

    private function validatedKibali(Request $request): array
    {
        $validated = $request->validate([
            'hali' => ['required', Rule::in(['uzalishaji', 'ufreshiaji', 'ufukuziaji'])],
            'duara_id' => ['required', 'integer'],
            'msimamizi_id' => ['nullable', 'integer', 'required_without:msimamizi_jina'],
            'msimamizi_jina' => ['nullable', 'string', 'max:255', 'required_without:msimamizi_id'],
            'msimamizi_simu' => ['nullable', 'string', 'max:30', 'required_with:msimamizi_jina'],
            'idadi_ya_matundu' => ['required', 'integer', 'min:0', 'max:100000'],
            'tarehe' => ['required', 'date'],
            'mlipuzi_id' => ['required', 'integer'],
            'wachorongaji' => ['nullable', 'array', 'max:5'],
            'wachorongaji.*' => ['nullable', 'integer'],
            'aina_ya_mlipuko' => ['required', Rule::in(['cotex', 'dull_fuse'])],
            'msimamizi_wa_idara' => ['required', 'string', 'max:255'],
        ], [
            'hali.required' => 'Hali ya kibali inahitajika.',
            'duara_id.required' => 'Duara No. inahitajika.',
            'msimamizi_id.required_without' => 'Chagua msimamizi wa duara, au andika jina na simu.',
            'mlipuzi_id.required' => 'Jina la mlipuaji linahitajika.',
            'aina_ya_mlipuko.required' => 'Chagua COTEX au DULL FUSE.',
            'msimamizi_wa_idara.required' => 'Jina la msimamizi wa idara linahitajika.',
        ]);

        $companyId = $request->user()->company_id;
        $duara = Duara::forCompany($companyId)->with('wasimamizi')->find($validated['duara_id']);
        if (! $duara || $duara->hali === 'imefungwa') {
            throw ValidationException::withMessages([
                'duara_id' => 'Chagua duara linalofanya kazi.',
            ]);
        }

        $msimamizi = Msimamizi::chaguaAuAndika(
            $duara,
            $validated['msimamizi_id'] ?? null,
            $request->input('msimamizi_jina'),
            $request->input('msimamizi_simu')
        );

        $mlipuzi = Mlipuzi::forCompany($companyId)->with('mwenyeBc')->find($validated['mlipuzi_id']);
        if (! $mlipuzi || $mlipuzi->hali === 'blocked') {
            throw ValidationException::withMessages([
                'mlipuzi_id' => 'Chagua mlipuaji ambaye hajafungwa.',
            ]);
        }

        $wachorongaji = [];
        $seen = [(int) $mlipuzi->id => true];
        foreach (array_values($request->input('wachorongaji', [])) as $index => $id) {
            if ($id === null || $id === '') {
                continue;
            }
            $nafasi = $index + 1;
            if ($nafasi > 5) {
                break;
            }
            $mtu = Mlipuzi::forCompany($companyId)->find($id);
            if (! $mtu || $mtu->hali !== 'active') {
                throw ValidationException::withMessages([
                    'wachorongaji' => 'Chagua wachorongaji walio active.',
                ]);
            }
            if (isset($seen[$mtu->id])) {
                throw ValidationException::withMessages([
                    'wachorongaji' => 'Mtu mmoja hawezi kurudiwa kwenye kibali.',
                ]);
            }
            $seen[$mtu->id] = true;
            $wachorongaji[$nafasi] = $mtu->id;
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
            'katibu' => trim((string) $request->user()->name),
            'wachorongaji' => $wachorongaji,
        ];
    }

    private function somaMsimbo(string $code, int $companyId): array
    {
        $parsed = $this->gawaMsimbo($code);

        if ($parsed['aina'] === 'kibali' || $parsed['aina'] === 'haijulikani') {
            $kibali = Kibali::forCompany($companyId)
                ->with(['duara', 'mlipuzi', 'msimamizi'])
                ->where('uthibitisho_token', $parsed['token'])
                ->first();
            if ($kibali) {
                $kimetumika = $kibali->imefungwa();

                return [
                    'aina' => 'kibali',
                    'halali' => true,
                    'kimetumika' => $kimetumika,
                    'ujumbe' => $kimetumika
                        ? 'Kibali ni halali, lakini kimetumika tayari.'
                        : 'Kibali ni halali na bado hakijatumika.',
                    'kibali' => $this->kibaliMaelezo($kibali),
                ];
            }
        }

        if ($parsed['aina'] === 'mawe' || $parsed['aina'] === 'haijulikani') {
            $mawe = KibaliChaMawe::forCompany($companyId)
                ->with(['duara', 'msimamizi'])
                ->where('uthibitisho_token', $parsed['token'])
                ->first();
            if ($mawe) {
                return [
                    'aina' => 'mawe',
                    'halali' => true,
                    'kimetumika' => false,
                    'ujumbe' => 'Kibali cha '.$mawe->ainaLabel().' ni halali.',
                    'mawe' => [
                        'namba' => $mawe->namba,
                        'tarehe' => optional($mawe->tarehe)->format('d/m/Y'),
                        'duara' => $mawe->duara->namba ?? null,
                        'aina' => $mawe->ainaLabel(),
                        'mifuko' => $mawe->idadi_ya_mifuko,
                        'msimamizi' => $mawe->msimamizi->jina ?? null,
                        'katibu' => $mawe->katibu,
                    ],
                ];
            }
        }

        if ($parsed['aina'] === 'mlipuzi' || $parsed['aina'] === 'haijulikani') {
            $mtu = Mlipuzi::forCompany($companyId)
                ->with('mwenyeBc')
                ->where('uthibitisho_token', $parsed['token'])
                ->first();
            if ($mtu) {
                $amefungwa = $mtu->hali === 'blocked';

                return [
                    'aina' => 'mlipuzi',
                    'halali' => ! $amefungwa,
                    'kimetumika' => false,
                    'ujumbe' => $amefungwa
                        ? 'Kitambulisho ni halisi, lakini mlipuaji amefungwa.'
                        : 'Kitambulisho ni halali. Mlipuaji yuko active.',
                    'mlipuzi' => $this->mlipuziFupi($mtu),
                ];
            }
        }

        return [
            'aina' => 'haijulikani',
            'halali' => false,
            'kimetumika' => false,
            'ujumbe' => 'Haijatambulika. Si halali.',
        ];
    }

    private function gawaMsimbo(string $code): array
    {
        $code = trim($code);
        if (preg_match('#thibitisha/kibali/([A-Za-z0-9]+)#', $code, $m)) {
            return ['aina' => 'kibali', 'token' => $m[1]];
        }
        if (preg_match('#thibitisha/mawe/([A-Za-z0-9]+)#', $code, $m)) {
            return ['aina' => 'mawe', 'token' => $m[1]];
        }
        if (preg_match('#thibitisha/mlipuaji/([A-Za-z0-9]+)#', $code, $m)) {
            return ['aina' => 'mlipuzi', 'token' => $m[1]];
        }

        return ['aina' => 'haijulikani', 'token' => $code];
    }

    private function kibaliMaelezo(Kibali $kibali): array
    {
        return [
            'id' => $kibali->id,
            'namba' => $kibali->namba,
            'tarehe' => optional($kibali->tarehe)->format('d/m/Y'),
            'duara' => $kibali->duara->namba ?? null,
            'mlipuaji' => $kibali->mlipuzi->jina ?? null,
            'msimamizi' => $kibali->msimamizi->jina ?? null,
            'hali' => $kibali->haliLabel(),
            'aina' => $kibali->aina_ya_mlipuko === 'dull_fuse' ? 'DULL FUSE' : 'COTEX',
            'matundu' => $kibali->idadi_ya_matundu,
            'bc_no' => $kibali->bc_no,
            'katibu' => $kibali->katibu,
            'msimamizi_wa_idara' => $kibali->msimamizi_wa_idara,
            'wachorongaji' => $kibali->relationLoaded('wachorongaji')
                ? $kibali->wachorongaji->pluck('jina')->values()
                : [],
            'kampuni' => $kibali->company?->name,
            'anuani' => $kibali->company?->address,
            'ukaguzi_url' => $kibali->thibitishoUrl(),
            'kimetumika' => $kibali->imefungwa(),
            'imetumika_saa' => optional($kibali->imefungwa_at)->format('d/m/Y H:i'),
            'token' => $kibali->uthibitisho_token,
        ];
    }

    private function maweMaelezo(KibaliChaMawe $kibali): array
    {
        return [
            'namba' => $kibali->namba,
            'tarehe' => optional($kibali->tarehe)->format('d/m/Y'),
            'duara' => $kibali->duara->namba ?? null,
            'mifuko' => $kibali->idadi_ya_mifuko,
            'aina' => $kibali->ainaLabel(),
            'msimamizi' => $kibali->msimamizi->jina ?? null,
            'katibu' => $kibali->katibu,
            'kampuni' => $kibali->company?->name,
            'anuani' => $kibali->company?->address,
            'ukaguzi_url' => $kibali->thibitishoUrl(),
        ];
    }

    private function mlipuziFupi(Mlipuzi $mtu): array
    {
        return [
            'id' => $mtu->id,
            'jina' => $mtu->jina,
            'bc_no' => $mtu->bcInayotumika(),
            'hali' => $mtu->hali,
            'simu' => $mtu->simu,
            'picha' => $mtu->pichaUrl(),
            'amefungwa' => $mtu->hali === 'blocked',
        ];
    }

    private function tokenResponse(User $user): JsonResponse
    {
        $user->tokens()->where('name', 'milipuko-mobile')->delete();
        $token = $user->createToken('milipuko-mobile')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Umeingia.',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => $this->formatUser($user),
            ],
        ]);
    }

    private function formatUser(User $user): array
    {
        $user->loadMissing(['branch', 'branches', 'company', 'locations']);
        $branches = $user->branches;
        if ($branches->isEmpty() && $user->branch) {
            $branches = collect([$user->branch]);
        }

        $branchId = (int) ($user->branch_id ?: $branches->first()?->id ?: 0);
        $locations = $user->locations;
        $location = $locations->first(fn ($loc) => (int) $loc->branch_id === $branchId && (int) $loc->pivot->is_default === 1)
            ?: $locations->first(fn ($loc) => (int) $loc->branch_id === $branchId)
            ?: $locations->first();

        if (! $location && $branchId) {
            $location = InventoryLocation::where('branch_id', $branchId)->orderBy('name')->first();
        }

        $locationRows = $locations->map(fn ($loc) => [
            'id' => $loc->id,
            'name' => $loc->name,
            'branch_id' => $loc->branch_id,
        ])->values();

        if ($location && $locationRows->where('id', $location->id)->isEmpty()) {
            $locationRows->push([
                'id' => $location->id,
                'name' => $location->name,
                'branch_id' => $location->branch_id,
            ]);
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'company' => $user->company?->name,
            'branch_id' => $branchId ?: null,
            'location_id' => $location?->id,
            'branch_name' => $branches->firstWhere('id', $branchId)?->name ?? $user->branch?->name,
            'location_name' => $location?->name,
            'branches' => $branches->map(fn ($b) => [
                'id' => $b->id,
                'name' => $b->name,
            ])->values(),
            'locations' => $locationRows,
            'roles' => $user->getRoleNames()->values(),
            'verifier_only' => $this->niMthibitishajiPekee($user),
        ];
    }

    private function niMthibitishajiPekee(User $user): bool
    {
        $roles = $user->getRoleNames()->map(fn ($name) => strtolower($name));
        if (! $roles->contains('verifier')) {
            return false;
        }

        return $roles->intersect(['super-admin', 'admin', 'manager'])->isEmpty();
    }

    private function kataMthibitishaji(User $user): void
    {
        if ($this->niMthibitishajiPekee($user)) {
            abort(403, 'Akaunti ya Verifier inathibitisha vibali na vitambulisho tu.');
        }
    }

    private function branchId(Request $request, User $user): ?int
    {
        $header = $request->header('X-Branch-Id');
        if (is_numeric($header)) {
            return (int) $header;
        }

        return $user->branch_id;
    }

    private function hakikishaRoleYaVerifier(): void
    {
        $role = Role::firstOrCreate([
            'name' => 'Verifier',
            'guard_name' => 'web',
        ]);
        if (blank($role->description)) {
            $role->description = 'Askari wa geti na watoa vilipuzi. Wanathibitisha vibali na vitambulisho tu.';
            $role->save();
        }
    }
}
