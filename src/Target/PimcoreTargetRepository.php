<?php declare(strict_types=1);

namespace Neusta\Pimcore\TranslationMigrationBundle\Target;

use Neusta\Pimcore\TranslationMigrationBundle\Model\TranslationCollection;
use Pimcore\Model\Translation;

final class PimcoreTargetRepository implements TargetRepository
{
    public function count(): int
    {
        return (new Translation\Listing())->getTotalCount();
    }

    public function getModifiedIds(): array
    {
        $translationIds = [];
        foreach ((new Translation\Listing())->load() as $databaseTranslation) {
            if ($databaseTranslation->getCreationDate() === $databaseTranslation->getModificationDate()) {
                continue;
            }

            if (null !== $key = $databaseTranslation->getKey()) {
                $translationIds[] = $key;
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
