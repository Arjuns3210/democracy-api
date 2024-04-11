<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class ListEnrollContestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this['id'],
            'contest_id' => $this['contest']['id'],
            'name' => $this['contest']['name'],
            'contest_date' => Carbon::parse($this['contest']['contest_date'])->format('jS M y'),
            'start_time' => Carbon::parse($this['contest']['start_time'])->format('h:i A'),
            'end_time' => Carbon::parse($this['contest']['end_time'])->format('h:i A'),
            'contest_details' => $this['contest_details'] ?? ''
        ];
    }
}
