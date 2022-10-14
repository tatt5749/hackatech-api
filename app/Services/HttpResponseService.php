<?php

namespace App\Services;


class HttpResponseService
{
    
    public static function httpSuccess($data=[], $status = 200, $extraHeaders = [])
    {
        $data =  is_object($data) ?  json_encode($data,true)  : $data;
       
        if(!isset($data['data'])){
             
            $response = [
                "data" => formatJsonObjectToArray($data)
            ];
        }else{
            $response = $data;
          
            $response['data'] = formatJsonObjectToArray($response['data']);
        }
        
        return response()->json($response, $status, $extraHeaders);
    }
    
    public static function httpFail($data = [], $status = 400, $extraHeaders = [])
    {
        
        $response = [
            "error" => formatJsonObjectToArray($data)
        ];

        return response()->json($response, $status, $extraHeaders);
    }
    
    public static function httpError($message=null, $code = null, $data = null, $status = 500, $extraHeaders = [])
    {
        $message = (!is_null($message)) ? formatJsonObjectToArray($message) :  trans('application.internal_server_error');
        $response = [
            //"status" => "error",
            "system_error" => $message
        ];
        !is_null($code) && $response['code'] = $code;
        !is_null($data) && $response['data'] = $data;
        
        return response()->json($response, $status, $extraHeaders);
    }
    

}