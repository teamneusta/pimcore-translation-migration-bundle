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
        private SourceFinder $finder,
        private array $resourceDirectories,
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

                // check for "kernel.enabled_locales"

                foreach ($this->load($file)->all($domain) as $id => $translation) {
                    $collection->add($file->locale(), $id, $translation);
                }
            }
        }

        return $collection;
    }

    private function load(TranslationFileInfo $file): MessageCatalogue
    {
        if (!$loader = $this->loaders[$file->format()] ?? null) {
            throw new \RuntimeException(sprintf('No loader is registered for the "%s" format when loading the "%s" resource.', $file->format(), $file->file()));
        }

        return $loader->load($file->file(), $file->locale(), $file->domain());
    }
}
