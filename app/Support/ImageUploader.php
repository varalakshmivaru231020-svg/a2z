<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Stores uploaded photos on the public "uploads" disk (public/uploads, so no
 * storage:link is needed on shared hosting). Every image is decoded and
 * re-encoded with GD, which strips metadata, caps the dimensions and keeps
 * page weight low.
 */
class ImageUploader
{
    private const MAX_SOURCE_PIXELS = 36_000_000; // refuse images that would exhaust memory

    /**
     * @return array{path: string, thumb: ?string} paths relative to the uploads disk
     */
    public function store(UploadedFile $file, string $directory, int $maxWidth = 1600, ?int $thumbWidth = null): array
    {
        $source = $this->decode($file);
        $base = trim($directory, '/') . '/' . Str::random(24);
        $ext = function_exists('imagewebp') ? 'webp' : 'jpg';

        $path = $this->encode($this->fit($source, $maxWidth), "$base.$ext", $ext);
        $thumb = $thumbWidth ? $this->encode($this->fit($source, $thumbWidth), "$base-thumb.$ext", $ext) : null;

        return ['path' => $path, 'thumb' => $thumb];
    }

    public function delete(?string ...$paths): void
    {
        $paths = array_filter($paths);

        if ($paths) {
            Storage::disk('uploads')->delete($paths);
        }
    }

    private function decode(UploadedFile $file): \GdImage
    {
        $real = $file->getRealPath();
        $info = $real ? @getimagesize($real) : false;

        if (! $info || ! in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            throw new RuntimeException('Unsupported image type.');
        }

        if ($info[0] * $info[1] > self::MAX_SOURCE_PIXELS) {
            throw new RuntimeException('Image dimensions are too large.');
        }

        $image = @imagecreatefromstring(file_get_contents($real));

        if (! $image) {
            throw new RuntimeException('Could not read the image.');
        }

        // Honour the camera's orientation flag so phone photos are not sideways.
        if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $orientation = @exif_read_data($real)['Orientation'] ?? 1;
            $angle = [3 => 180, 6 => -90, 8 => 90][$orientation] ?? 0;

            if ($angle && ($rotated = imagerotate($image, $angle, 0))) {
                $image = $rotated;
            }
        }

        return $image;
    }

    private function fit(\GdImage $image, int $maxWidth): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if ($width <= $maxWidth) {
            return $this->copy($image, $width, $height);
        }

        return $this->copy($image, $maxWidth, (int) round($height * $maxWidth / $width));
    }

    private function copy(\GdImage $image, int $width, int $height): \GdImage
    {
        $canvas = imagecreatetruecolor($width, $height);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 255, 255, 255, 127));
        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));

        return $canvas;
    }

    private function encode(\GdImage $image, string $path, string $ext): string
    {
        ob_start();
        if ($ext === 'webp') {
            imagewebp($image, null, 82);
        } else {
            // JPEG has no alpha: flatten onto white.
            $flat = imagecreatetruecolor(imagesx($image), imagesy($image));
            imagefill($flat, 0, 0, imagecolorallocate($flat, 255, 255, 255));
            imagecopy($flat, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
            imagejpeg($flat, null, 84);
        }
        $bytes = ob_get_clean();

        Storage::disk('uploads')->put($path, $bytes);

        return $path;
    }
}
