<?php declare(strict_types=1);

namespace Neusta\Pimcore\TranslationMigrationBundle\DependencyInjection;

use Neusta\Pimcore\TranslationMigrationBundle\Source\SymfonySourceProvider;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class NeustaPimcoreTranslationMigrationExtension extends Extension
{
    /**
     * @param array<string, mixed> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(\dirname(__DIR__, 2) . '/config'));
        $loader->load('services.php');

        $container
            ->findDefinition(SymfonySourceProvider::class)
            ->replaceArgument(2, $this->getTranslationFileDirectories($container));
    }

    /**
     * @return list<string>
     */
    private function getTranslationFileDirectories(ContainerBuilder $container): array
    {
        $directories = [];

        // Add translation directories of bundles
        /** @var array<string, array{path: string, namespace: string}> $kernelBundlesMetadata */
        $kernelBundlesMetadata = $container->getParameter('kernel.bundles_metadata');
        foreach ($kernelBundlesMetadata as ['path' => $bundleDir]) {
            if (is_dir($dir = $bundleDir . '/Resources/translations') || is_dir($dir = $bundleDir . '/translations')) {
                $directories[] = $dir;
            }
        }

        // Add translation directory of project
        $projectDir = $container->getParameter('kernel.project_dir');
        if (is_string($projectDir)) {
            if (is_dir($dir = $projectDir . '/Resources/translations') || is_dir($dir = $projectDir . '/translations')) {
                $directories[] = $dir;
            }
        }

        return $directories;
    }
}
