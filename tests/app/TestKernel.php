<?php declare(strict_types=1);

use Neusta\Pimcore\TestingFramework\Kernel\TestKernel as TestingFrameworkTestKernel;
use Neusta\Pimcore\TranslationMigrationBundle\NeustaPimcoreTranslationMigrationBundle;
use Pimcore\HttpKernel\BundleCollection\BundleCollection;

class TestKernel extends TestingFrameworkTestKernel
{
    public function registerBundlesToCollection(BundleCollection $collection): void
    {
        $collection->addBundle(new NeustaPimcoreTranslationMigrationBundle());
    }
}
