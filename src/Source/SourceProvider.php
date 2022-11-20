<?php declare(strict_types=1);

namespace Neusta\Pimcore\TranslationMigrationBundle\Source;

use Neusta\Pimcore\TranslationMigrationBundle\Model\TranslationCollection;

interface SourceProvider
{
    /**
     * @return list<string>
     */
    public function getDirectories(): array;

    public function getTranslations(string $domain): TranslationCollection;
}
