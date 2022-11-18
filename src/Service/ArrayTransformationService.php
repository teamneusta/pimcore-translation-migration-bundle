<?php declare(strict_types=1);

namespace Neusta\Pimcore\TranslationMigrationBundle\Service;

class ArrayTransformationService
{
    /**
     * @psalm-pure
     *
     * @template TLocale as string
     * @template TTranslationKey as string
     * @template TTranslationValue as string
     *
     * @param array<TLocale, array<TTranslationKey, TTranslationValue>> $translationsGroupedByLocale
     *
     * @return array<TTranslationKey, array<TLocale, TTranslationValue>>
     */
    public function groupByTranslationKey(array $translationsGroupedByLocale): array
    {
        $translationsGroupedByKey = [];

        foreach ($translationsGroupedByLocale as $locale => $translations) {
            foreach ($translations as $translationKey => $translationValue) {
                $translationsGroupedByKey[$translationKey][$locale] = $translationValue;
            }
        }

        return $translationsGroupedByKey;
    }
}
