<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Services\OtpService;
use App\Mail\SendRegister;
use App\Enums\OtpType;
use App\Http\Resources\UserResource;

class UserService
{
  
    public function __construct()
    {
        
    }
    
    public function index($userId)
    {
        return new UserResource(User::findOrFail($userId),true);
    }
    
    public function edit($userId,$request)
    {
        $user;
        DB::transaction(function () use ($request,$userId,&$user) {
            $user = User::find($userId);
            $user->update([
                "name" =>  $request['name'] ?? $user->name ,
                'email' =>  $request['email'] ?? $user->email ,
                'phone'  =>  $request['phone'] ?? $user->phone,
                'phone_prefix_code'  =>  $request['phone_prefix_code'] ?? $user->phone_prefix_code,
                'phone_country_code'  =>  $request['phone_country_code'] ?? $user->phone_country_code,
            ]);
           
            $user->info()->update([
                'gender' => $request['gender'] ?? $user->info->gender,
            ]);
           ;
        });
        
        return new UserResource($user->refresh());
    }
    
    public function updateWalletInfo($userId){
        
        $user = User::findOrFail($userId);
        
        $userInfo = $user->info;
        
        if(is_null($userInfo->private_key) || $userInfo->private_key == ""){
            $wallet = $this->createWallet();
        
            $user->info()->update($wallet);
            
            return new UserResource($user->refresh());
        }else{
            throwApiErrorException([
                    'wallet_address' => ['Wallet already setup.']
                ]
            );
        }
    }
    
    private function createWallet(){
        return [
            'wallet_address' => '0xf1D900d77470B06b170Be6a96115bDe392d69541',
            'private_key' => '60ae6c2fbc1051c9f2fbf9bc37e7b034a79e8c62fc95d147bc4a91e086dacc9a'
        ];
    }
}