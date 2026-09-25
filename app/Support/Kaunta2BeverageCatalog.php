<?php

namespace App\Support;

/**
 * Kaunta 2 location beverage catalog from notebook pages
 * (opening balance + item import templates; prices in TZS).
 */
class Kaunta2BeverageCatalog
{
    /**
     * @return list<array{
     *   name: string,
     *   code: string,
     *   quantity: int|float,
     *   cost_price: int|float|string,
     *   unit_price: int|float|string,
     *   unit_of_measure: string,
     *   track_expiry: bool,
     *   minimum_stock: int,
     *   maximum_stock: int,
     *   reorder_level: int
     * }>
     */
    public static function all(): array
    {
        // [name, code, qty, cost, sell, uom, track_expiry, min, max, reorder]
        $rows = [
            // Page: beers / popular (Kaunta 2)
            ['Serengeti Lite', 'K2-SLITE', 73, 1640, 2500, 'bottles', true, 20, 200, 40],
            ['Serengeti Lager ndogo', 'K2-SLAG-NDG', 51, 1600, 2500, 'bottles', true, 15, 150, 30],
            ['Serengeti Lager kubwa', 'K2-SLAG-KB', 30, 1775, 3000, 'bottles', true, 10, 100, 15],
            ['Castle Lite', 'K2-CLITE', 102, 1700, 2500, 'bottles', true, 30, 300, 50],
            ['Kilimanjaro ndogo', 'K2-KILI-NDG', 40, 1700, 2500, 'bottles', true, 10, 150, 20],
            ['Kilimanjaro kubwa', 'K2-KILI-KB', 18, 1800, 3000, 'bottles', true, 5, 80, 10],
            ['Safari ndogo', 'K2-SAFARI-NDG', 21, 1700, 2500, 'bottles', true, 5, 100, 10],
            ['Safari kubwa', 'K2-SAFARI-KB', 29, 1800, 3000, 'bottles', true, 10, 100, 15],
            ['Guinness Smooth', 'K2-GUINNESS', 18, 1720, 2500, 'bottles', true, 5, 80, 10],
            ['Redd\'s can', 'K2-REDDS', 12, 2200, 3000, 'cans', true, 3, 50, 6],
            ['Flying Fish', 'K2-FLYFISH', 37, 1650, 2500, 'bottles', true, 10, 150, 20],
            ['Flying Fish can', 'K2-FLYFISH-CAN', 12, 2200, 3000, 'cans', true, 3, 50, 6],
            ['Heineken', 'K2-HEINEKEN', 21, 3250, 5000, 'bottles', true, 5, 80, 10],
            ['Windhoek', 'K2-WINDHOEK', 14, 3333, 5000, 'bottles', true, 3, 50, 7],
            ['Bavaria', 'K2-BAVARIA', 6, 3250, 5000, 'bottles', true, 2, 40, 3],
            ['Kilimanjaro Lite', 'K2-KILI-LITE', 22, 2250, 3500, 'bottles', true, 5, 80, 10],
            ['Savanna Apple', 'K2-SAVAPPLE', 40, 2500, 3500, 'bottles', true, 10, 150, 20],
            ['Safari Cane', 'K2-SAFARICANE', 15, 2250, 3000, 'bottles', true, 5, 80, 8],
            ['Castle Lite can', 'K2-CLITE-CAN', 12, 2200, 3000, 'cans', true, 3, 50, 6],
            ['Brutal Fruit', 'K2-BRUTAL', 24, 3333, 5000, 'bottles', true, 5, 100, 12],
            ['Stamina', 'K2-STAMINA', 40, 1640, 2500, 'bottles', true, 10, 150, 20],
            ['Savanna', 'K2-SAVANNA', 12, 3666, 5000, 'bottles', true, 3, 50, 6],
            ['Smirnoff Ice', 'K2-SMIR-ICE', 32, 2600, 4000, 'bottles', true, 10, 100, 15],
            ['Smirnoff ndogo', 'K2-SMIR-NDG', 2, 8000, 12000, 'bottles', true, 1, 20, 1],
            ['Smirnoff kubwa', 'K2-SMIR-KB', 1, 15000, 20000, 'bottles', true, 1, 10, 1],
            ['Robertson', 'K2-ROBERT', 2, 16000, 25000, 'bottles', true, 1, 20, 1],
            ['Drostdy Hof ndogo', 'K2-DROST-NDG', 1, 11500, 17000, 'bottles', true, 1, 10, 1],
            ['Drostdy Hof kubwa', 'K2-DROST-KB', 1, 11500, 17000, 'bottles', true, 1, 10, 1],
            ['St. Anne', 'K2-STANNE', 1, 14000, 20000, 'bottles', true, 1, 10, 1],
            ['Imagi liter', 'K2-IMAGI-LT', 1, 3875, 5000, 'bottles', true, 1, 20, 1],
            ['Imagi kubwa', 'K2-IMAGI-KB', 1, 11500, 17000, 'bottles', true, 1, 10, 1],

            // Page: spirits / wines
            ['Alterwine', 'K2-ALTWINE', 1, 14000, 17000, 'bottles', true, 1, 10, 1],
            ['Heavenly ndogo', 'K2-HEAV-NDG', 1, 5400, 8000, 'bottles', true, 1, 10, 1],
            ['Heavenly kubwa', 'K2-HEAV-KB', 1, 11000, 17000, 'bottles', true, 1, 10, 1],
            ['Grand Malt', 'K2-GMALT', 16, 2291, 3000, 'bottles', true, 5, 80, 8],
            ['Imperial', 'K2-IMPERIAL', 11, 3250, 5000, 'bottles', true, 3, 50, 5],
            ['Pepsi', 'K2-PEPSI', 19, 916, 1500, 'bottles', true, 5, 80, 10],
            ['Red Label ndogo', 'K2-RL-NDG', 1, 14500, 20000, 'bottles', true, 1, 10, 1],
            ['Red Label kubwa', 'K2-RL-KB', 2, 46000, 60000, 'bottles', true, 1, 20, 1],
            ['Absolut liter', 'K2-ABS-LT', 2, 34000, 45000, 'bottles', true, 1, 20, 1],
            ['Captain Morgan ndogo', 'K2-CMORG-NDG', 3, 5000, 8000, 'bottles', true, 1, 20, 2],
            ['Captain Morgan kubwa', 'K2-CMORG-KB', 1, 24000, 30000, 'bottles', true, 1, 10, 1],
            ['Johnnie Walker', 'K2-JW', 2, 14000, 20000, 'bottles', true, 1, 20, 1],
            ['Chrome ndogo', 'K2-CHROME-NDG', 1, 4000, 8000, 'bottles', true, 1, 10, 1],
            ['Moonlight ndogo', 'K2-MOON-NDG', 4, 3166, 5000, 'bottles', true, 1, 30, 2],
            ['Moonlight kubwa', 'K2-MOON-KB', 2, 8333, 13000, 'bottles', true, 1, 20, 1],
            ['Ballantine\'s ndogo', 'K2-BALLAN-NDG', 1, 15500, 20000, 'bottles', true, 1, 10, 1],
            ['Ballantine\'s kubwa', 'K2-BALLAN-KB', 1, 46000, 60000, 'bottles', true, 1, 10, 1],
            ['Magic Moment kubwa', 'K2-MM-KB', 1, 24000, 30000, 'bottles', true, 1, 10, 1],
            ['Champagne', 'K2-CHAMP', 1, 15000, 17000, 'bottles', true, 1, 10, 1],
            ['Grant\'s kubwa', 'K2-GRANTS-KB', 2, 32500, 50000, 'bottles', true, 1, 20, 1],
            ['Dimple', 'K2-DIMPLE', 1, 14000, 17000, 'bottles', true, 1, 10, 1],
            ['Zenji ndogo', 'K2-ZENJI-NDG', 6, 6000, 10000, 'bottles', true, 2, 30, 3],
            ['K. Vant ndogo', 'K2-KVANT-NDG', 8, 3458, 5000, 'bottles', true, 2, 40, 4],
            ['K. Vant kubwa', 'K2-KVANT-KB', 7, 9583, 15000, 'bottles', true, 2, 30, 3],
            ['H-Choir ndogo', 'K2-HCHOIR-NDG', 15, 3500, 5000, 'bottles', true, 5, 50, 8],
            ['H-Choir kubwa', 'K2-HCHOIR-KB', 2, 10500, 15000, 'bottles', true, 1, 20, 1],
            ['Konyagi ndogo', 'K2-KONY-NDG', 10, 4400, 7000, 'bottles', true, 3, 50, 5],
            ['Konyagi liter', 'K2-KONY-LT', 5, 8250, 12000, 'bottles', true, 2, 30, 3],
            ['Konyagi kubwa', 'K2-KONY-KB', 5, 11000, 15000, 'bottles', true, 2, 30, 3],
            ['Jack Daniel\'s liter', 'K2-JD-LT', 1, 39000, 50000, 'bottles', true, 1, 10, 1],
            ['Jack Daniel\'s kubwa', 'K2-JD-KB', 1, 72000, 90000, 'bottles', true, 1, 10, 1],
            ['Apple Punch', 'K2-APPLEP', 12, 1200, 1500, 'bottles', true, 3, 50, 6],

            // Page: premium / soft drinks continuation
            ['Hennessy ndogo', 'K2-HEN-NDG', 1, 39000, 50000, 'bottles', true, 1, 10, 1],
            ['Hennessy liter', 'K2-HEN-LT', 1, 61000, 80000, 'bottles', true, 1, 10, 1],
            ['Hennessy kubwa', 'K2-HEN-KB', 1, 110000, 130000, 'bottles', true, 1, 10, 1],
            ['J. Master ndogo', 'K2-JM-NDG', 1, 22000, 30000, 'bottles', true, 1, 10, 1],
            ['J. Master liter', 'K2-JM-LT', 1, 31000, 45000, 'bottles', true, 1, 10, 1],
            ['J. Master kubwa', 'K2-JM-KB', 1, 43000, 60000, 'bottles', true, 1, 10, 1],
            ['Black & White ndogo', 'K2-BW-NDG', 2, 11000, 15000, 'bottles', true, 1, 20, 1],
            ['Black & White kubwa', 'K2-BW-KB', 1, 22000, 35000, 'bottles', true, 1, 10, 1],
            ['Vellour kubwa', 'K2-VELLOUR', 1, 36000, 60000, 'bottles', true, 1, 10, 1],
            ['Viceroy ndogo', 'K2-VIC-NDG', 2, 8500, 12000, 'bottles', true, 1, 20, 1],
            ['Viceroy kubwa', 'K2-VIC-KB', 1, 26000, 40000, 'bottles', true, 1, 10, 1],
            ['Amarula liter', 'K2-AMARULA-LT', 2, 19000, 25000, 'bottles', true, 1, 20, 1],
            ['Amarula kubwa', 'K2-AMARULA-KB', 2, 22000, 40000, 'bottles', true, 1, 20, 1],
            ['Condence ndogo', 'K2-CONDENCE', 1, 12000, 17000, 'bottles', true, 1, 10, 1],
            ['Red Bull', 'K2-REDBULL', 7, 3750, 5000, 'cans', true, 2, 50, 5],
            ['Azam Energy', 'K2-AZAMEN', 13, 520, 1000, 'cans', true, 5, 50, 8],
            ['Maji ndogo', 'K2-MAJI-NDG', 26, 458, 1000, 'bottles', false, 10, 100, 15],
            ['Maji kubwa', 'K2-MAJI-KB', 23, 833, 1500, 'bottles', false, 10, 100, 15],
            ['Soda', 'K2-SODA', 54, 583, 1000, 'bottles', true, 15, 150, 25],
            ['Desperados', 'K2-DESPERADOS', 12, 3416, 4000, 'bottles', true, 3, 50, 6],
        ];

        return array_map(static function (array $r): array {
            return [
                'name' => $r[0],
                'code' => $r[1],
                'quantity' => $r[2],
                'cost_price' => $r[3],
                'unit_price' => $r[4],
                'unit_of_measure' => $r[5],
                'track_expiry' => $r[6],
                'minimum_stock' => $r[7],
                'maximum_stock' => $r[8],
                'reorder_level' => $r[9],
            ];
        }, $rows);
    }

    /**
     * @return list<list<string>>
     */
    public static function itemImportRows(): array
    {
        $out = [];
        foreach (self::all() as $item) {
            $out[] = [
                $item['name'],
                $item['code'],
                'Kaunta2 on hand: ' . $item['quantity'],
                $item['unit_of_measure'],
                (string) $item['cost_price'],
                (string) $item['unit_price'],
                (string) $item['minimum_stock'],
                (string) $item['maximum_stock'],
                (string) $item['reorder_level'],
                $item['track_expiry'] ? 'Yes' : 'No',
            ];
        }

        return $out;
    }

    /**
     * @return list<list<string>>
     */
    public static function openingBalanceRows(): array
    {
        $out = [];
        foreach (self::all() as $item) {
            $out[] = [
                $item['name'],
                $item['code'],
                (string) $item['quantity'],
                (string) $item['cost_price'],
                $item['track_expiry'] ? 'true' : 'false',
                '',
            ];
        }

        return $out;
    }

    /**
     * @return array<string, array{quantity: int|float, cost_price: int|float|string, track_expiry: bool}>
     */
    public static function keyedByCode(): array
    {
        $map = [];
        foreach (self::all() as $item) {
            $map[$item['code']] = [
                'quantity' => $item['quantity'],
                'cost_price' => $item['cost_price'],
                'track_expiry' => $item['track_expiry'],
            ];
        }

        return $map;
    }
}
