<?php 

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use App\Http\Resources\UserInfoResource;


class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => intval($this->id),
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'phone_country_code' => $this->phone_country_code,
            'phone_prefix_code' => $this->phone_prefix_code,
            'email_verify' => ( is_null($this->email_verified_at) || $this->email_verified_at == ''  ) ? 0 : 1,
            'phone_verify' => ( is_null($this->phone_verified_at) || $this->phone_verified_at == ''  ) ? 0 : 1,
            'created_at' => dateFormat($this->created_at),
            'user_info' => new UserInfoResource( $this->info),
        ];
    }
}