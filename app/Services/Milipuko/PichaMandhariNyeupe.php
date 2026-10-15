<?php

namespace App\Services\Milipuko;

class PichaMandhariNyeupe
{
    public function jpeg(string $bytes): ?string
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

        $max = 640;
        $scale = min(1.0, $max / max($sw, $sh));
        $w = max(2, (int) round($sw * $scale));
        $h = max(2, (int) round($sh * $scale));

        $img = imagecreatetruecolor($w, $h);
        $white = imagecolorallocate($img, 255, 255, 255);
        imagefilledrectangle($img, 0, 0, $w - 1, $h - 1, $white);
        imagecopyresampled($img, $src, 0, 0, 0, 0, $w, $h, $sw, $sh);
        imagedestroy($src);

        $pixels = [];
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $pixels[] = imagecolorat($img, $x, $y) & 0xFFFFFF;
            }
        }

        $bg = $this->rangiYaUkingo($pixels, $w, $h);
        $mask = $this->maskYaMandhari($pixels, $w, $h, $bg);
        $n = $w * $h;
        $imeondolewa = substr_count($mask, "\1");

        if ($imeondolewa > 0 && $imeondolewa < (int) ($n * 0.82)) {
            for ($i = 0; $i < $n; $i++) {
                if ($mask[$i] === "\1") {
                    imagesetpixel($img, $i % $w, intdiv($i, $w), $white);
                }
            }
        }

        ob_start();
        imagejpeg($img, null, 92);
        imagedestroy($img);

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
        $rangi = $sampuli[$ndoo];
        $rs = $gs = $bs = [];
        foreach ($rangi as $c) {
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
        $kikomo = 80 * 80;
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

        $upeo = 96 * 96;
        $nyongeza = [];
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $i = $y * $w + $x;
                if ($mask[$i] === "\1") {
                    continue;
                }
                $karibu = ($x > 0 && $mask[$i - 1] === "\1")
                    || ($x < $w - 1 && $mask[$i + 1] === "\1")
                    || ($y > 0 && $mask[$i - $w] === "\1")
                    || ($y < $h - 1 && $mask[$i + $w] === "\1");
                if (! $karibu) {
                    continue;
                }
                $c = $pixels[$i];
                $dr = (($c >> 16) & 255) - $br;
                $dg = (($c >> 8) & 255) - $bgG;
                $db = ($c & 255) - $bb;
                if (($dr * $dr) + ($dg * $dg) + ($db * $db) <= $upeo) {
                    $nyongeza[] = $i;
                }
            }
        }
        foreach ($nyongeza as $i) {
            $mask[$i] = "\1";
        }

        return $mask;
    }
}
