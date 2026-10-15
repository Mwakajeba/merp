<?php

namespace App\Services\Milipuko;

class PichaMandhariNyeupe
{
    public function png(string $bytes): ?string
    {
        $src = @imagecreatefromstring($bytes);
        if ($src === false) {
            return null;
        }

        $sw = imagesx($src);
        $sh = imagesy($src);
        if ($sw < 2 || $sh < 2) {
            imagedestroy($src);

            return null;
        }

        $max = 420;
        $scale = min(1.0, $max / max($sw, $sh));
        $w = max(2, (int) round($sw * $scale));
        $h = max(2, (int) round($sh * $scale));
        $ndogo = imagecreatetruecolor($w, $h);
        imagecopyresampled($ndogo, $src, 0, 0, 0, 0, $w, $h, $sw, $sh);

        $pixels = [];
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $pixels[] = imagecolorat($ndogo, $x, $y) & 0xFFFFFF;
            }
        }
        imagedestroy($ndogo);

        $bg = $this->rangiYaUkingo($pixels, $w, $h);
        $mask = $this->maskYaMandhari($pixels, $w, $h, $bg);
        $imeondolewa = substr_count($mask, "\1");

        if ($imeondolewa === 0 || $imeondolewa >= (int) ($w * $h * 0.82)) {
            imagedestroy($src);

            return null;
        }

        $out = imagecreatetruecolor($sw, $sh);
        imagealphablending($out, false);
        imagesavealpha($out, true);
        $wazi = imagecolorallocatealpha($out, 0, 0, 0, 127);
        imagefilledrectangle($out, 0, 0, $sw - 1, $sh - 1, $wazi);
        imagealphablending($out, true);
        imagecopy($out, $src, 0, 0, 0, 0, $sw, $sh);
        imagedestroy($src);
        imagealphablending($out, false);
        imagesavealpha($out, true);

        for ($y = 0; $y < $sh; $y++) {
            $sy = min($h - 1, intdiv($y * $h, $sh));
            $mstari = $sy * $w;
            for ($x = 0; $x < $sw; $x++) {
                $sx = min($w - 1, intdiv($x * $w, $sw));
                if ($mask[$mstari + $sx] === "\1") {
                    imagesetpixel($out, $x, $y, $wazi);
                }
            }
        }

        ob_start();
        imagepng($out, null, 6);
        imagedestroy($out);

        return ob_get_clean() ?: null;
    }

    /**
     * @param  array<int, int>  $pixels
     * @return array{0: int, 1: int, 2: int}
     */
    private function rangiYaUkingo(array $pixels, int $w, int $h): array
    {
        $hesabu = [];
        $sampuli = [];

        $weka = function (int $i) use (&$hesabu, &$sampuli, $pixels): void {
            $c = $pixels[$i];
            $ndoo = (($c >> 20) & 0xF) << 8 | (($c >> 12) & 0xF) << 4 | (($c >> 4) & 0xF);
            $hesabu[$ndoo] = ($hesabu[$ndoo] ?? 0) + 1;
            $sampuli[$ndoo][] = $c;
        };

        for ($x = 0; $x < $w; $x += 2) {
            $weka($x);
            $weka(($h - 1) * $w + $x);
        }
        for ($y = 2; $y < $h - 1; $y += 2) {
            $weka($y * $w);
            $weka($y * $w + $w - 1);
        }

        arsort($hesabu);
        $ndoo = (int) array_key_first($hesabu);
        $rs = $gs = $bs = [];
        foreach ($sampuli[$ndoo] as $c) {
            $rs[] = ($c >> 16) & 255;
            $gs[] = ($c >> 8) & 255;
            $bs[] = $c & 255;
        }
        sort($rs);
        sort($gs);
        sort($bs);
        $kati = intdiv(count($rs), 2);

        return [$rs[$kati], $gs[$kati], $bs[$kati]];
    }

    /**
     * @param  array<int, int>  $pixels
     * @param  array{0: int, 1: int, 2: int}  $bg
     */
    private function maskYaMandhari(array $pixels, int $w, int $h, array $bg): string
    {
        $n = $w * $h;
        $seen = str_repeat("\0", $n);
        $mask = str_repeat("\0", $n);
        $kikomo = 68 * 68;
        $queue = [];

        for ($x = 0; $x < $w; $x++) {
            $queue[] = $x;
            $queue[] = ($h - 1) * $w + $x;
        }
        for ($y = 1; $y < $h - 1; $y++) {
            $queue[] = $y * $w;
            $queue[] = $y * $w + $w - 1;
        }

        $head = 0;
        [$br, $bgG, $bb] = $bg;

        while ($head < count($queue)) {
            $i = $queue[$head++];
            if ($seen[$i] === "\1") {
                continue;
            }
            $seen[$i] = "\1";

            $c = $pixels[$i];
            $dr = (($c >> 16) & 255) - $br;
            $dg = (($c >> 8) & 255) - $bgG;
            $db = ($c & 255) - $bb;
            if (($dr * $dr) + ($dg * $dg) + ($db * $db) > $kikomo) {
                continue;
            }

            $mask[$i] = "\1";
            $x = $i % $w;
            $y = intdiv($i, $w);
            if ($x > 0) {
                $queue[] = $i - 1;
            }
            if ($x < $w - 1) {
                $queue[] = $i + 1;
            }
            if ($y > 0) {
                $queue[] = $i - $w;
            }
            if ($y < $h - 1) {
                $queue[] = $i + $w;
            }
        }

        return $mask;
    }
}
