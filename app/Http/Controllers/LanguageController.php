<?php

namespace App\Http\Controllers;

use App\Http\Requests\Language\LanguageRequest;
use App\Http\Resources\BaseJsonResource;
use App\Services\Language\LanguageService;
use Illuminate\Support\Facades\Response;

class LanguageController extends Controller
{

    public function __construct(public LanguageService $languageService)
    {
    }
    public function getLang(LanguageRequest $request)
    {
        return Response::apiSuccess(new BaseJsonResource(
            meta: $this->languageService->getLang($request->lang)
        ));
    }
}
