<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class BaseRequest extends FormRequest
{
  
    public function rules(Request $request)
    {
        // 验证方法名，对应控制器中的同名方，法获取控制器的方法名
        $verifyMethod = \Route::current() ? \Route::current()->getActionMethod() : '';
        // 如果验证类中与控制器同名的验证方法规则不存在,则返回空的规则,也就是不进行验证
        if(! method_exists($this, $verifyMethod)) {
            return [];
        }
        // 返回自定义的验证规则
        
      
        return $this->{$verifyMethod}($request);
    }
}