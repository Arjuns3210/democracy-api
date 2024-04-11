<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ListContestResource extends JsonResource
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
            'contest_name' => $this->name,
            'sub_title' => $this->sub_title,
            'contest_date' => Carbon::parse($this->contest_date)->format('jS M y'),
            'contest_time_start' => Carbon::parse($this->start_time)->format('h:i A'),
            'total_question' => $this->question_count,
        ];
    }
}
