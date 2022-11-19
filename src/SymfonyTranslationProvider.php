<?php

namespace Neusta\Pimcore\TranslationMigrationBundle;

use Psr\Container\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

final class SymfonyTranslationProvider
{
    /**
     * @param ContainerInterface          $loaderLocator
     * @param array<string, list<string>> $loaderIds
     * @param array<string, string>       $resourceDirectories
     */
    public function __construct(
        private ContainerInterface $loaderLocator,
        private array $loaderIds,
        private array $resourceDirectories,
    ) {
    }

    /**
     * @return array<string, MessageCatalogue>
     */
    public function getTranslations(): array
    {
        $resourcesByLocale = [];
        foreach ($this->resourceDirectories as $dir) {
            $finder = Finder::create()
                ->followLinks()
                ->files()
                ->filter(function (\SplFileInfo $file) {
                    return 2 <= substr_count($file->getBasename(), '.') && preg_match('/\.\w+$/', $file->getBasename());
                })
                ->in($dir)
                ->sortByName();
            foreach ($finder as $file) {
                // filename is domain.locale.format
                $fileNameParts = explode('.', $file->getBasename());
                $format = array_pop($fileNameParts);
                $locale = array_pop($fileNameParts);
                $domain = implode('.', $fileNameParts);
                $resourcesByLocale[$locale][] = [$format, $file, $domain];
            }
        }

        $cataloguesByLocale = [];
        foreach ($resourcesByLocale as $locale => $resources) {
            $cataloguesByLocale[$locale] = new MessageCatalogue($locale);

            foreach ($resources as $resource) {
                if (!$loader = $this->getLoader($resource[0])) {
                    throw new \RuntimeException(sprintf('No loader is registered for the "%s" format when loading the "%s" resource.', $resource[0], $resource[1]));
                }

                $cataloguesByLocale[$locale]->addCatalogue($loader->load($resource[1], $locale, $resource[2]));
            }
        }

        return $cataloguesByLocale;
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
