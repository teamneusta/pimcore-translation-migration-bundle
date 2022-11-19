<?php

namespace Neusta\Pimcore\TranslationMigrationBundle;

use Traversable;

final class TranslationCollection implements \IteratorAggregate
{
    /** @var array<string, array<string, string>>  [id => [locale => translation[]]] */
    private array $translations;

    public function add(string $locale, string $id, string $translation): void
    {
        $this->translations[$id][$locale] = $translation;
    }

    public function without(string ...$ids): self
    {
        $new = new self();
        $new->translations = array_filter($this->translations, static fn (string $id) => !in_array($id, $ids, true));

        return $new;
    }

    public function getIterator(): \Generator
    {
        yield from $this->translations;
    }
}
