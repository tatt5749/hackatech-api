<?php

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Exceptions\ApiErrorException;
use Illuminate\Support\Facades\Log;
use App\Services\SystemSettingsService;


if (!function_exists('cacheService')) {
    /**
     * Get the instance of Util class core
     *
     * @return \App\Core\Adapters\Util|\Illuminate\Contracts\Foundation\Application|mixed
     * @throws Throwable
     */
    function cacheService()
    {
        //$bootstrap = "\App\Core\Bootstraps\Bootstrap$demo";

        $cache = "\App\Services\CacheService";


        return app($cache);
    }
}


if (!function_exists('httpResponse')) {
    /**
     * Get the instance of Util class core
     *
     * @return \App\Core\Adapters\Util|\Illuminate\Contracts\Foundation\Application|mixed
     * @throws Throwable
     */
    function httpResponse()
    {
        //$bootstrap = "\App\Core\Bootstraps\Bootstrap$demo";

        $service = "\App\Services\HttpResponseService";


        return app($service);
    }
}



if (!function_exists("formatJsonObjectToArray")) {
    /**
     * @param array | Illuminate\Database\Eloquent\Model $data
     * @param int $status HTTP status code
     * @param array $extraHeaders
     * @return validated input
     */
    function formatJsonObjectToArray($message)
    {
       return (isJSON($message)) ? json_decode($message,true) : $message;
    }
}


if (!function_exists("isJSON")) {
    function isJSON($string){
       return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }
}


if (!function_exists("dateFormat")) {
    /**
     * @param array | Illuminate\Database\Eloquent\Model $data
     * @param int $status HTTP status code
     * @param array $extraHeaders
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    function dateFormat($date=null, $format = null)
    {
        if(is_null($format)){
           $format = settings('APP_DATE_FORMAT') ?? 'Y-m-d H:i:s'; 
        }
        
        return (is_null($date)) ? null : Carbon::parse($date)->format($format);
    }
}

if (!function_exists("dateToday")) {
    /**
     * @param array | Illuminate\Database\Eloquent\Model $data
     * @param int $status HTTP status code
     * @param array $extraHeaders
     * @return validated input
     */
    function dateToday($format=null){
        return dateFormat(Carbon::now(),$format);
    }
}

if (!function_exists("currentYear")) {
    /**
     * @param array | Illuminate\Database\Eloquent\Model $data
     * @param int $status HTTP status code
     * @param array $extraHeaders
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    function currentYear()
    {
        return Carbon::now()->format('Y');
    }
}


if (!function_exists("currentMonth")) {
    /**
     * @param array | Illuminate\Database\Eloquent\Model $data
     * @param int $status HTTP status code
     * @param array $extraHeaders
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    function currentMonth()
    {
        return Carbon::now()->format('m');
    }
}

if (!function_exists("getIp")) {
    function getIp(Request $request){
        $ip = null;
        $ipArr = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR','X_FORWARDED_FOR'];
        foreach ($ipArr as $key){
            if (array_key_exists($key, $_SERVER) === true){
                foreach (explode(',', $_SERVER[$key]) as $ip){
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                        return $ip;
                    }
                }
            }
        }
        return $ip;
    }
}

if (!function_exists("isJson")) {
    function isJson($string){
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}

if (!function_exists("errorLog")) {
    /**
     * @param array | Illuminate\Database\Eloquent\Model $data
     * @param int $status HTTP status code
     * @param array $extraHeaders
     * @return validated input
     */
    function errorLog(Throwable $exception,$category=''){
        $errorMsg = [
            "message" => $exception->getMessage(),
            "line" => $exception->getLine(),
            "file" => $exception->getFile(),
        ];
        
        Log::channel('mysql')->error($category,$errorMsg);
    }
}


if (!function_exists("throwApiErrorException")) {
    /**
     * @param array | Illuminate\Database\Eloquent\Model $data
     * @param int $status HTTP status code
     * @param array $extraHeaders
     * @return validated input
     */
    function throwApiErrorException($message)
    {
        throw new ApiErrorException(formatArrayToString($message) );
    }
}


if (!function_exists("formatArrayToString")) {
    /**
     * @param array | Illuminate\Database\Eloquent\Model $data
     * @param int $status HTTP status code
     * @param array $extraHeaders
     * @return validated input
     */
    function formatArrayToString($message)
    {
       return (is_array($message)) ? json_encode($message) : $message;
    }
}

if (!function_exists("removeNumberFormat")) {
    function removeNumberFormat($string){
        $num = str_replace(",", "", $string);
        return (float)$num;
    }
}

if (!function_exists("getCurentTime")) {
    /**
     * @param array | Illuminate\Database\Eloquent\Model $data
     * @param int $status HTTP status code
     * @param array $extraHeaders
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    function getCurentTime()
    {
        return dateFormat(Carbon::now());
    }
}


if (!function_exists("addDay")) {
    /**
     * @param array | Illuminate\Database\Eloquent\Model $data
     * @param int $status HTTP status code
     * @param array $extraHeaders
     * @return validated input
     */
    function addDay($date,$day=0)
    {
        return dateFormat(Carbon::parse($date)->addDays($day));
    }
}


if (!function_exists("validateGreaterOrEqualDateTime")) {
    /**
     * @param array | Illuminate\Database\Eloquent\Model $data
     * @param int $status HTTP status code
     * @param array $extraHeaders
     * @return validated input
     */
    function validateGreaterOrEqualDateTime($date,$compareDate)
    {
        return Carbon::parse($date)->gte(Carbon::parse($compareDate));
    }
}


if (!function_exists("validateLessOrEqualDateTime")) {
    /**
     * @param array | Illuminate\Database\Eloquent\Model $data
     * @param int $status HTTP status code
     * @param array $extraHeaders
     * @return validated input
     */
    function validateLessOrEqualDateTime($date,$compareDate)
    {
        return Carbon::parse($date)->lte(Carbon::parse($compareDate));
    }
}



if (!function_exists("settings")) {
    function settings($key)
    {
        static $settings;
        if(strtoupper(env('APP_ENV')) == 'LOCAL'){
            $settings = env($key);
        }
        
        
        
        
        if(is_null($settings) || $settings == ''){
            $settingService = new SystemSettingsService();
            $setting = Cache::tags([$settingService->getCacheTag()])->remember($settingService->getCacheKey(), 24*60, function() use ($settingService) {
                return Arr::pluck($settingService->lists(), 'value', 'key');
            });
            
            
            if((is_array($key))){
                return Arr::only($setting, $key);
            }else{
                return $setting[$key] ?? null;
            }
        }else{
            return $settings;
        }
        
        
        //return (is_array($key)) ? Arr::only($settings, $key) : $settings[$key];
    }
}
