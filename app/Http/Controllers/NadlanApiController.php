<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NadlanApiController extends Controller
{
    public function getApiNadlanAddressFromNadlan(Request $request)
    {
        $address = trim($request->input('adresse'));
        $address = urlencode($address);

        $url = "https://www.nehassim.com/pages/getshunabynadlan?adresse=" . $address;
        $userAgent = "Mozilla/5.0 (Linux; Android 9; BLA-L29 Build/HUAWEIBLA-L29S; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/75.0.3770.101 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/228.0.0.41.124;]";

        $response = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->withUserAgent($userAgent)
                        ->timeout(30)
                        ->get($url);

        return $response->body();
    }

    public function apiGetAssetsAndDeals(Request $request)
    {
        $count = $request->input('count');
        $result = $request->input('result');
        $city = $request->input('city');
        $shuna = addslashes(str_replace('"', '', $request->input('shuna')));
        $searchId = $request->input('searchId');

        $resulttemp = str_replace('"PageNo":0', '"PageNo":' . $count, $result);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'cache-control' => 'no-cache'
        ])->post('https://www.nadlan.gov.il/Nadlan.REST/Main/GetAssestAndDeals?=', $resulttemp);

        Log::info($response->body());

        $res = json_decode($response->body());

        if (count($res->AllResults) == 0) {
            Log::error('No results found for city: ' . $city . ' and shuna: ' . $shuna);
            return response()->json(['error' => 'No results found'], 404);
        }

        // Further processing...
        return response()->json($res);
    }
}
