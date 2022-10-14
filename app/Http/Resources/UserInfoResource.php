<?php 

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;


class UserInfoResource extends JsonResource
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
            'identity_number' => $this->identity_number,
            'gender' => $this->gender,
            'country' => $this->country,
            "wallet_address" => $this->wallet_address,
        ];
    }
}