<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class PhotoThumb
{
    /**
     * Ambil foto dari disk private dan ubah menjadi thumbnail JPEG (data URI) untuk disematkan ke PDF.
     */
    public static function dataUri(string $path, int $maxSide = 200): ?string
    {
        try {
            $disk = Storage::disk('local');

            if (!$disk->exists($path)) {
                return null;
            }

            return self::fromBinary($disk->get($path), $maxSide);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Perkecil data gambar mentah menjadi JPEG data URI. Mengembalikan null jika GD tidak ada atau gambar rusak.
     */
    public static function fromBinary(string $binary, int $maxSide = 200): ?string
    {
        if (!function_exists('imagecreatefromstring')) {
            return null;
        }

        $source = @imagecreatefromstring($binary);
        if ($source === false) {
            return null;
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, $maxSide / max($width, $height));
        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));

        $thumb = imagecreatetruecolor($newWidth, $newHeight);
        imagefill($thumb, 0, 0, imagecolorallocate($thumb, 255, 255, 255)); // latar putih untuk PNG transparan
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        ob_start();
        imagejpeg($thumb, null, 70);
        $jpeg = ob_get_clean();

        return 'data:image/jpeg;base64,' . base64_encode($jpeg);
    }
}
