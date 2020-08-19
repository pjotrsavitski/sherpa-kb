<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PendingQuestionResource extends JsonResource
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
            'id' => $this->id,
            'descriptions' => $this->languages->keyBy('code')->map(function($language) {
                return $language->pivot->description;
            }),
            'group' => $this->group_no,
            'date' => $this->created_at->format('d.m.Y'),
            'status' => [
                'value' => $this->status->getValue(),
                'status' => $this->status->status(),
            ],
        ];
    }
}
