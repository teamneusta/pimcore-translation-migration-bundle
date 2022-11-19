<?php

namespace Neusta\Pimcore\TranslationMigrationBundle;

use Pimcore\Model\Translation;

final class PimcoreTranslationRepository
{
    /**
     * @return string[]
     */
    public function getModifiedIds(): array
    {
        $translationIds = [];
        foreach ((new Translation\Listing())->load() as $databaseTranslation) {
            if ($databaseTranslation->getCreationDate() !== $databaseTranslation->getModificationDate()) {
                $translationIds[] = $databaseTranslation->getKey();
            }
        }

        return $translationIds;
    }

    public function save(TranslationCollection $collection): void
    {
        foreach ($collection as $key => $translationsByLocale) {
            $translation = new Translation();
            $translation->setKey($key);
            $translation->setTranslations($translationsByLocale);
            $translation->save();
        }
    }
}
