<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\AuthRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Enums\GeneralStatus;
use App\Services\Auth\AuthService;
use App\Services\OtpService;

class AuthController extends Controller
{
    private $service;
    
     public function __construct()
    {
        $this->service = new AuthService();
    }
    
   
    
    public function loginEmail(AuthRequest $request)
    {
        $params = $request->validated();
        return httpResponse()::httpSuccess($this->service->loginEmail($params));
        
    }
    
    public function loginPhone(AuthRequest $request)
    {
        $params = $request->validated();
        return httpResponse()::httpSuccess($this->service->loginPhone($params));
        
    }
    

}
