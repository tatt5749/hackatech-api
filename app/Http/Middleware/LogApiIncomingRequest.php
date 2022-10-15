<?php

namespace App\Http\Middleware;

use App\Models\ApiIncomingLog;
use Closure;
use Log;
class LogApiIncomingRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
    
    public function terminate($request, $response)
    {
        //$logFlag = settings('LOG_API_INCOMING') ?? false;
        $logFlag = true;
        $logFlag = filter_var($logFlag, FILTER_VALIDATE_BOOLEAN);
       // $responseLogFlag = filter_var(settings('LOG_API_INCOMING_RESPONSE'), FILTER_VALIDATE_BOOLEAN);
        $responseLogFlag = true;
        
        if ($logFlag) {
            $headerArr = $request->headers->all() ?? [];
            $header = json_encode($headerArr);
            $method = $request->method();
            $user_agent =  request()->server('HTTP_USER_AGENT') ?? null;
            $language = app()->getLocale();
            $responseCode =  $response->status();
            //$response = $response->getContent();
            
            
            if($responseCode >= 400){
                $responseContent = $response->getContent();
            }else{
                $responseContent = ($responseLogFlag) ? $response->getContent() : '{}';
            }
            
            ApiIncomingLog::create([
                'url' => $request->path(),
                'method' => $request->method(),
                'user_agent' => $user_agent,
                'header' => $header,
                'host' => request()->getSchemeAndHttpHost(),
                //'request' => json_encode($request->input()),
                'request' => json_encode($request->all()),
                'response' => $responseContent,
                'error_exception' => $response->exception,
                'ip' => getIp($request),
                'status_code' => $responseCode,
                'language' => $language,
            ]);
        }
    }
}
