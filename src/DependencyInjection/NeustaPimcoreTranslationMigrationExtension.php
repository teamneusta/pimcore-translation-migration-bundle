<?php declare(strict_types=1);

namespace Neusta\Pimcore\TranslationMigrationBundle\DependencyInjection;

use Neusta\Pimcore\TranslationMigrationBundle\Source\SymfonySourceProvider;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

final class NeustaPimcoreTranslationMigrationExtension extends ConfigurableExtension
{
    public function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(\dirname(__DIR__, 2) . '/config'));
        $loader->load('services.php');

        $container
            ->findDefinition(SymfonySourceProvider::class)
            ->replaceArgument('$resourceDirectories', $this->getTranslationFileDirectories($container));
    }

    private function getTranslationFileDirectories(ContainerBuilder $container): array
    {
        $directories = [];

        // Add translation directories of bundles
        foreach ($container->getParameter('kernel.bundles_metadata') as ['path' => $bundleDir]) {
            if (is_dir($dir = $bundleDir . '/Resources/translations') || is_dir($dir = $bundleDir . '/translations')) {
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
