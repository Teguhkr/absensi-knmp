<?php

namespace App\Helpers;

class PdfImageHelper
{
    /**
     * Get compressed base64 image data for PDF rendering.
     * Caches compressed images in storage/app/pdf-cache/
     */
    public static function getCompressedBase64(string $imgPath, int $maxWidth = 500, int $quality = 75): ?string
    {
        if (!file_exists($imgPath) || is_dir($imgPath)) {
            return null;
        }

        try {
            // Small files (< 100KB) don't need compression
            if (filesize($imgPath) < 100 * 1024) {
                $imgData = file_get_contents($imgPath);
                $ext = pathinfo($imgPath, PATHINFO_EXTENSION) ?: 'jpeg';
                return 'data:image/' . $ext . ';base64,' . base64_encode($imgData);
            }

            // Cache key based on file path, mtime, width, quality
            $cacheDir = storage_path('app/pdf-cache');
            if (!file_exists($cacheDir)) {
                @mkdir($cacheDir, 0755, true);
            }

            $cacheKey = md5($imgPath . '_' . filemtime($imgPath) . "_{$maxWidth}_{$quality}");
            $cachePath = $cacheDir . '/' . $cacheKey . '.jpg';

            if (file_exists($cachePath)) {
                $compressedData = file_get_contents($cachePath);
                return 'data:image/jpeg;base64,' . base64_encode($compressedData);
            }

            $info = @getimagesize($imgPath);
            if (!$info) {
                $imgData = file_get_contents($imgPath);
                $ext = pathinfo($imgPath, PATHINFO_EXTENSION) ?: 'jpeg';
                return 'data:image/' . $ext . ';base64,' . base64_encode($imgData);
            }

            $width = $info[0];
            $height = $info[1];
            $mime = $info['mime'];

            switch ($mime) {
                case 'image/jpeg':
                case 'image/jpg':
                    $srcImg = @imagecreatefromjpeg($imgPath);
                    break;
                case 'image/png':
                    $srcImg = @imagecreatefrompng($imgPath);
                    break;
                case 'image/webp':
                    $srcImg = @imagecreatefromwebp($imgPath);
                    break;
                default:
                    $srcImg = null;
                    break;
            }

            if (!$srcImg) {
                $imgData = file_get_contents($imgPath);
                $ext = pathinfo($imgPath, PATHINFO_EXTENSION) ?: 'jpeg';
                return 'data:image/' . $ext . ';base64,' . base64_encode($imgData);
            }

            if ($width > $maxWidth) {
                $newWidth = $maxWidth;
                $newHeight = (int) floor($height * ($maxWidth / $width));
            } else {
                $newWidth = $width;
                $newHeight = $height;
            }

            $dstImg = imagecreatetruecolor($newWidth, $newHeight);

            if ($mime === 'image/png' || $mime === 'image/webp') {
                imagealphablending($dstImg, false);
                imagesavealpha($dstImg, true);
                $transparent = imagecolorallocatealpha($dstImg, 255, 255, 255, 127);
                imagefilledrectangle($dstImg, 0, 0, $newWidth, $newHeight, $transparent);
            }

            imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            ob_start();
            imagejpeg($dstImg, null, $quality);
            $compressedData = ob_get_clean();

            imagedestroy($srcImg);
            imagedestroy($dstImg);

            if ($compressedData) {
                @file_put_contents($cachePath, $compressedData);
                return 'data:image/jpeg;base64,' . base64_encode($compressedData);
            }
        } catch (\Throwable $e) {
            // Fallback to direct reading if compression fails
        }

        $imgData = @file_get_contents($imgPath);
        if ($imgData) {
            $ext = pathinfo($imgPath, PATHINFO_EXTENSION) ?: 'jpeg';
            return 'data:image/' . $ext . ';base64,' . base64_encode($imgData);
        }

        return null;
    }
}
