<?php declare(strict_types=1);

namespace Neusta\Pimcore\TranslationMigrationBundle\DependencyInjection;

use Neusta\Pimcore\TranslationMigrationBundle\Source\SymfonySourceProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class SourceProviderPass implements CompilerPassInterface
{
    private const SOURCE_PROVIDER_ID = SymfonySourceProvider::class;
    private const LOADER_TAG = 'translation.loader';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(self::SOURCE_PROVIDER_ID)) {
            return;
        }

        $loaders = [];
        $loaderRefs = [];
        foreach ($container->findTaggedServiceIds(self::LOADER_TAG, true) as $id => $attributes) {
            $loaderRefs[$id] = new Reference($id);
            $loaders[$id][] = $attributes[0]['alias'];
            if (isset($attributes[0]['legacy-alias'])) {
                $loaders[$id][] = $attributes[0]['legacy-alias'];
            }
        }

        $definition = $container->getDefinition(self::SOURCE_PROVIDER_ID);
        foreach ($loaders as $id => $formats) {
            foreach ($formats as $format) {
                $definition->addMethodCall('addLoader', [$format, $loaderRefs[$id]]);
            }
        }
    }
}
