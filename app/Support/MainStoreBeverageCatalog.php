<?php

namespace App\Support;

/**
 * Main Store (MTAJI STOO) beverage catalog from notebook pages
 * (opening balance + item import templates; prices in TZS).
 *
 * Where the notebook shows line totals, unit cost = round(total / qty).
 */
class MainStoreBeverageCatalog
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
            // Page with unit cost + sell (spirits / mixers)
            ['Coke', 'MS-COKE', 72, 2125, 4000, 'bottles', true, 20, 200, 40],
            ['Red Bull', 'MS-REDBULL', 48, 2125, 4000, 'cans', true, 10, 150, 20],
            ['Ceres Juice 1L', 'MS-CERES', 24, 4833, 8000, 'bottles', true, 5, 80, 10],
            ['Tropical', 'MS-TROPICAL', 12, 2166, 4000, 'bottles', true, 3, 50, 6],
            ['Smirnoff Ice 300ml', 'MS-SMIR-ICE', 3, 1200, 4000, 'bottles', true, 1, 30, 2],
            ['Grand Malt', 'MS-GMALT', 48, 2166, 5000, 'bottles', true, 10, 150, 20],
            ['Konyagi ndogo 200ml', 'MS-KONY-NDG', 24, 3833, 10000, 'bottles', true, 5, 100, 12],
            ['Konyagi liter 500ml', 'MS-KONY-LT', 8, 8125, 15000, 'bottles', true, 2, 40, 4],
            ['Konyagi kubwa 750ml', 'MS-KONY-KB', 6, 10333, 20000, 'bottles', true, 2, 40, 3],
            ['Gordon\'s Gin 200ml', 'MS-GORDON-NDG', 4, 12000, 20000, 'bottles', true, 1, 20, 2],
            ['Gordon\'s Gin kubwa', 'MS-GORDON-KB', 3, 37500, 52000, 'bottles', true, 1, 20, 2],
            ['Grant\'s ndogo 200ml', 'MS-GRANTS-NDG', 5, 14000, 35000, 'bottles', true, 1, 30, 2],
            ['Grant\'s liter 375ml', 'MS-GRANTS-LT', 3, 28000, 40000, 'bottles', true, 1, 20, 2],
            ['Amarula 375ml', 'MS-AMARULA-375', 3, 14500, 35000, 'bottles', true, 1, 20, 2],
            ['Amarula 700ml', 'MS-AMARULA-700', 3, 27000, 50000, 'bottles', true, 1, 20, 2],
            ['Black & White 200ml', 'MS-BW-200', 6, 8500, 20000, 'bottles', true, 2, 40, 3],
            ['Black & White 750ml', 'MS-BW-750', 3, 21000, 45000, 'bottles', true, 1, 20, 2],
            ['Zenji 200ml', 'MS-ZENJI', 12, 5833, 10000, 'bottles', true, 3, 50, 6],
            ['Jack Daniel\'s 200ml', 'MS-JD-200', 2, 24000, 40000, 'bottles', true, 1, 20, 1],
            ['Jack Daniel\'s 350ml', 'MS-JD-350', 3, 37000, 60000, 'bottles', true, 1, 20, 2],
            ['Jack Daniel\'s 700ml', 'MS-JD-700', 2, 70000, 110000, 'bottles', true, 1, 10, 1],
            ['Magic Moment 375ml', 'MS-MM-375', 3, 11000, 20000, 'bottles', true, 1, 20, 2],
            ['Magic Moment 700ml', 'MS-MM-700', 3, 20000, 35000, 'bottles', true, 1, 20, 2],
            ['Captain Morgan 200ml', 'MS-CMORG-200', 12, 4541, 15000, 'bottles', true, 3, 50, 6],
            ['Captain Morgan 750ml', 'MS-CMORG-750', 6, 16166, 40000, 'bottles', true, 2, 30, 3],
            ['Absolut Vodka 200ml', 'MS-ABS-200', 3, 16000, 30000, 'bottles', true, 1, 20, 2],
            ['Absolut Vodka 375ml', 'MS-ABS-375', 3, 28000, 50000, 'bottles', true, 1, 20, 2],
            ['Absolut Vodka 750ml', 'MS-ABS-750', 3, 45000, 100000, 'bottles', true, 1, 20, 2],
            ['Viceroy 750ml', 'MS-VIC-750', 3, 9000, 17000, 'bottles', true, 1, 20, 2],
            ['Jameson 200ml', 'MS-JAM-200', 3, 21000, 35000, 'bottles', true, 1, 20, 2],
            ['Jameson 350ml', 'MS-JAM-350', 2, 36000, 50000, 'bottles', true, 1, 10, 1],
            ['Jameson 700ml', 'MS-JAM-700', 2, 59000, 90000, 'bottles', true, 1, 10, 1],

            // MTAJI STOO page (qty + line total → unit cost)
            ['Mirinda', 'MS-MIRINDA', 14, 916, 1500, 'bottles', true, 5, 80, 10],
            ['Maji ndogo', 'MS-MAJI-NDG', 53, 460, 1000, 'bottles', false, 15, 200, 30],
            ['K. Vant kubwa', 'MS-KVANT-KB', 4, 8583, 15000, 'bottles', true, 1, 20, 2],
            ['Grey Wine', 'MS-GREYWINE', 1, 11000, 17000, 'bottles', true, 1, 10, 1],
            ['Soda', 'MS-SODA', 263, 583, 1000, 'bottles', true, 50, 500, 100],
            ['Serengeti Lager kubwa', 'MS-SLAG-KB', 71, 1775, 3000, 'bottles', true, 20, 200, 40],
            ['Castle Lite', 'MS-CLITE', 100, 1700, 2500, 'bottles', true, 30, 300, 50],
            ['Apple Punch', 'MS-APPLEP', 12, 1000, 1500, 'bottles', true, 3, 50, 6],
            ['Pepsi', 'MS-PEPSI', 23, 916, 1500, 'bottles', true, 5, 80, 10],
            ['Safari kubwa', 'MS-SAFARI-KB', 59, 1800, 3000, 'bottles', true, 15, 150, 30],
            ['Safari ndogo', 'MS-SAFARI-NDG', 90, 1700, 2500, 'bottles', true, 20, 250, 45],
            ['Bavaria', 'MS-BAVARIA', 9, 3250, 5000, 'bottles', true, 2, 40, 5],
            ['Smirnoff liter', 'MS-SMIR-LT', 2, 36000, 50000, 'bottles', true, 1, 10, 1],
            ['Africana', 'MS-AFRICANA', 36, 2250, 3500, 'bottles', true, 10, 100, 15],
            ['Redd\'s', 'MS-REDDS', 17, 2250, 3000, 'cans', true, 5, 80, 10],
            ['Safari Cane', 'MS-SAFARICANE', 12, 2250, 3000, 'bottles', true, 3, 50, 6],
            ['Windhoek', 'MS-WINDHOEK', 10, 3333, 5000, 'bottles', true, 3, 50, 5],
            ['Heineken', 'MS-HEINEKEN', 12, 3250, 5000, 'bottles', true, 3, 50, 6],
            ['Kilimanjaro Lite', 'MS-KILI-LITE', 5, 2250, 3500, 'bottles', true, 2, 40, 3],
            ['Chrome kubwa', 'MS-CHROME-KB', 3, 16500, 25000, 'bottles', true, 1, 20, 2],
            ['St. Anne', 'MS-STANNE', 1, 14000, 20000, 'bottles', true, 1, 10, 1],
            ['Drostdy Hof kubwa', 'MS-DROST-KB', 3, 15000, 25000, 'bottles', true, 1, 20, 2],
            ['Imagi kubwa', 'MS-IMAGI-KB', 2, 11500, 17000, 'bottles', true, 1, 20, 1],

            // Continuation page (qty + line total)
            ['Brutal Fruit', 'MS-BRUTAL', 25, 9353, 15000, 'bottles', true, 5, 100, 12],
            ['Viceroy ndogo', 'MS-VIC-NDG', 1, 8000, 12000, 'bottles', true, 1, 10, 1],
            ['Ballantine\'s ndogo', 'MS-BALLAN-NDG', 2, 16000, 25000, 'bottles', true, 1, 20, 1],
            ['J&B ndogo', 'MS-JB-NDG', 1, 12000, 20000, 'bottles', true, 1, 10, 1],
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
                'Main Store on hand: ' . $item['quantity'],
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
