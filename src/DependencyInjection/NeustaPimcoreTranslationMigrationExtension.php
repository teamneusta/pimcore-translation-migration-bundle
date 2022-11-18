<?php declare(strict_types=1);

namespace Neusta\Pimcore\TranslationMigrationBundle\DependencyInjection;

use Neusta\Pimcore\TranslationMigrationBundle\Command\TranslationsMigrateCommand;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

final class NeustaPimcoreTranslationMigrationExtension extends ConfigurableExtension
{
    public function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        $loader->load('services.yaml');

        $directories = $this->getTranslationFileDirectories($container);

        $container
            ->findDefinition(TranslationsMigrateCommand::class)
            ->replaceArgument('$translationFilePaths', $directories);
    }

    private function getTranslationFileDirectories(ContainerBuilder $container): array
    {
        // Add translation directory of bundles
        $directories = [];
        $bundlesMetadata = $container->getParameter('kernel.bundles_metadata');
        foreach ($bundlesMetadata as $bundleMetadata) {
            if (
                is_dir($dir = $bundleMetadata['path'] . '/Resources/translations')
                || is_dir($dir = $bundleMetadata['path'] . '/translations')
            ) {
                $directories[] = $dir;
            }
        }

        // Add translation directory of project
        $projectDir = $container->getParameter('kernel.project_dir');
        if (is_dir($dir = $projectDir . '/Resources/translations') || is_dir($dir = $projectDir . '/translations')) {
            $directories[] = $dir;
        }

        return $directories;
    }
}
