<?php

namespace App\Support;

/**
 * Shared Kaunta / bar beverage sample catalog for inventory item
 * and opening-balance CSV templates (prices in TZS).
 */
class BeverageSampleCatalog
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
        $rows = [
            // Kaunta 1 — spirits / wines
            ['Red Label ndogo', 'RL-NDG', 2, 14000, 18000, 'bottles', true, 1, 20, 2],
            ['Benedictine kubwa', 'BNG-KB', 1, 12000, 15000, 'bottles', true, 1, 10, 1],
            ['St. Anne', 'STANNE', 2, 16000, 20000, 'bottles', true, 1, 20, 2],
            ['Robertson', 'ROBERT', 1, 16500, 25000, 'bottles', true, 1, 10, 1],
            ['Alterwine', 'ALTWINE', 3, 14500, 17000, 'bottles', true, 1, 30, 3],
            ['Smirnoff kubwa', 'SMIR-KB', 2, 15000, 20000, 'bottles', true, 1, 20, 2],
            ['Smirnoff ndogo', 'SMIR-NDG', 3, 8500, 12000, 'bottles', true, 1, 30, 3],
            ['Champagne', 'CHAMP', 1, 15000, 17000, 'bottles', true, 1, 10, 1],
            ['Imagi ndogo', 'IMAGI-NDG', 17, 3625, 5000, 'bottles', true, 5, 100, 10],
            ['Imagi liter', 'IMAGI-LT', 15, 3875, 5000, 'bottles', true, 5, 100, 10],
            ['Imagi kubwa', 'IMAGI-KB', 1, 14000, 17000, 'bottles', true, 1, 10, 1],
            ['Heavenly ndogo', 'HEAV-NDG', 1, 5500, 8000, 'bottles', true, 1, 10, 1],
            ['Heavenly kubwa', 'HEAV-KB', 2, 11500, 17000, 'bottles', true, 1, 20, 2],
            ['Four Cousins', 'FOURCOUS', 2, 16000, 25000, 'bottles', true, 1, 20, 2],
            ['Dodoma Wine', 'DODOMA', 1, 11500, 17000, 'bottles', true, 1, 10, 1],
            ['Done Wine kubwa', 'DONE-KB', 1, 11500, 17000, 'bottles', true, 1, 10, 1],
            ['Done Wine ndogo', 'DONE-NDG', 2, 5500, 8000, 'bottles', true, 1, 20, 2],
            ['Olden', 'OLDEN', 1, 11500, 17000, 'bottles', true, 1, 10, 1],
            ['Dompo', 'DOMPO', 1, 11500, 17000, 'bottles', true, 1, 10, 1],
            ['Grappa', 'GRAPPA', 2, 11500, 17000, 'bottles', true, 1, 20, 2],
            ['Pearly Bay', 'PEARLY', 2, 20000, 25000, 'bottles', true, 1, 20, 2],
            ['Hennessy ndogo', 'HEN-NDG', 1, 39000, 50000, 'bottles', true, 1, 10, 1],
            ['Hennessy liter', 'HEN-LT', 1, 61000, 80000, 'bottles', true, 1, 10, 1],
            ['J&B ndogo', 'JB-NDG', 2, 12000, 17000, 'bottles', true, 1, 20, 2],
            ['J&B kubwa', 'JB-KB', 1, 37000, 60000, 'bottles', true, 1, 10, 1],
            ['Red Bull', 'REDBULL', 5, 3750, 5000, 'cans', true, 2, 50, 5],
            ['Apple Punch', 'APPLEP', 12, 1100, 1500, 'bottles', true, 5, 100, 10],
            ['Martini', 'MARTINI', 1, 39000, 60000, 'bottles', true, 1, 10, 1],
            ['J. Master ndogo', 'JM-NDG', 1, 22000, 30000, 'bottles', true, 1, 10, 1],
            ['J. Master liter', 'JM-LT', 1, 31000, 40000, 'bottles', true, 1, 10, 1],
            ['J. Master kubwa', 'JM-KB', 2, 43000, 60000, 'bottles', true, 1, 20, 2],
            ['Brutal', 'BRUTAL', 24, 3833, 5000, 'bottles', true, 5, 100, 12],
            // Beers / soft drinks
            ['Heineken', 'HEINEKEN', 31, 3200, 4000, 'bottles', true, 10, 100, 20],
            ['Windhoek', 'WINDHOEK', 20, 3333, 4000, 'bottles', true, 5, 80, 10],
            ['Bavarian', 'BAVARIAN', 18, 3250, 4000, 'bottles', true, 5, 80, 10],
            ['Flying Fish', 'FLYFISH', 81, 1650, 2500, 'bottles', true, 20, 200, 40],
            ['Savanna', 'SAVANNA', 16, 3886, 5000, 'bottles', true, 5, 80, 10],
            ['Castle Lite', 'CASTLITE', 26, 2250, 3000, 'bottles', true, 10, 100, 15],
            ['Castle Lager', 'CASTLAG', 42, 2250, 3000, 'bottles', true, 10, 150, 20],
            ['Redd\'s', 'REDDS', 24, 2250, 3000, 'cans', true, 5, 100, 12],
            ['Tropical', 'TROPICAL', 9, 3250, 5000, 'bottles', true, 3, 50, 5],
            ['Pepsi', 'PEPSI', 26, 916, 1500, 'bottles', true, 10, 100, 15],
            ['Mirinda', 'MIRINDA', 23, 916, 1500, 'bottles', true, 10, 100, 15],
            ['Embe', 'EMBE', 10, 916, 1500, 'bottles', true, 5, 50, 5],
            ['Azam Energy', 'AZAMEN', 25, 520, 1000, 'cans', true, 10, 100, 15],
            ['Mo Energy', 'MOENERGY', 14, 500, 1000, 'cans', true, 5, 50, 10],
            ['Serengeti Lager kubwa', 'SLAG-KB', 30, 1775, 3000, 'bottles', true, 10, 100, 15],
            ['Serengeti Lager ndogo', 'SLAG-NDG', 81, 1650, 2500, 'bottles', true, 20, 200, 40],
            ['Serengeti Lite', 'SLITE', 89, 1640, 2500, 'bottles', true, 20, 200, 40],
            ['Safari ndogo', 'SAFARI-NDG', 71, 1700, 2500, 'bottles', true, 20, 200, 35],
            ['Safari kubwa', 'SAFARI-KB', 29, 1800, 3000, 'bottles', true, 10, 100, 15],
            ['Castle Lite (bulk)', 'CLITE-B', 106, 1700, 2500, 'bottles', true, 30, 300, 50],
            ['Kilimanjaro ndogo', 'KIL-NDG', 57, 1700, 2500, 'bottles', true, 15, 150, 30],
            ['Kilimanjaro kubwa', 'KIL-KB', 30, 1800, 3000, 'bottles', true, 10, 100, 15],
            ['Serengeti Lemon', 'SLEMON', 60, 1600, 2500, 'bottles', true, 15, 150, 30],
            ['Smirnoff Ice', 'SMIR-ICE', 52, 2600, 4000, 'bottles', true, 15, 150, 25],
            ['Guinness Smooth', 'GNSMOOTH', 56, 1720, 2500, 'bottles', true, 15, 150, 30],
            ['Savanna Apple', 'SAVAPPLE', 60, 2500, 3500, 'bottles', true, 15, 150, 30],
            ['Origin', 'ORIGIN', 23, 2500, 3500, 'bottles', true, 5, 80, 10],
            ['Smirnoff Vodka', 'SMIR-VOD', 3, 10000, 15000, 'bottles', true, 1, 20, 2],
            ['Grand Malt', 'GMALT', 22, 2291, 3000, 'bottles', true, 5, 80, 10],
            ['Maji kubwa', 'MAJI-KB', 32, 833, 1500, 'bottles', false, 10, 100, 15],
            ['Maji ndogo', 'MAJI-NDG', 36, 458, 1000, 'bottles', false, 10, 100, 15],
            ['Soda', 'SODA', 72, 583, 1000, 'bottles', true, 20, 200, 30],
            // Spirits / liqueurs
            ['Plynch Cane', 'PLYNCH', 24, 2250, 3000, 'bottles', true, 5, 100, 12],
            ['K. Vant ndogo', 'KVANT-NDG', 14, 3458, 5000, 'bottles', true, 5, 50, 7],
            ['K. Vant kubwa', 'KVANT-KB', 5, 9583, 15000, 'bottles', true, 2, 30, 3],
            ['Konyagi ndogo', 'KONY-NDG', 15, 4000, 7000, 'bottles', true, 5, 50, 8],
            ['Konyagi liter', 'KONY-LT', 6, 8250, 12000, 'bottles', true, 2, 30, 3],
            ['Konyagi kubwa', 'KONY-KB', 8, 11000, 16000, 'bottles', true, 3, 40, 4],
            ['H-Choir ndogo', 'HCHOIR-NDG', 9, 3600, 5000, 'bottles', true, 3, 40, 5],
            ['H-Choir kubwa', 'HCHOIR-KB', 4, 10000, 15000, 'bottles', true, 1, 20, 2],
            ['Gordon\'s ndogo', 'GORDON-NDG', 1, 12000, 20000, 'bottles', true, 1, 10, 1],
            ['Gordon\'s kubwa', 'GORDON-KB', 1, 40000, 60000, 'bottles', true, 1, 10, 1],
            ['Grant\'s ndogo', 'GRANTS-NDG', 1, 14000, 25000, 'bottles', true, 1, 10, 1],
            ['Grant\'s kubwa', 'GRANTS-KB', 1, 32000, 50000, 'bottles', true, 1, 10, 1],
            ['Amarula liter', 'AMARULA-LT', 2, 19000, 25000, 'bottles', true, 1, 20, 1],
            ['Black & White ndogo', 'BW-NDG', 2, 10000, 15000, 'bottles', true, 1, 20, 1],
            ['Black & White kubwa', 'BW-KB', 2, 22000, 35000, 'bottles', true, 1, 20, 1],
            ['Moonlight ndogo', 'MOON-NDG', 6, 3166, 5000, 'bottles', true, 2, 30, 3],
            ['Moonlight kubwa', 'MOON-KB', 2, 8333, 13000, 'bottles', true, 1, 20, 1],
            ['Zenji ndogo', 'ZENJI-NDG', 2, 6000, 10000, 'bottles', true, 1, 20, 1],
            ['Jack Daniel\'s ndogo', 'JD-NDG', 2, 25000, 35000, 'bottles', true, 1, 20, 1],
            ['Magic Moment ndogo', 'MM-NDG', 2, 11000, 15000, 'bottles', true, 1, 20, 1],
            ['Magic Moment kubwa', 'MM-KB', 1, 20000, 30000, 'bottles', true, 1, 10, 1],
            ['Captain Morgan ndogo', 'CMORG-NDG', 2, 5000, 8000, 'bottles', true, 1, 20, 1],
            ['Captain Morgan kubwa', 'CMORG-KB', 1, 20000, 30000, 'bottles', true, 1, 10, 1],
            ['Absolut ndogo', 'ABS-NDG', 1, 18000, 25000, 'bottles', true, 1, 10, 1],
            ['Absolut kubwa', 'ABS-KB', 1, 60000, 80000, 'bottles', true, 1, 10, 1],
            ['Viceroy ndogo', 'VIC-NDG', 2, 8000, 12000, 'bottles', true, 1, 20, 1],
            ['Jameson ndogo', 'JAM-NDG', 1, 21000, 30000, 'bottles', true, 1, 10, 1],
            ['Jameson liter', 'JAM-LT', 1, 36000, 45000, 'bottles', true, 1, 10, 1],
            ['Jameson kubwa', 'JAM-KB', 1, 59000, 80000, 'bottles', true, 1, 10, 1],
            ['Chrome ndogo', 'CHROME-NDG', 1, 4500, 8000, 'bottles', true, 1, 10, 1],
            ['Chrome kubwa', 'CHROME-KB', 2, 12000, 20000, 'bottles', true, 1, 20, 1],
            ['Hunting Lodge ndogo', 'HL-NDG', 1, 9000, 15000, 'bottles', true, 1, 10, 1],
            ['Hunting Lodge kubwa', 'HL-KB', 1, 26000, 40000, 'bottles', true, 1, 10, 1],
            ['Tusker ndogo', 'TUSKER-NDG', 4, 8000, 10000, 'bottles', true, 1, 20, 2],
            ['Tusker kubwa', 'TUSKER-KB', 2, 13000, 20000, 'bottles', true, 1, 20, 1],
            ['Gilbey\'s ndogo', 'GILBEY-NDG', 2, 9000, 15000, 'bottles', true, 1, 20, 1],
            ['Gilbey\'s kubwa', 'GILBEY-KB', 1, 21000, 30000, 'bottles', true, 1, 10, 1],
            ['Campari ndogo', 'CAMPARI-NDG', 2, 19000, 25000, 'bottles', true, 1, 20, 1],
            ['Safari Cane', 'SAFARICANE', 24, 2200, 3000, 'bottles', true, 5, 100, 12],
            ['Black Label ndogo', 'BL-NDG', 2, 23000, 30000, 'bottles', true, 1, 20, 1],
            ['Black Label kubwa', 'BL-KB', 1, 92000, 110000, 'bottles', true, 1, 10, 1],
            ['Johnnie Walker kubwa', 'JW-KB', 1, 45000, 60000, 'bottles', true, 1, 10, 1],
            ['Ballantine\'s ndogo', 'BALLAN-NDG', 1, 16500, 20000, 'bottles', true, 1, 10, 1],
            ['Desperados', 'DESPERADOS', 12, 3416, 4000, 'bottles', true, 3, 50, 6],
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
     * Rows for inventory items CSV template.
     *
     * @return list<list<string>>
     */
    public static function itemImportRows(): array
    {
        $out = [];
        foreach (self::all() as $item) {
            $out[] = [
                $item['name'],
                $item['code'],
                'On hand: ' . $item['quantity'],
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
