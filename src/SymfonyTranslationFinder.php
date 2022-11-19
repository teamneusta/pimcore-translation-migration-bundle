<?php

namespace Neusta\Pimcore\TranslationMigrationBundle;

use Symfony\Component\Finder\Finder;

final class SymfonyTranslationFinder implements \IteratorAggregate
{
    private ?Finder $finder = null;

    public function find(string $directory): self
    {
        $this->finder = Finder::create()
            ->followLinks()
            ->files()
            ->filter(function (\SplFileInfo $file) {
                return 2 <= substr_count($file->getBasename(), '.') && preg_match('/\.\w+$/', $file->getBasename());
            })
            ->in($directory)
            ->sortByName();

        return $this;
    }

    /**
     * @return \Generator<TranslationFileInfo>
     */
    public function getIterator(): \Generator
    {
        if ($this->finder) {
            foreach ($this->finder as $file) {
                yield TranslationFileInfo::fromSplFileInfo($file);
            }
        }
    }
}
