<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use ReflectionClass;

trait HasTranslations
{
    protected static function bootTranslations()
    {
        static::saved(function ($model) {
            $translationModel = $model->getTranslationModel();

            DB::transaction(function () use ($model, $translationModel) {
                $model->deleteTranslations();

                foreach (request()->only($model->translatable) as $attribute => $locales) {
                    foreach ($locales as $locale => $value) {
                        (new $translationModel([
                            $translationModel::FOREIGN_KEY => $model->id,
                            'locale' => $locale,
                            $attribute => $value
                        ]))->save();
                    }
                }
            });
        });

        static::deleted(function ($model) {
            $model->deleteTranslations();
        });
    }

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

    private function deleteTranslations()
    {
        $translationModel = $this->getTranslationModel();

        DB::transaction(function () use ($translationModel) {
            $translationModel::query()->where($translationModel::FOREIGN_KEY, $this->id)->delete();
        });
    }
}
