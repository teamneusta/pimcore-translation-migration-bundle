<?php declare(strict_types=1);

namespace Neusta\Pimcore\TranslationMigrationBundle\Source;

use Neusta\Pimcore\TranslationMigrationBundle\Model\TranslationCollection;
use Symfony\Component\Translation\Loader\LoaderInterface;

final class SymfonySourceProvider implements SourceProvider
{
    /** @var array<string, LoaderInterface> */
    private array $loaders = [];

    /**
     * @param list<string>  $resourceDirectories
     * @param array<string> $enabledLocales
     */
    public function __construct(
        private SourceFinder $finder,
        private array $resourceDirectories,
        private array $enabledLocales,
    ) {
    }

    public function addLoader(string $format, LoaderInterface $loader): void
    {
        $this->loaders[$format] = $loader;
    }

    /**
     * @return list<string>
     */
    public function getDirectories(): array
    {
        return $this->resourceDirectories;
    }

    public function getTranslations(string $domain): TranslationCollection
    {
        $collection = new TranslationCollection($domain);

        foreach ($this->resourceDirectories as $directory) {
            foreach ($this->finder->find($directory) as $file) {
                if ($file->domain() !== $domain) {
                    continue;
                }

                if (!in_array($file->locale(), $this->enabledLocales, true)) {
                    continue;
                }

                if (!$loader = $this->loaders[$file->format()] ?? null) {
                    continue;
                }

                $collection = $collection->withCatalogue($loader->load($file, $file->locale(), $file->domain()));
            }
        }

        return $collection;
    }
}
