<?php declare(strict_types=1);

namespace Neusta\Pimcore\TranslationMigrationBundle\Model;

use Symfony\Component\Translation\MessageCatalogue;

/**
 * @template-implements \IteratorAggregate<string, array<string, string>>
 */
final class TranslationCollection implements \IteratorAggregate, \Countable
{
    /** @var array<string, array<string, string>> */
    private array $translations = [];

    public function __construct(
        private string $domain,
    ) {
    }

    public function withCatalogue(MessageCatalogue $catalogue): self
    {
        $locale = $catalogue->getLocale();
        $clone = clone $this;

        foreach ($catalogue->all($this->domain) as $id => $translation) {
            $clone->translations[$id][$locale] = $translation;
        }

        return $clone;
    }

    public function withoutIds(string ...$ids): self
    {
        $clone = clone $this;

        $clone->translations = array_filter(
            $this->translations,
            static fn (string $id): bool => !in_array($id, $ids, true),
            ARRAY_FILTER_USE_KEY,
        );

        return $clone;
    }

    public function getIterator(): \Generator
    {
        yield from $this->translations;
    }

    public function count(): int
    {
        return count($this->translations);
    }
}
