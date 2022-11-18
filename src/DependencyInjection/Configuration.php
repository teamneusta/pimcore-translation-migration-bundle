<?php declare(strict_types=1);

namespace Neusta\Pimcore\TranslationMigrationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('neusta_pimcore_translation_migration');
        $rootNode = $treeBuilder->getRootNode();

        return $treeBuilder;
    }
}
