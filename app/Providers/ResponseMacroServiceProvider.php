<?php

namespace App\Providers;

use App\Constants\StatusCodes;
use App\Http\Resources\BaseJsonResource;
use App\Services\Helper\HelperService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;

class ResponseMacroServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->responseApiSuccess();

        $this->responseApiError();
    }

    private function responseApiSuccess()
    {
        Response::macro('apiSuccess', function ($response = ['code' => StatusCodes::SUCCESS], int $status = 200, array $headers = [], $options = null) {

            if ($response instanceof BaseJsonResource) {
                try {
                    $response = $response->toArray();
                } catch (\Throwable $th) {
                    throw $th;
                }
            }

            $finalResponse = [];
            !isset($response['code']) ? $finalResponse['code'] = StatusCodes::SUCCESS : $finalResponse['code'] = $response['code'];
            !isset($response['message']) ? ($finalResponse['code'] == StatusCodes::SUCCESS ? $finalResponse['message'] = 'Success' : '') : $finalResponse['message'] = $response['message'];
            !isset($response['errors']) ?: $finalResponse['errors'] = $response['errors'];
            !isset($response['data']) ? $finalResponse['data'] = [] : $finalResponse['data'] = $response['data'];
            !isset($response['meta']) ?: $finalResponse['meta'] = $response['meta'];
            !isset($response['pagination']) ?: $finalResponse['pagination'] = $response['pagination'];

            if (
                isset($finalResponse['data']) &&
                $finalResponse['data'] &&
                (($finalResponse['data'] instanceof \Illuminate\Database\Eloquent\Collection) ||
                    ($finalResponse['data'] instanceof \Illuminate\Support\Collection) ||
                    ($finalResponse['data'] instanceof \Illuminate\Database\Eloquent\Model) ||
                    is_subclass_of($finalResponse['data'], '\App\Models\BaseModel')
                )
            ) {
                $finalResponse['data'] = $finalResponse['data']->toArray();
            }

            if ($finalResponse != null && !empty($finalResponse) && is_array($finalResponse)) {
                array_walk_recursive($finalResponse, HelperService::class . '::arrayRecursiveChangeDateFormat');
            }

            return response()->json($finalResponse, $status, $headers, JSON_UNESCAPED_UNICODE);
        });
    }

    private function responseApiError()
    {
        Response::macro('apiError', function ($response = ['code' => StatusCodes::UNKNOWN_ERROR], int $status = 400, array $headers = [], $options = null) {

            if ($response instanceof BaseJsonResource) {
                $response = $response->toArray();
            }

            $finalResponse = [];
            !isset($response['code']) ?  $finalResponse['code'] = StatusCodes::UNKNOWN_ERROR : $finalResponse['code'] = $response['code'];
            !isset($response['message']) ?: $finalResponse['message'] = $response['message'];
            !isset($response['errors']) ?: $finalResponse['errors'] = $response['errors'];
            !isset($response['data']) ?: $finalResponse['data'] = $response['data'];
            !isset($response['meta']) ?: $finalResponse['meta'] = $response['meta'];

            return response()->json($finalResponse, $status, $headers, JSON_UNESCAPED_UNICODE);
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
