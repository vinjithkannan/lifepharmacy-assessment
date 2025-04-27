<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    public function uploadFile(array $files)
    {
        $urls = [];
        foreach ($files as $file) {
            $fileName = Str::random(20) . '.' . $file->getClientOriginalExtension();
            $path = Storage::disk('public')->putFileAs('uploads', $file, $fileName);
            $urls[] = [ 'image' => Storage::url($path)];
        }

        return $urls;
    }
}
