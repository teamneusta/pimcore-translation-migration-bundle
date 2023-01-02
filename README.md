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

Add the bundle to your application. For example via the `Kernel.php`.

```shell
use Neusta\Pimcore\TranslationMigrationBundle\NeustaPimcoreTranslationMigrationBundle;
...
    public function registerBundlesToCollection(BundleCollection $collection)
    {
        ...
        $collection->addBundle(new NeustaPimcoreTranslationMigrationBundle());
    }
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

### Running tests for development

```shell
./run-tests.sh
```

Only supported on Linux.

### Further development

Pipelines will tell you, when code does not meet our standards. To use the same tools in local development, take the Docker command from above with other scripts from the `composer.json`. For example:

* cs:check
* phpstan

```shell
docker run -it --rm -v $(pwd):/app -w /app pimcore/pimcore:PHP8.1-cli composer install --ignore-platform-reqs
docker run -it --rm -v $(pwd):/app -w /app pimcore/pimcore:PHP8.1-cli composer <composer-script>
```

Only supported on Linux.
