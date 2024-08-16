<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NadlanApiController extends Controller
{

    /**
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function apiGetDataByQuery(Request $request): JsonResponse | string
    {
        $url = $request->input('url');  // Assuming URL is passed as a query parameter
        $url = stripcslashes($url);
        Log::info("GetApiDataByQuery: $url");

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->withUserAgent("Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36")
                            ->timeout(30)
                            ->withoutVerifying()  // Disables SSL certificate verification
                            ->get($url);

            if ($response->successful()) {
                Log::info("GetApiDataByQuery Result: " . $response->body());
                return $response->body();
            } else {
                Log::error("Failed to retrieve data from: $url");
                return response()->json(['error' => 'Failed to retrieve data'], 400);
            }
        } catch (\Exception $e) {
            Log::error("Error in GetApiDataByQuery: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTrace(), 'request_url' => $url], 500);
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function apiGetAssetsAndDeals(Request $request): JsonResponse
    {
        // Extract data from the request
        $count = $request->input('count');
        $result = $request->input('result');
        $city = $request->input('city');
        $shuna = $request->input('shuna');

        // Clean and prepare data
        $shuna = addslashes(str_replace('"', '', $shuna));
        $resulttemp = str_replace('"PageNo":0', '"PageNo":' . $count, $result);

        // Perform the HTTP request using Laravel's HTTP client
        $response = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'cache-control' => 'no-cache',
        ])->withOptions([
            'verify' => false,
        ])->post('https://www.nadlan.gov.il/Nadlan.REST/Main/GetAssestAndDeals', $resulttemp);

        // Check if the request was successful
        if ($response->successful()) {
            $data = $response->json();
            return response()->json($data);
        } else {
            // Log and handle errors
            Log::error("Failed to retrieve data from API for city: {$city}, shuna: {$shuna}");
            return response()->json(['error' => 'Failed to retrieve data from the API', 'response' => $response, 'resulttemp' => $resulttemp, ], 400);
        }
    }

}
