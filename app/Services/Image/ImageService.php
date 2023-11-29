<?php

namespace App\Services\Image;

use App\Exceptions\Image\BadImageFormatException;
use Illuminate\Support\Facades\File;
use Storage;
use Str;
use Intervention\Image\ImageManagerStatic as Image;

class ImageService
{

    public $allowedExtensionList = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'bmp'];

    public function storeFromBase64($image64, $userId = 0, $config = [])
    {

        if (strpos($image64, 'base64') === false) {
            throw new BadImageFormatException();
        }

        $extension = explode('/', explode(':', substr($image64, 0, strpos($image64, ';')))[1])[1];   // .jpg .png .pdf

        if (!in_array($extension, $this->allowedExtensionList)) {
            throw new BadImageFormatException();
        }

        $replace = substr($image64, 0, strpos($image64, ',') + 1);
        $image = str_replace($replace, '', $image64);
        $image = str_replace(' ', '+', $image);

        $contents = base64_decode($image);

        $filename = ((string) Str::uuid()) . "." . strtolower($extension);

        if (!empty($config) && isset($config['name_prefix'])) {
            $filename = $config['name_prefix'] . $filename;
        }

        $filePath = "upload/{$userId}/";
        $file = $filePath . $filename;

        Storage::disk('public')->put($file, $contents);

        return '/storage/' . $file;
    }

    public function storeFromUrl($url, $userId = 0, $config = [])
    {
        $contents = file_get_contents($url);
        $urlHeaders = get_headers($url, true);
        $contentType = '';

        if (isset($urlHeaders['Content-Type'])) {
            $contentType = 'Content-Type';
        } else if (isset($urlHeaders['content-type'])) {
            $contentType = 'content-type';
        }

        if ($contentType == '') {
            throw new BadImageFormatException();
        }

        $extension = explode('/', $urlHeaders[$contentType])[1];

        if (!in_array($extension, $this->allowedExtensionList)) {
            throw new BadImageFormatException();
        }

        $filename = ((string) Str::uuid()) . "." . strtolower($extension);

        if (!empty($config) && isset($config['name_prefix'])) {
            $filename = $config['name_prefix'] . $filename;
        }

        $filePath = "upload/{$userId}/";
        $file = $filePath . $filename;

        Storage::disk('public')->put($file, $contents);

        return '/storage/' . $file;
    }

    public function storeFromFile($file, $userId = 0, $config = [])
    {

        ini_set('memory_limit', '256M');
        ini_set('max_execution_time', '500s');
        $extension = File::guessExtension($file);

        $filename = ((string) Str::uuid()) . $extension;
        if (!empty($config) && isset($config['name'])) {
            $filename = $config['name'] . "." . $extension;
        }
        if (!empty($config) && isset($config['name_prefix'])) {
            $filename = $config['name_prefix'] . $filename;
        }
        $basePath = "app/public";
        $filePath = "/upload/{$userId}/";
        $fileNewPath = storage_path($basePath . $filePath . $filename);

        if (!file_exists(storage_path($basePath . $filePath))) {
            mkdir(storage_path($basePath . $filePath), 0777, true);
        }

        $img = Image::make($file->path());

        if (!empty($config) && isset($config['w']) && isset($config['h'])) {
            $img = $img->fit($config['w'], $config['h']);
        }

        $img->save($fileNewPath);

        return '/storage' . $filePath . $filename;
    }

    public function deleteByFilePath($filePath)
    {
        $file = str_replace('/storage/', '', $filePath);
        $fileTmb = dirname($file) . '/tmb_' . basename($filePath);

        File::delete(storage_path('app/public/' . $file));
        File::delete(storage_path('app/public/' . $fileTmb));
    }
}
