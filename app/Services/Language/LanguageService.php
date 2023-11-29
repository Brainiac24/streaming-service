<?php

namespace App\Services\Language;

use App\Exceptions\NotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;

class LanguageService
{
    public function getLang($lang)
    {
        $langPath = storage_path('app/public/frontend/lang/' .$lang. '.json');

        $json = File::get($langPath);

        if (empty($json)) {
            throw new NotFoundException();
        }

        $messages = json_decode($json);
        $messages->error = Lang::get('error');

        return [
            "language" => $lang,
            "messages" => $messages
        ];
    }
}
