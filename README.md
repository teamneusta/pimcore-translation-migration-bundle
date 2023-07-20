# Pimcore Translation Migration Bundle

![CI](https://github.com/teamneusta/pimcore-translation-migration-bundle/actions/workflows/test-and-qa.yaml/badge.svg)

![Software License](https://img.shields.io/badge/license-GPLv3-informational.svg)

This bundle combines the advantages of Symfony translation files and translations in the Pimcore admin backend.

This bundle reads standard symfony translation files and migrates them to Pimcore translations. Changed Pimcore translations are not overwritten (compared `creationDate` and `modificationDate`).

## Installation

1. **Require the bundle**

   ```shell script
   composer require teamneusta/pimcore-translation-migration-bundle
   ```

2. **Enable the bundle**

   Add the Translation Migration Bundle to your `config/bundles.php`:

   ```php
   Neusta\Pimcore\TranslationMigrationBundle\NeustaPimcoreTranslationMigrationBundle::class => ['all' => true],
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

To develop on local machine, the vendor dependencies are required.

```shell
bin/composer install
```

We use composer scripts for our main quality tools. They can be executed via the `bin/composer` file as well.

```shell
bin/composer cs:fix
bin/composer phpstan
```

For the tests there is a different script, that includes a database setup.

```shell
bin/run-tests
```
