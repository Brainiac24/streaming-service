<?php

namespace App\Http\Controllers;

use App\Http\Requests\Nimble\NimbleRequest;
use App\Http\Requests\Nimble\NimbleStreamRequest;
use App\Services\Nimble\NimbleService;

class NimbleController extends Controller
{
    public function __construct(public NimbleService $nimbleService)
    {
    }

    public function publisherAuth(NimbleRequest $request)
    {
        $this->nimbleService->publisherAuth($request->validated());

        return response('ok', '201');
    }
    public function publisherUpdate(NimbleRequest $request)
    {
        $this->nimbleService->publisherUpdate($request->validated());

        return response('ok', '201');
    }
    public function getPublisherRouteResolution(NimbleStreamRequest $request)
    {
        $resolution = $this->nimbleService->getPublisherRouteResolution($request->validated());

        return response($resolution, '200');
    }
    public function getStreamForS3(NimbleStreamRequest $request)
    {
        $data = $this->nimbleService->getStreamForS3($request->validated());

        return response($data, $data['result']);
    }
    public function moveStreamToS3(NimbleStreamRequest $request)
    {
        $data = $this->nimbleService->moveStreamToS3($request->validated());

        return response($data, $data['result']);
    }
}
