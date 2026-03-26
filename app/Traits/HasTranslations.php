<?php

namespace App\Traits;

use ReflectionClass;

trait HasTranslations
{
    public function translations()
    {
        return $this->hasMany($this->getTranslationModel());
    }

    public function translation($locale = null)
    {
        $locale = $locale ?? app()->getLocale();

        return $this->hasOne($this->getTranslationModel())->where('locale', $locale);
    }

    private function getTranslationModel()
    {
        $modelName = (new ReflectionClass($this))->getShortName() . 'Translation';

        return "App\Models\Translations\\$modelName";
    }
}
