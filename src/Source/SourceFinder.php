<?php declare(strict_types=1);

namespace Neusta\Pimcore\TranslationMigrationBundle\Source;

use Neusta\Pimcore\TranslationMigrationBundle\Model\TranslationFileInfo;

/**
 * @template-extends \IteratorAggregate<TranslationFileInfo>
 */
interface SourceFinder extends \IteratorAggregate
{
    /**
     * @return $this
     */
    public function find(string $directory): static;
}
