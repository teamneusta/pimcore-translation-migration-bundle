<?php declare(strict_types=1);

namespace Neusta\Pimcore\TranslationMigrationBundle;

use Psr\Container\ContainerInterface;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

final class SymfonyTranslationProvider
{
    /**
     * @param array<string, list<string>> $loaderIds
     * @param list<string>                $resourceDirectories
     */
    public function __construct(
        private ContainerInterface $loaderLocator,
        private array $loaderIds,
        private SymfonyTranslationFinder $finder,
        private array $resourceDirectories,
    ) {
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
        if (!$loader = $this->getLoader($file->format())) {
            throw new \RuntimeException(sprintf(
                'No loader is registered for the "%s" format when loading the "%s" resource.',
                $file->format(),
                $file->file(),
            ));
        }

        return $loader->load($file->file(), $file->locale(), $file->domain());
    }

    private function getLoader(string $format): ?LoaderInterface
    {
        foreach ($this->loaderIds as $id => $aliases) {
            if (in_array($format, $aliases, true) && $this->loaderLocator->has($id)) {
                return $this->loaderLocator->get($id);
            }
        }

        return null;
    }
}
