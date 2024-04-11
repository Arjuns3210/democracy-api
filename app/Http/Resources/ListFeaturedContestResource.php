<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ListFeaturedContestResource extends JsonResource
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
            'id' => $this->id,
            'name' => $this->name,
            'sub_title' => $this->sub_title,
            'contest_date' => Carbon::parse($this->contest_date)->format('jS M y'),
            'start_time' => Carbon::parse($this->start_time)->format('h:i A'),
            'question_count' => $this->question_count." Questions",
        ];
    }
}
