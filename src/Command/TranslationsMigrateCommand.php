<?php declare(strict_types=1);

namespace Neusta\Pimcore\TranslationMigrationBundle\Command;

use Neusta\Pimcore\TranslationMigrationBundle\Event\FileCannotBeLoaded;
use Neusta\Pimcore\TranslationMigrationBundle\Event\FileWasLoaded;
use Neusta\Pimcore\TranslationMigrationBundle\Source\SourceProvider;
use Neusta\Pimcore\TranslationMigrationBundle\Target\TargetRepository;
use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[AsCommand(
    name: 'neusta:translations:migrate',
    description: 'Creates Pimcore translations for every Symfony translation file.',
)]
final class TranslationsMigrateCommand extends AbstractCommand
{
    private const DOMAIN = 'messages';
    private const PROJECT_ROOT = PIMCORE_PROJECT_ROOT . '/';

    public function __construct(
        private SourceProvider $sourceProvider,
        private TargetRepository $targetRepository,
        private EventDispatcherInterface $eventDispatcher,
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
            EOF
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $isVerbose = OutputInterface::VERBOSITY_VERBOSE === $this->io->getVerbosity();

        $this->io->comment('Start migrating translations to Pimcore translations');

        if ($isVerbose) {
            $output->writeln('Reading from directories:');
            $this->io->listing(array_map(
                fn (string $path): string => $this->stripProjectPrefix($path),
                $this->sourceProvider->getDirectories(),
            ));

            $ioTableRows = [];
            $this->eventDispatcher->addListener(
                FileWasLoaded::class,
                function (FileWasLoaded $event) use (&$ioTableRows): void {
                    $ioTableRows[] = [
                        $this->stripProjectPrefix($event->file->file()->getRealPath()),
                        'PARSED',
                    ];
                },
            );
            $this->eventDispatcher->addListener(
                FileCannotBeLoaded::class,
                function (FileCannotBeLoaded $event) use (&$ioTableRows): void {
                    $ioTableRows[] = [
                        $this->stripProjectPrefix($event->file->file()->getRealPath()),
                        sprintf('IGNORED: %s', $event->exception->getMessage()),
                    ];
                },
            );
        }

        $translations = $this->sourceProvider->getTranslations(self::DOMAIN);

        if ($isVerbose) {
            $this->io->table(['Realpath', 'Result'], $ioTableRows);
            $output->writeln(sprintf('Found %s translation keys in translation files', \count($translations)));
            $output->writeln('');
            $output->writeln('Loading Pimcore translations from database');
            $output->writeln(sprintf('Found %s Pimcore translation keys in database', $this->targetRepository->count()));
        }

        $translationsToUpdate = $translations->withoutIds(...$this->targetRepository->getModifiedIds());
        $this->targetRepository->save($translationsToUpdate);

        $this->io->info(sprintf('%s translation keys were added to Pimcore.', \count($translationsToUpdate)));
        $this->io->success('Pimcore translations updated successfully');

        return Command::SUCCESS;
    }

    private function stripProjectPrefix(string $string): string
    {
        return str_starts_with($string, self::PROJECT_ROOT)
            ? substr($string, \strlen(self::PROJECT_ROOT))
            : $string;
    }
}
