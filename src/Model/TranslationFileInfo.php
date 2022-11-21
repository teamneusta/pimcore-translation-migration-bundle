<?php declare(strict_types=1);

namespace Neusta\Pimcore\TranslationMigrationBundle\Model;

final class TranslationFileInfo implements \Stringable
{
    public function __construct(
        private \SplFileInfo $file,
        private string $format,
        private string $locale,
        private string $domain,
    ) {
    }

    public static function fromSplFileInfo(\SplFileInfo $fileInfo): self
    {
        $fileNameParts = explode('.', $fileInfo->getBasename());

        if (count($fileNameParts) < 3) {
            throw new \InvalidArgumentException('Unsupported file name. The scheme must be as follows: "domain.locale.format"');
        }

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

    public function __toString(): string
    {
        return $this->file->__toString();
    }
}
