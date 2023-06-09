# Pimcore Translation Migration Bundle

![CI](https://github.com/teamneusta/pimcore-translation-migration-bundle/actions/workflows/test-and-qa.yaml/badge.svg)

![Software License](https://img.shields.io/badge/license-GPLv3-informational.svg)

This bundle combines the advantages of Symfony translation files and translations in the Pimcore admin backend.

This bundle reads standard symfony translation files and migrates them to Pimcore translations. Changed Pimcore translations are not overwritten (compared `creationDate` and `modificationDate`).

## Installation

Require via Composer

```shell
composer require teamneusta/pimcore-translation-migration-bundle
```

Enable the bundle via the [Symfony Bundle System](https://symfony.com/doc/current/bundles.html).

```php
// config/bundles.php
return [
    ...
    Neusta\Pimcore\TranslationMigrationBundle\NeustaPimcoreTranslationMigrationBundle::class => ['all' => true],
];
```

## Usage

This bundle provides a Symfony command that just executes the migration.

```shell
bin/console neusta:translations:migrate
```

For an example of how to use it, look at the [documentation](docs/index.md).

## Configuration

There is no configuration available.

## Contribution

Feel free to open issues for any bug, feature request, or other ideas.

Please remember to create an issue before creating large pull requests.

### Local Development

For every purpose there is an Docker Compose profile. Running the 'init' profile once is required.

```shell
docker compose --profile init up
docker compose --profile test up
docker compose --profile php-cs-fixer up
docker compose --profile phpstan up
```
