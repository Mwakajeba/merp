<?php

namespace App\Http\Controllers\Milipuko;

use App\Http\Controllers\Controller;
use App\Models\Milipuko\Duara;
use App\Models\Milipuko\KibaliChaMawe;
use App\Models\Milipuko\UzalishajiWaDuara;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UzalishajiWaMaduaraController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;

        $rekodi = UzalishajiWaDuara::forCompany($companyId)
            ->with('duara')
            ->addSelect([
                'mifuko_ya_mawe' => KibaliChaMawe::selectRaw('COALESCE(SUM(idadi_ya_mifuko), 0)')
                    ->whereColumn('vibali_vya_mawe.company_id', 'uzalishaji_wa_maduara.company_id')
                    ->whereColumn('vibali_vya_mawe.duara_id', 'uzalishaji_wa_maduara.duara_id')
                    ->whereColumn('vibali_vya_mawe.tarehe', 'uzalishaji_wa_maduara.tarehe')
                    ->where('aina_ya_mzigo', 'mawe'),
                'mifuko_ya_chorongeo' => KibaliChaMawe::selectRaw('COALESCE(SUM(idadi_ya_mifuko), 0)')
                    ->whereColumn('vibali_vya_mawe.company_id', 'uzalishaji_wa_maduara.company_id')
                    ->whereColumn('vibali_vya_mawe.duara_id', 'uzalishaji_wa_maduara.duara_id')
                    ->whereColumn('vibali_vya_mawe.tarehe', 'uzalishaji_wa_maduara.tarehe')
                    ->where('aina_ya_mzigo', 'chorongeo'),
            ])
            ->orderByDesc('tarehe')
            ->orderBy('duara_id')
            ->paginate(30);

        $maduara = Duara::forCompany($companyId)
            ->where('hali', 'inafanya_kazi')
            ->orderBy('namba')
            ->get();

        return view('milipuko.uzalishaji.index', compact('rekodi', 'maduara'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tarehe' => ['required', 'date'],
            'duara_id' => ['required', 'integer'],
        ], [
            'tarehe.required' => 'Tarehe inahitajika.',
            'duara_id.required' => 'Chagua duara.',
        ]);

        $companyId = auth()->user()->company_id;
        $duara = Duara::forCompany($companyId)
            ->where('hali', 'inafanya_kazi')
            ->find($data['duara_id']);

        if (! $duara) {
            throw ValidationException::withMessages([
                'duara_id' => 'Chagua duara linalofanya kazi.',
            ]);
        }

        $lipo = UzalishajiWaDuara::forCompany($companyId)
            ->where('duara_id', $duara->id)
            ->whereDate('tarehe', $data['tarehe'])
            ->exists();

        if ($lipo) {
            throw ValidationException::withMessages([
                'duara_id' => 'Duara hili tayari limesajiliwa kuwa limezalisha tarehe hii.',
            ]);
        }

        UzalishajiWaDuara::create([
            'company_id' => $companyId,
            'branch_id' => session('branch_id') ?: auth()->user()->branch_id,
            'duara_id' => $duara->id,
            'tarehe' => $data['tarehe'],
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('milipuko.uzalishaji.index')
            ->with('success', 'Duara '.$duara->namba.' limesajiliwa kwenye uzalishaji.');
    }

    public function destroy(UzalishajiWaDuara $uzalishaji)
    {
        $uzalishaji->delete();

        return redirect()
            ->route('milipuko.uzalishaji.index')
            ->with('success', 'Uzalishaji umeondolewa.');
    }
}
