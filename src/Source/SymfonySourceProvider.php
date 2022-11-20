<?php declare(strict_types=1);

namespace Neusta\Pimcore\TranslationMigrationBundle\Source;

use Neusta\Pimcore\TranslationMigrationBundle\Model\TranslationCollection;
use Neusta\Pimcore\TranslationMigrationBundle\Model\TranslationFileInfo;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

final class SymfonySourceProvider implements SourceProvider
{
    /** @var array<string, LoaderInterface> */
    private array $loaders = [];

    /**
     * @param list<string> $resourceDirectories
     */
    public function __construct(
        private array $resourceDirectories,
        private SourceFinder $finder,
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
        $collection = new TranslationCollection();

        foreach ($this->resourceDirectories as $directory) {
            foreach ($this->finder->find($directory) as $file) {
                if ($domain !== $file->domain()) {
                    continue;
                }

                if (!$loader = $this->loaders[$file->format()] ?? null) {
                    continue;
                }

                // check for "kernel.enabled_locales"

                foreach ($loader->load($file, $file->locale(), $file->domain())->all($domain) as $id => $translation) {
                    $collection->add($file->locale(), $id, $translation);
                }
            }
        }

        return $collection;
    }
}
