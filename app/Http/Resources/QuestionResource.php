<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    private function getTopicData(): ?array
    {
        if ($this->topic) {
            return [
                'id' => $this->topic->id,
                'value' => $this->topic->description,
            ];
        }

        return NULL;
    }
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // TODO Need to expose additional fields
        return [
            'id' => $this->id,
            'descriptions' => $this->languages->keyBy('code')->map(function($language) {
                return $language->pivot->description;
            }),
            'topic' => $this->getTopicData(),
            'status' => [
                'value' => $this->status->getValue(),
                'status' => $this->status->status(),
                'transitionable' => $this->status->transitionableStates(),
            ],
            'date' => $this->created_at->format('d.m.Y'),
        ];
    }
}
