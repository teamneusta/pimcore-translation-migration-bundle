<?php declare(strict_types=1);

namespace Neusta\Pimcore\TranslationMigrationBundle;

final class TranslationFileInfo
{
    private function __construct(
        private \SplFileInfo $file,
        private string $format,
        private string $locale,
        private string $domain,
    ) {
    }

    public static function fromSplFileInfo(\SplFileInfo $fileInfo): self
    {
        // filename is domain.locale.format
        $fileNameParts = explode('.', $fileInfo->getBasename());
        $format = array_pop($fileNameParts);
        $locale = array_pop($fileNameParts);
        $domain = implode('.', $fileNameParts);

        return new self($fileInfo, $format, $locale, $domain);
    }

    public function file(): \SplFileInfo
    {
        return $this->file;
    }

    public function format(): string
    {
        return $this->format;
    }

    public function locale(): string
    {
        return $this->locale;
    }

    public function domain(): string
    {
        return $this->domain;
    }
}
