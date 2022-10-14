<?php

namespace App\Services\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\OtpService;
use App\Mail\SendRegister;
use App\Enums\OtpType;
use Jenssegers\Agent\Agent;
use App\Enums\GeneralStatus;
use App\Http\Resources\UserResource;

class AuthService
{
  
    public function __construct()
    {
    }
    
    public function register($request)
    {
        $user = '';
        
        DB::transaction(function () use (&$user,$request) { 
            $user = User::create([
                'name' => $request['name'],
                'email' => $request['email'],
                'phone' => $request['phone'],
                'phone_country_code' => $request['phone_country_code'],
                'phone_prefix_code' => $request['phone_prefix_code'],
                'password' => Hash::make($request['password']),
            ]); 
            $userInfo['user_id'] = $user->id;
            $user->info()->create($userInfo);
            //$sendEmailOtp = $this->sendRegisterEmail($user->id,$request['email']);
            //$user['otp_uuid'] =  $sendEmailOtp['otp_uuid'];
        });
        
        return [
            'id' => $user->id,
            'name' => $request['name'],
            'email' => $request['email'],
            'phone' => $request['phone'],
            'otp_uuid' => $user['otp_uuid'],
        ];
    }
    
    
    public function loginEmail($request)
    {
        $user = User::where('email', $request['email'])->where('status',GeneralStatus::ACTIVE)->get()->first();
        return $this->login($user,$request);
    }
    
    public function loginPhone($request)
    {
        $user = User::where('phone', $request['phone'])->where('status',GeneralStatus::ACTIVE)->get()->first();
        return $this->login($user,$request);
    }
    
    protected function login($user,$request)
    {
        //Log::Info(Hash::make($request['password']));
        if (! $user || ! Hash::check($request['password'], $user->password)) {
            throwApiErrorException(
                trans('auth.failed'),
            );
        }
        $agent = new Agent();
        $device = $agent->device();
        $device = ($device) ? $device : '';
        $user->push_token = $request['token'] ?? "";
        $user->save();
        $user->tokens()->where('name', $device)->delete();
        
        return [
            'user' => new UserResource($user),
            'token' =>   $user->createToken($device)->plainTextToken  
        ];
    }
    
    
    public function sendRegisterEmail($userId,$email)
    {
        $optService = new OtpService();
        $otpArr = $optService->generateOtp($userId,$email,OtpType::REGISTER);
        Mail::to($email)->send(new SendRegister($otpArr['otp']));

        return ['otp_uuid' => $otpArr['uuid'] ];
    }
    
   
    
    
    
    public function validateEmail($request)
    {
        $otpService = new OtpService();
        $validateEmail = $otpService->validateOtp($request);
        if(isset($validateEmail['status']) && $validateEmail['status'] ){
            $user  = User::firstWhere('email', $validateEmail['email']);
            $user->update([
                "email_verified_at" =>  dateToday(),
            ]);
        }
        return [
            'status' => true,
            'email' => $validateEmail['email'],
        ];
    }
    
}