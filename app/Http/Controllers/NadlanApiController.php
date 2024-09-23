<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

        // Generate a unique cache key
        $cacheKey = 'apiGetDataByQuery_'.md5($url);

        // Set cache TTL to 14 days (in minutes)
        $cacheTTL = 14 * 24 * 60; // 14 days in minutes

        try {
            // Check if the response is cached
            if (Cache::has($cacheKey)) {
                Log::info("Returning cached data for: $url");
                return Cache::get($cacheKey);
            }

            // Make the HTTP request
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->withUserAgent("Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36")
                            ->timeout(30)
                            ->withoutVerifying()  // Disables SSL certificate verification
                            ->get($url);

            if ($response->successful()) {
                $responseBody = $response->body();

                // Cache the response
                Cache::put($cacheKey, $responseBody, $cacheTTL);

                Log::info("GetApiDataByQuery Result: ".$responseBody);
                return $responseBody;
            } else {
                Log::error("Failed to retrieve data from: $url");
                return response()->json(['error' => 'Failed to retrieve data'], 400);
            }
        } catch (\Exception $e) {
            Log::error("Error in GetApiDataByQuery: ".$e->getMessage());
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
        $fresh = $request->boolean('fresh');

        // Clean and prepare data
        $shuna = addslashes(str_replace('"', '', $shuna));
        $decodedResult = json_decode($result, true);
        $decodedResult['PageNo'] = $count;

        // Encode the data as JSON string, matching the curl's --data-raw
        $resulttemp = json_encode($decodedResult, JSON_UNESCAPED_UNICODE);

        // Generate a unique cache key
        $cacheKey = 'apiGetAssetsAndDeals_'.md5($count.$resulttemp.$city.$shuna);

        // Set cache TTL to 14 days (in minutes)
        $cacheTTL = 14 * 24 * 60; // 14 days in minutes

        // Check if the response is cached
        if (!$fresh && Cache::has($cacheKey)) {
            Log::info("Returning cached data for city: $city, shuna: $shuna");
            return response()->json(Cache::get($cacheKey));
        }

        // Prepare headers to match the curl command
        $headers = [
            'Accept'             => 'application/json, text/plain, */*',
            'Accept-Language'    => 'en-US,en;q=0.9,he-IL;q=0.8,he;q=0.7',
            'Connection'         => 'keep-alive',
            'Content-Type'       => 'application/json;charset=UTF-8',
            'Cookie'             => 'p_hosting=!WGMJP2SoyAF+U8JVMO6s9sATeWp7o5c8NqnJuKPCqj62OSk37+qkk5lHLKqIxOiKx6YPuJ5o2wsJbB4=; _ga=GA1.3.1834999426.1717406145; TS01c75138=0124934a81ef3cfe365604d485687b60ba04021ce1347f97a1cdf3131605881dd88286c1997a369cc37e2fb1284e063ce12a1e88cb; _gid=GA1.3.2087894438.1727091133; _ga_RWF2PL4D3L=GS1.3.1727091133.6.0.1727091133.0.0.0; TS624e36da027=08b707dd67ab2000403701648db490c3f2c8fc9641759ac5ca61d5d565e035eb877c3938b6cf896808634460311130007ecd6c7d3fc93413a20ace034eb1adc58b6b646f6bbb4d5dcb5b9ec33246c52cb72d39d5a516f21b229d4cf3aa7f337e',
            'DNT'                => '1',
            'Origin'             => 'https://www.nadlan.gov.il',
            'Referer'            => 'https://www.nadlan.gov.il/?search='.urlencode($city),
            'Sec-Fetch-Dest'     => 'empty',
            'Sec-Fetch-Mode'     => 'cors',
            'Sec-Fetch-Site'     => 'same-origin',
            'User-Agent'         => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36',
            'sec-ch-ua'          => '"Google Chrome";v="129", "Not=A?Brand";v="8", "Chromium";v="129"',
            'sec-ch-ua-mobile'   => '?0',
            'sec-ch-ua-platform' => '"Linux"',
        ];

        // Perform the HTTP request using Laravel's HTTP client
        $response = Http::withHeaders($headers)
                        ->withOptions([
                            'verify' => false,
                        ])
                        ->withBody($resulttemp, 'application/json;charset=UTF-8')
                        ->post('https://www.nadlan.gov.il/Nadlan.REST/Main/GetAssestAndDeals');

        // Check if the request was successful
        if ($response->successful()) {
            $data = $response->json();

            // Cache the response
            Cache::put($cacheKey, $data, $cacheTTL);

            return response()->json($data);
        } else {
            // Log and handle errors
            Log::error("Failed to retrieve data from API for city: $city, shuna: $shuna");
            return response()->json([
                'error'          => "Failed to retrieve data from API for city: $city, shuna: $shuna",
                'response'       => $response->body(),
                'resulttemp'     => $resulttemp,
                '$decodedResult' => $decodedResult,
            ], 400);
        }
    }


}
