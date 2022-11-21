<?php declare(strict_types=1);

namespace Neusta\Pimcore\TranslationMigrationBundle\Event;

use Neusta\Pimcore\TranslationMigrationBundle\Model\TranslationFileInfo;

final class FileWasLoaded
{
    public function __construct(
        public TranslationFileInfo $file,
    ) {
    }
}
