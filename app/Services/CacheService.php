<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    
    public static function cacheTag($tag)
    {
        $appName = str_replace(' ', '_',  env("APP_NAME"));
        return $appName."_".$tag;
    }
    
    public static function cacheKey($key)
    {
        return md5($key);
    }
    
    public static function clearCacheTag(array $tag=[])
    {
        
        if(sizeof($tag) > 0 ){
            Cache::tags($tag)->flush();
        }
    }
}