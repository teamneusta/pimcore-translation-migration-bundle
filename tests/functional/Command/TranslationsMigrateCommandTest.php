<?php declare(strict_types=1);

namespace Neusta\Pimcore\TranslationMigrationBundle\Tests\Functional\Command;

use Neusta\Pimcore\TranslationMigrationBundle\Tests\Functional\Database\ResetDatabase;
use Pimcore\Cache\RuntimeCache;
use Pimcore\Model\Translation;
use Pimcore\Test\KernelTestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class TranslationsMigrateCommandTest extends KernelTestCase
{
    use ResetDatabase;
    use MatchesSnapshots;

    private const COMMAND_NAME = 'neusta:translations:migrate';

    private CommandTester $commandTester;
    private string $translationFixtureDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        static::$kernel = static::createKernel();

        $this->commandTester = new CommandTester((new Application(static::$kernel))->find(self::COMMAND_NAME));
        $this->translationFixtureDirectory = PIMCORE_PROJECT_ROOT . '/tests/translation-fixtures';

        (new Filesystem())->mkdir($this->translationFixtureDirectory);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        (new Filesystem())->remove($this->translationFixtureDirectory);
    }

    /** @test */
    public function execute_must_create_pimcore_translation(): void
    {
        $this->createTranslationFile('en', ['test.translation.key' => 'Value of test translation']);

        $this->commandTester->execute([], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);

        $this->commandTester->assertCommandIsSuccessful();
        $this->assertMatchesTextSnapshot($this->commandTester->getDisplay());
        $this->assertTranslationIsSame(['en' => 'Value of test translation'], 'test.translation.key');
    }

    /** @test */
    public function execute_must_update_existing_key_when_translation_is_untouched(): void
    {
        $this->createPimcoreTranslation('test.translation.key', ['en' => 'Value before update']);
        $this->createTranslationFile('en', ['test.translation.key' => 'Value of test translation']);

        $this->commandTester->execute([], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);

        $this->commandTester->assertCommandIsSuccessful();
        $this->assertMatchesTextSnapshot($this->commandTester->getDisplay());
        $this->assertTranslationIsSame(['en' => 'Value of test translation'], 'test.translation.key');
    }

    /** @test */
    public function execute_must_not_update_modified_translation(): void
    {
        $translation = $this->createPimcoreTranslation(
            'test.translation.key',
            ['en' => 'Some random initial value'],
        );

        \sleep(1);
        // update modification date
        $translation->setTranslations(['en' => 'Modified translation value']);
        $translation->save();

        $this->createTranslationFile('en', ['test.translation.key' => 'Value of test translation']);

        $this->commandTester->execute([], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);

        $this->commandTester->assertCommandIsSuccessful();
        $this->assertMatchesTextSnapshot($this->commandTester->getDisplay());
        $this->assertTranslationIsSame(['en' => 'Modified translation value'], 'test.translation.key');
    }

    private function createTranslationFile(string $language, array $translations): void
    {
        file_put_contents(
            $this->translationFixtureDirectory . "/messages.{$language}.yaml",
            Yaml::dump($translations),
        );
    }

    private function createPimcoreTranslation(string $key, array $translations): Translation
    {
        $translation = new Translation();
        $translation->setKey($key);
        $translation->setTranslations($translations);

        $translation->save();

        return $translation;
    }

    private function assertTranslationIsSame(array $expected, string $key): void
    {
        RuntimeCache::clear();
        self::assertSame($expected, Translation::getByKey($key)?->getTranslations());
    }
}
