<?php 

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;


class SystemSettingResource extends JsonResource
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
            'key' => $this->key,
            'value' => $this->value,
            'created_at' => dateFormat($this->created_at),
            'updated_at' => dateFormat($this->updated_at),
            
        ];
    }
}