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

                foreach(self::mapTranslations(request()->only($model->translatable)) as $translationData) {
                    (new $translationModel([
                        $translationModel::FOREIGN_KEY => $model->id,
                        ...$translationData
                    ]))->save();
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

    private static function mapTranslations(array $data): array
    {
        $result = [];

        foreach ($data as $attribute => $locales) {
            if (!is_array($locales)) {
                continue;
            }

            foreach ($locales as $locale => $value) {
                if (!isset($result[$locale])) {
                    $result[$locale] = ['locale' => $locale];
                }

                $result[$locale][$attribute] = $value;
            }
        }

        return array_values($result);
    }
}
