<?php

namespace App\Http\Controllers\Milipuko;

use App\Http\Controllers\Controller;
use App\Models\Milipuko\Kibali;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class TaarifaController extends Controller
{
    public function pdf(Request $request)
    {
        $validated = $request->validate([
            'tarehe' => ['required', 'date'],
            'hali' => ['required', Rule::in(['uzalishaji', 'ufreshiaji', 'ufukuziaji'])],
        ], [
            'tarehe.required' => 'Tarehe inahitajika.',
            'tarehe.date' => 'Tarehe si sahihi.',
            'hali.required' => 'Hali ya mlipuko inahitajika.',
            'hali.in' => 'Hali ni Uzalishaji, Ufreshiaji au Ufukuziaji.',
        ]);

        $haliLebo = [
            'uzalishaji' => 'Uzalishaji',
            'ufreshiaji' => 'Ufreshiaji',
            'ufukuziaji' => 'Ufukuziaji',
        ][$validated['hali']];

        $vibali = Kibali::forCompany(auth()->user()->company_id)
            ->with(['duara', 'mlipuzi', 'msimamizi'])
            ->whereDate('tarehe', $validated['tarehe'])
            ->where('hali', $validated['hali'])
            ->get()
            ->sortBy(fn (Kibali $kibali) => sprintf(
                '%s-%s',
                $kibali->duara->namba ?? '',
                $kibali->namba
            ))
            ->values();

        $company = auth()->user()->company;
        $tarehe = Carbon::parse($validated['tarehe']);

        $pdf = Pdf::loadView('milipuko.taarifa.pdf', compact('vibali', 'company', 'tarehe', 'haliLebo'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('taarifa-milipuko-'.$tarehe->format('Y-m-d').'-'.$validated['hali'].'.pdf');
    }
}
