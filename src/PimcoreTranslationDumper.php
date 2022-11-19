<?php

namespace Neusta\Pimcore\TranslationMigrationBundle;

use \Pimcore\Model\Translation;

final class PimcoreTranslationDumper
{
    public function __construct(
        private PimcoreTranslationRepository $repository,
    ) {
    }

    public function dump(TranslationCollection $collection): int
    {
        $collection = $collection->without(...$this->repository->getModifiedIds());

        $this->repository->save($collection);

        return count($collection);
    }
}
