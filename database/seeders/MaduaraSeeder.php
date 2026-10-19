<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Milipuko\Duara;
use Illuminate\Database\Seeder;

class MaduaraSeeder extends Seeder
{
    /**
     * Maduara ya Msasa Gold Mine Bukombe-Geita.
     * Kila namba inasajiliwa mara moja. Maelezo ni eneo tu.
     */
    public function run(): void
    {
        $company = Company::query()->orderBy('id')->first();

        if (! $company) {
            return;
        }

        $branchId = Branch::query()
            ->where('company_id', $company->id)
            ->orderBy('id')
            ->value('id');

        Duara::query()
            ->where('company_id', $company->id)
            ->where('maelezo', 'like', 'Msasa Gold Mine Bukombe-Geita.%')
            ->whereDoesntHave('vibali')
            ->whereDoesntHave('vibaliVyaMawe')
            ->whereDoesntHave('uzalishaji')
            ->delete();

        $mpya = 0;
        $zilizokuwepo = 0;

        foreach ($this->maeneo() as $eneo => $nambaZote) {
            foreach ($this->pekee($nambaZote) as $namba) {
                $duara = Duara::firstOrNew([
                    'company_id' => $company->id,
                    'namba' => $namba,
                ]);

                $iliyokuwepo = $duara->exists;
                $duara->maelezo = $eneo;

                if (! $iliyokuwepo) {
                    $duara->branch_id = $branchId;
                    $duara->hali = 'inafanya_kazi';
                }

                $duara->save();

                if ($iliyokuwepo) {
                    $zilizokuwepo++;
                } else {
                    $mpya++;
                }
            }
        }

        $this->command?->info("Maduara: {$mpya} mapya, {$zilizokuwepo} yalishakuwepo.");
    }

    /**
     * @param  array<int, string>  $namba
     * @return array<int, string>
     */
    private function pekee(array $namba): array
    {
        $zilizoonwa = [];

        foreach ($namba as $moja) {
            $moja = trim($moja);
            if ($moja === '' || isset($zilizoonwa[$moja])) {
                continue;
            }
            $zilizoonwa[$moja] = true;
        }

        return array_keys($zilizoonwa);
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function maeneo(): array
    {
        return [
            'Eneo la Fensi' => [
                'KO164B', '86B', '80B', '21B', '79B', '78B', '87B', '77B', '133/24A',
                '73A', '166B', '167B', '72A', '144A', '5G', '79A', '67A', '168A',
                '62A', '64A', '61A', '120A', '58A', '57A', '63A', '55A', '92A',
                '54A', '96A', '50A', '48/49A', '47A', '45A',
            ],
            'Eneo la Mti Mkubwa' => [
                'KO44A', '9A', 'KO42A', 'KO195A', 'KO18A', '94A', 'KO17A', 'KO16A',
                'KO15A', 'KO14A', 'KO13A', 'KO12A', 'KO11A', 'KO10A', 'KO29A', 'KO28A',
                'KO6A', 'KO31A', 'KO36A', 'KO32A', 'KO3A', 'KO34A',
            ],
            'Eneo la Gata' => [
                'KO82B', 'KO85B', 'KO83B', 'KO84B', 'KO88B', '8B', 'KO90B', 'KO89B',
                'KO91/96B', 'KO93B', 'KO97/121B', 'KO122B', 'KO123/127B', 'KO95B',
                'KO128B', 'KO129B', 'KO102B', 'KO126B', 'KO130B', 'KO131B', 'KO132B',
            ],
            'Eneo la Tanroad' => [
                'KO137-B', 'KO135-B', 'KO138B', 'KO140B', 'KO139B', 'KO141B', 'KO143B',
                'KO142B', 'KO145B', 'KO146B', 'KO127B', 'KO148B', 'KO154B', 'KO420T', 'KO155B',
            ],
            "Korongo la 'T' Fensi" => [
                '31A', 'KO30A', 'KO186A', 'KO7A', 'KO8A', 'KO5A', 'KO4A', 'KO2A',
                '122T', 'KO1A', '78T',
            ],
        ];
    }
}
