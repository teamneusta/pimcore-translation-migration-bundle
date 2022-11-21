<?php declare(strict_types=1);

namespace Neusta\Pimcore\TranslationMigrationBundle\Command;

use Neusta\Pimcore\TranslationMigrationBundle\Target\TargetRepository;
use Neusta\Pimcore\TranslationMigrationBundle\Source\SourceProvider;
use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        $this->io->comment('Start migrating translations to Pimcore translations');

        if (OutputInterface::VERBOSITY_VERBOSE === $this->io->getVerbosity()) {
            $output->writeln('Reading from directories:');
            $this->io->listing(\array_map(
                fn (string $path): string => $this->stripProjectPrefix($path),
                $this->sourceProvider->getDirectories(),
            ));
        }

        $collection = $this->sourceProvider->getTranslations(self::DOMAIN);

        if (OutputInterface::VERBOSITY_VERBOSE === $this->io->getVerbosity()) {
            $output->writeln(sprintf('Found %s translation keys in translation files', \count($collection)));
            $output->writeln('');
            $output->writeln(sprintf('Found %s Pimcore translation keys in database', $this->targetRepository->count()));
        }

        $collection = $collection->withoutIds(...$this->targetRepository->getModifiedIds());
        $this->targetRepository->save($collection);

        $this->io->info(sprintf('%s translation keys were added to Pimcore.', \count($collection)));
        $this->io->success('Pimcore translations updated successfully');

        return Command::SUCCESS;
    }

    private function stripProjectPrefix(string $string): string
    {
        return str_starts_with($string, self::PROJECT_ROOT)
            ? substr($string, strlen(self::PROJECT_ROOT))
            : $string;
    }
}
