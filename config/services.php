<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Neusta\Pimcore\TranslationMigrationBundle\Command\TranslationsMigrateCommand;
use Neusta\Pimcore\TranslationMigrationBundle\Source\SourceFinder;
use Neusta\Pimcore\TranslationMigrationBundle\Source\SourceProvider;
use Neusta\Pimcore\TranslationMigrationBundle\Source\SymfonySourceFinder;
use Neusta\Pimcore\TranslationMigrationBundle\Source\SymfonySourceProvider;
use Neusta\Pimcore\TranslationMigrationBundle\Target\PimcoreTargetRepository;
use Neusta\Pimcore\TranslationMigrationBundle\Target\TargetRepository;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set(SymfonySourceFinder::class, SymfonySourceFinder::class)
        ->alias(SourceFinder::class, SymfonySourceFinder::class)

        ->set(SymfonySourceProvider::class, SymfonySourceProvider::class)
            ->args([
                service(SourceFinder::class),
                [], // Resource directories
                param('kernel.enabled_locales'),
            ])
        ->alias(SourceProvider::class, SymfonySourceProvider::class)

        ->set(PimcoreTargetRepository::class, PimcoreTargetRepository::class)
        ->alias(TargetRepository::class, PimcoreTargetRepository::class)

        ->set(TranslationsMigrateCommand::class, TranslationsMigrateCommand::class)
            ->args([
                service(SourceProvider::class),
                service(TargetRepository::class),
            ])
            ->tag('console.command')
    ;
};
