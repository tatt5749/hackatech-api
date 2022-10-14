<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\SystemSettingResource;

class SystemSettingsService
{
    const CACHE_KEY = "system_settings";
    const CACHE_TAG = "systemSettings";
    
    public function __construct()
    {
        
        
    }
    
    

    public function lists()
    {
        $records = SystemSetting::all();
        
        return SystemSettingResource::collection($records);
    }
    
    public function store($request)
    {
        $setting = SystemSetting::create([
            'key' => $request['system_setting_key'],
            'value' => $request['system_setting_value'],
        ]); 
        cacheService()::clearCacheTag([$this->getCacheTag()]);
        return $setting->id;
    }
    
    public function edit($request,$id)
    {
        $setting = SystemSetting::find($id);
        $setting->update([
            'key' =>   $request['system_setting_key'],
            'value' => $request['system_setting_value'],
        ]);
        cacheService()::clearCacheTag([$this->getCacheTag()]);
        return $setting->id;
    }
    
    public function getCacheTag()
    {
        return cacheService()::cacheTag( self::CACHE_TAG);
    }
    
    public function getCacheKey()
    {
        return self::CACHE_KEY;
    }
    

}