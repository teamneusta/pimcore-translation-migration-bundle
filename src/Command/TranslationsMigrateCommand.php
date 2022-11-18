<?php declare(strict_types=1);

namespace Neusta\Pimcore\TranslationMigrationBundle\Command;

use Neusta\Pimcore\TranslationMigrationBundle\Service\ArrayTransformationService;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\Translation;
use Pimcore\Model\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Yaml\Yaml;

final class TranslationsMigrateCommand extends AbstractCommand
{
    protected static $defaultName = 'neusta:translations:migrate';
    protected static $defaultDescription = 'Creates Pimcore translations for every YAML translation file.';
    private array $editableLanguages = [];

    /**
     * @param string[] $translationFilePaths
     */
    public function __construct(
        private array $translationFilePaths,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp(
            <<<'EOF'
            The <info>%command.name%</info> command reads typical Symfony translation files and migrates
            them to Pimcore translations. Existing Pimcore translations are updated only if they are not
            modified (CreationDate and ModificationDate are the same).

            Use <info>-v, --verbose</info> to output the amount of updated translations.

              <info>php %command.full_name%</info>
              <info>php %command.full_name% -v</info>

            By default, this command uses YAML translation files from %kernel.project_dir%/translations.
            This can be changed via Symfony service definitions (services.yaml).
            EOF
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->comment('Start migrating YAML translations to Pimcore translations');

        // User may be not allowed to store all languages
        // Idea from \Pimcore\Model\Translation\Dao
        $userId = \Pimcore\Tool\Admin::getCurrentUser()?->getId() ?? 0;
        $user = User::getById($userId);
        $this->editableLanguages = $user instanceof User ? $user->getAllowedLanguagesForEditingWebsiteTranslations() : [];

        $this->writelnVerbose('Read from directories:');
        if (OutputInterface::VERBOSITY_VERBOSE === $this->io->getVerbosity()) {
            $basePaths = \array_map(fn ($path) => $this->stripProjectPrefix($path), $this->translationFilePaths);
            $this->io->listing($basePaths);
        }

        $finder = $this->createFinder();
        $translationsFromFiles = $this->readTranslationsFromFiles($finder);

        $translationsGroupedByKey = $this->regroupTranslationArray($translationsFromFiles);
        $currentPimcoreTranslations = $this->loadPimcoreTranslations();

        $translationsGroupedByKey = $this->filterTranslationsFromFiles(
            $translationsGroupedByKey,
            $currentPimcoreTranslations,
        );

        $this->createPimcoreTranslations($translationsGroupedByKey);
        $this->io->info(\count($translationsGroupedByKey) . ' translation keys were added to Pimcore.');

        $this->io->success('Pimcore translations updated successfully');

        return Command::SUCCESS;
    }

    private function writelnVerbose(string $output): void
    {
        $this->output->writeln($output, OutputInterface::VERBOSITY_VERBOSE);
    }

    private function stripProjectPrefix(string $string): string
    {
        $prefix = PIMCORE_PROJECT_ROOT . '/';

        return str_starts_with($string, $prefix)
            ? substr($string, strlen($prefix))
            : $string;
    }

    private function createFinder(): Finder
    {
        $finder = new Finder();
        $finder->in($this->translationFilePaths);

        return $finder;
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function readTranslationsFromFiles(Finder $finder): array
    {
        $ioTableRows = [];
        $translationsFromFiles = [];
        foreach ($finder->files() as $translationYaml) {
            $filename = $translationYaml->getFilename();
            $basePath = $this->stripProjectPrefix($translationYaml->getRealPath());

            if (!$this->isYamlFile($filename)) {
                $ioTableRows[] = [$basePath, 'IGNORED: Not a \'messages\' Yaml file'];
                continue;
            }

            $locale = $this->getLocale($filename);

            // User may be not allowed to store all languages
            if (!\in_array($locale, $this->editableLanguages, true)) {
                $ioTableRows[] = [$basePath, 'IGNORED: User has no permission for editing this language'];
                continue;
            }

            // Parse can throw ParseException. Bubble it up.
            $newTranslations = Yaml::parse($translationYaml->getContents());
            $alreadyCollectedTranslations = $translationsFromFiles[$locale] ?? [];
            $translationsFromFiles[$locale] = array_merge($newTranslations, $alreadyCollectedTranslations);

            $ioTableRows[] = [$basePath, 'PARSED'];
        }

        $translationsFromFiles = $this->flattenTheYaml($translationsFromFiles);

        if (OutputInterface::VERBOSITY_VERBOSE === $this->io->getVerbosity()) {
            $headers = ['Realpath', 'Result'];
            $this->io->table($headers, $ioTableRows);
        }

        return $translationsFromFiles;
    }

    private function isYamlFile(string $filename): bool
    {
        return null !== $this->getLocale($filename);
    }

    private function getLocale(string $filename): ?string
    {
        // Matches 'messages.de.yaml' as well as 'messages+intl-icu.de_DE.yml'
        $result = preg_match('/messages.*\.(\w{2,5})\.(yml|yaml)$/', $filename, $matches);

        return 1 === $result || \count($matches) > 1 ? $matches[1] : null;
    }

    /**
     * If the Yaml contains hierarchical data, this method uses Parts from Symfony Translation
     * Component to flatten all keys by imploding them with dots.
     *
     * @param array<string, array<string, string>> $translationsFromFiles
     *
     * @return array<string, array<string, string>>
     */
    private function flattenTheYaml(array $translationsFromFiles): array
    {
        $locales = \array_keys($translationsFromFiles);

        foreach ($locales as $locale) {
            $messageCatalogue = (new ArrayLoader())->load($translationsFromFiles[$locale], $locale);
            $translationsFromFiles[$locale] = $messageCatalogue->all()['messages'];
        }

        return $translationsFromFiles;
    }

    /**
     * @param array<string, array<string, string>> $translationsFromFiles
     *
     * @return array<string, array<string, string>>
     */
    private function regroupTranslationArray(array $translationsFromFiles): array
    {
        $mergedTranslations = (new ArrayTransformationService())->groupByTranslationKey($translationsFromFiles);

        $this->writelnVerbose(
            sprintf('Found %s translation keys in translation files', \count($mergedTranslations)),
        );

        return $mergedTranslations;
    }

    /**
     * @return array<Translation>
     */
    private function loadPimcoreTranslations(): array
    {
        $this->output->writeln('');
        $this->writelnVerbose('Loading Pimcore translations from database');

        $databaseTranslations = (new Translation\Listing())->load();

        $this->writelnVerbose(
            sprintf('Found %s Pimcore translation keys in database', \count($databaseTranslations)),
        );

        return $databaseTranslations;
    }

    /**
     * @param array<string, array<string, string>> $translationsFromFiles
     * @param array<Translation>                   $currentPimcoreTranslations
     *
     * @return array<string, array<string, string>>
     */
    private function filterTranslationsFromFiles(
        array $translationsFromFiles,
        array $currentPimcoreTranslations,
    ): array {
        foreach ($currentPimcoreTranslations as $databaseTranslation) {
            if ($this->wasModifiedAfterMigration($databaseTranslation)) {
                // Do not overwrite modified values
                $key = $databaseTranslation->getKey();
                unset($translationsFromFiles[$key]);
            }
        }

        return $translationsFromFiles;
    }

    private function wasModifiedAfterMigration(Translation $databaseTranslation): bool
    {
        return $databaseTranslation->getCreationDate() !== $databaseTranslation->getModificationDate();
    }

    /**
     * @param array<string, array<string, string>> $translationsGroupedByKey
     */
    private function createPimcoreTranslations(array $translationsGroupedByKey): void
    {
        foreach ($translationsGroupedByKey as $translationKey => $translations) {
            $newTranslation = new Translation();
            $newTranslation->setKey($translationKey);
            $newTranslation->setTranslations($translations);
            $newTranslation->save();
        }
    }
}
