<?php

namespace Neusta\Pimcore\TranslationMigrationBundle;

use \Pimcore\Model\Translation;

final class PimcoreTranslationDumper
{
    public function dump(TranslationCollection $collection): void
    {
        $modifiedIds = [];
        foreach ((new Translation\Listing())->load() as $databaseTranslation) {
            if ($databaseTranslation->getCreationDate() !== $databaseTranslation->getModificationDate()) {
                $modifiedIds[] = $databaseTranslation->getKey();
            }
        }

        foreach ($collection->without(...$modifiedIds) as $key => $translations) {
            $newTranslation = new Translation();
            $newTranslation->setKey($key);
            $newTranslation->setTranslations($translations);
            $newTranslation->save();
        }
    }
}
