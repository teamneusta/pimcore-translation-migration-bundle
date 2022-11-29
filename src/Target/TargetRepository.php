<?php declare(strict_types=1);

namespace Neusta\Pimcore\TranslationMigrationBundle\Target;

use Neusta\Pimcore\TranslationMigrationBundle\Model\TranslationCollection;

interface TargetRepository
{
    public function count(): int;

    /**
     * @return list<string>
     */
    public function getModifiedIds(): array;

    public function save(TranslationCollection $collection): void;
}
