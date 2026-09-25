<?php

namespace App\Support;

/**
 * VIP location beverage catalog from MTAJI VIP notebook pages
 * (opening balance + item import templates; prices in TZS).
 */
class VipBeverageCatalog
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
            // Page: MTAJI VIP (purchase + sell prices)
            ['Red Label ndogo 200ml', 'VIP-RL-NDG', 12, 12833, 25000, 'bottles', true, 2, 50, 5],
            ['St. Anne', 'VIP-STANNE', 6, 12083, 25000, 'bottles', true, 1, 30, 3],
            ['Robertson', 'VIP-ROBERT', 3, 16000, 30000, 'bottles', true, 1, 20, 2],
            ['Altarina', 'VIP-ALTARINA', 6, 10833, 25000, 'bottles', true, 1, 30, 3],
            ['Drostdy Hof kubwa 750ml', 'VIP-DROST-KB', 6, 14000, 25000, 'bottles', true, 1, 30, 3],
            ['Drostdy Hof ndogo 375ml', 'VIP-DROST-NDG', 12, 7200, 18000, 'bottles', true, 2, 50, 5],
            ['Britannia Royal 750ml', 'VIP-BRIT', 2, 12000, 20000, 'bottles', true, 1, 20, 1],
            ['Imagi ndogo 200ml', 'VIP-IMAGI-NDG', 24, 3750, 8000, 'bottles', true, 5, 100, 10],
            ['Imagi 350ml', 'VIP-IMAGI-350', 24, 3354, 8000, 'bottles', true, 5, 100, 10],
            ['Imagi kubwa 750ml', 'VIP-IMAGI-KB', 6, 12000, 25000, 'bottles', true, 1, 30, 3],
            ['Four Cousins 700ml', 'VIP-FOURCOUS', 3, 14000, 30000, 'bottles', true, 1, 20, 2],
            ['Dominix 750ml', 'VIP-DOMINIX', 6, 11000, 25000, 'bottles', true, 1, 30, 3],
            ['Domino Ltd 750ml', 'VIP-DOMINO-LTD', 6, 9000, 25000, 'bottles', true, 1, 30, 3],
            ['Domino ndogo 200ml', 'VIP-DOMINO-NDG', 12, 3332, 8000, 'bottles', true, 2, 50, 5],
            ['Domino 750ml', 'VIP-DOMINO-KB', 6, 10833, 25000, 'bottles', true, 1, 30, 3],
            ['Pearly Bay 700ml', 'VIP-PEARLY', 6, 17000, 30000, 'bottles', true, 1, 30, 3],
            ['Hennessy 200ml', 'VIP-HEN-200', 3, 36000, 60000, 'bottles', true, 1, 20, 2],
            ['Hennessy 350ml', 'VIP-HEN-350', 2, 65000, 90000, 'bottles', true, 1, 10, 1],
            ['Hennessy 700ml', 'VIP-HEN-700', 3, 120000, 150000, 'bottles', true, 1, 10, 1],
            ['J&B 200ml', 'VIP-JB-200', 12, 11500, 20000, 'bottles', true, 2, 50, 5],
            ['J&B 700ml', 'VIP-JB-700', 6, 31500, 70000, 'bottles', true, 1, 30, 3],
            ['Red Bull', 'VIP-REDBULL', 48, 2625, 6000, 'cans', true, 10, 200, 20],
            ['J. Walker 200ml', 'VIP-JW-200', 6, 20000, 40000, 'bottles', true, 1, 30, 3],
            ['J. Walker 375ml', 'VIP-JW-375', 4, 29000, 50000, 'bottles', true, 1, 20, 2],
            ['J. Walker 750ml', 'VIP-JW-750', 3, 42000, 70000, 'bottles', true, 1, 20, 2],
            ['J. Walker 1 Ltr', 'VIP-JW-1L', 3, 58000, 100000, 'bottles', true, 1, 20, 2],
            ['Brutal Fruit 235ml', 'VIP-BRUTAL', 72, 3166, 7000, 'bottles', true, 10, 200, 24],
            ['Heineken', 'VIP-HEINEKEN', 3, 3179, 7000, 'bottles', true, 1, 50, 5],
            ['Windhoek', 'VIP-WINDHOEK', 1, 3179, 7000, 'bottles', true, 1, 30, 2],
            ['Savanna', 'VIP-SAVANNA', 48, 3000, 7000, 'bottles', true, 10, 150, 20],
            ['Savanna (alt)', 'VIP-SAVANNA2', 24, 3250, 7000, 'bottles', true, 5, 100, 10],
            ['Kilimanjaro Lite 330ml', 'VIP-KILI-LITE', 24, 1929, 4000, 'bottles', true, 5, 100, 10],

            // Continuation page (qty × unit cost = total value; sell estimated)
            ['Hunting Lodge 300ml', 'VIP-HL-300', 3, 1000, 2000, 'bottles', true, 1, 20, 2],
            ['Hunting Lodge 700ml', 'VIP-HL-700', 2, 2250, 4000, 'bottles', true, 1, 20, 1],
            ['Tree Lemon 750ml', 'VIP-TREE-750', 6, 12000, 18000, 'bottles', true, 1, 30, 3],
            ['Tree Lemon 500ml', 'VIP-TREE-500', 12, 4375, 7000, 'bottles', true, 2, 50, 5],
            ['Gilbey\'s Gin 700ml', 'VIP-GILBEY', 2, 12500, 20000, 'bottles', true, 1, 20, 1],
            ['J. Walker Black Label 200ml', 'VIP-BL-200', 1, 21000, 35000, 'bottles', true, 1, 10, 1],
            ['Black Label 750ml', 'VIP-BL-750', 2, 65000, 100000, 'bottles', true, 1, 10, 1],
            ['Ballantine\'s 200ml', 'VIP-BALLAN-200', 5, 10000, 18000, 'bottles', true, 1, 30, 2],
            ['Ballantine\'s 700ml', 'VIP-BALLAN-700', 3, 42000, 65000, 'bottles', true, 1, 20, 2],
            ['Serengeti Wine 700ml', 'VIP-SEREN-WINE', 6, 15000, 25000, 'bottles', true, 1, 30, 3],
            ['KWV', 'VIP-KWV', 2, 25000, 40000, 'bottles', true, 1, 20, 1],
            ['Tall Horse 700ml', 'VIP-TALLHORSE', 2, 20000, 35000, 'bottles', true, 1, 20, 1],
            ['Ice Tropez 275ml', 'VIP-TROPEZ', 12, 14000, 22000, 'bottles', true, 2, 50, 5],
            ['Freixenet 700ml', 'VIP-FREIX', 1, 50000, 80000, 'bottles', true, 1, 10, 1],
            ['Falconer\'s Honey 700ml', 'VIP-FALCON', 2, 20000, 35000, 'bottles', true, 1, 20, 1],
            ['Jack Daniel\'s 500ml', 'VIP-JD-500', 1, 60000, 90000, 'bottles', true, 1, 10, 1],
            ['Strawberry Lips', 'VIP-STRAW', 1, 35000, 55000, 'bottles', true, 1, 10, 1],
            ['Martell 700ml', 'VIP-MARTELL', 1, 125000, 180000, 'bottles', true, 1, 10, 1],
            ['Flying Fish', 'VIP-FLYFISH', 40, 1000, 2500, 'bottles', true, 10, 150, 20],
            ['Serengeti Lager ndogo', 'VIP-SLAG-NDG', 20, 1000, 2500, 'bottles', true, 5, 100, 10],
            ['Serengeti Lite', 'VIP-SLITE', 25, 1650, 3000, 'bottles', true, 5, 100, 10],
            ['Safari ndogo', 'VIP-SAFARI-NDG', 20, 1000, 2500, 'bottles', true, 5, 100, 10],
            ['Castle Lite', 'VIP-CLITE', 60, 1650, 3000, 'bottles', true, 10, 200, 20],
            ['Kilimanjaro ndogo 370ml', 'VIP-KILI-NDG', 40, 1000, 2500, 'bottles', true, 10, 150, 20],
            ['Serengeti Lite (alt)', 'VIP-SLITE2', 25, 2450, 4000, 'bottles', true, 5, 100, 10],
            ['Guinness 330ml', 'VIP-GUINNESS', 25, 1650, 3000, 'bottles', true, 5, 100, 10],
            ['Savanna Apple', 'VIP-SAVAPPLE', 40, 2500, 4000, 'bottles', true, 10, 150, 20],
            ['Desperados', 'VIP-DESPERADOS', 48, 3250, 5000, 'bottles', true, 10, 150, 20],
            ['Cernic', 'VIP-CERNIC', 24, 3125, 5000, 'bottles', true, 5, 100, 10],
            ['Camino Tequila 700ml', 'VIP-CAMINO', 1, 68000, 100000, 'bottles', true, 1, 10, 1],
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
                'VIP on hand: ' . $item['quantity'],
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
     * Rows for opening balance CSV (item_name, item_code, quantity, unit_cost, has_expiry_date, expiry_date).
     *
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
