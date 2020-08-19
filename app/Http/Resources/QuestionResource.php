<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
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
            'description' => $this->getDescription(),
            'english_translation' => $this->getEnglishDescription(),
            'category' => [
                'id' => $this->topic->id,
                'value' => $this->topic->description,
            ],
            'status' => [
                'value' => $this->status->getValue(),
                'status' => $this->status->status(),
            ],
            'date' => $this->created_at->format('d.m.Y'),
        ];
    }
}
