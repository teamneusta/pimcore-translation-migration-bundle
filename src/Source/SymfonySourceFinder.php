<?php declare(strict_types=1);

namespace Neusta\Pimcore\TranslationMigrationBundle\Source;

use Neusta\Pimcore\TranslationMigrationBundle\Model\TranslationFileInfo;
use Symfony\Component\Finder\Finder;

final class SymfonySourceFinder implements SourceFinder
{
    private ?Finder $finder = null;

    public function find(string $directory): static
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

    public function getIterator(): \Generator
    {
        if ($this->finder) {
            foreach ($this->finder as $file) {
                yield TranslationFileInfo::fromSplFileInfo($file);
            }
        }
    }
}
