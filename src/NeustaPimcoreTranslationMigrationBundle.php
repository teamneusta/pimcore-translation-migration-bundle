<?php declare(strict_types=1);

namespace Neusta\Pimcore\TranslationMigrationBundle;

use Neusta\Pimcore\TranslationMigrationBundle\DependencyInjection\SourceProviderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class NeustaPimcoreTranslationMigrationBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new SourceProviderPass());
    }
}
