<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PendingQuestion extends Model
{
    public function languages()
    {
        return $this->belongsToMany('App\Language')
            ->withPivot('description');
    }

    public function getDescription() : string
    {
        $languages = $this->languages()->get();
        
        if ($languages->count() > 1)
        {
            $language = $languages->first(function ($value, $key) {
                return $value->name !== 'English';
            });

            return $language->pivot->description;
        }

        return $languages->first()->pivot->description;
    }

    public function getEnglishDescription() : ?string
    {
        $languages = $this->languages()->get();

        if ($languages->count() > 0)
        {
            $language = $languages->first(function ($value, $key) {
                return $value->name === 'English';
            });

            return $language->pivot->description ?? NULL;
        }

        return NULL;
    }
}
