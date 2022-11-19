<?php

namespace Neusta\Pimcore\TranslationMigrationBundle;

final class TranslationCollection
{
    /** @var array<string, array<string, string>>  [id => [locale => translation[]]] */
    private array $translations;

    public function add(string $locale, string $id, string $translation): void
    {
        $this->translations[$id][$locale] = $translation;
    }
}
