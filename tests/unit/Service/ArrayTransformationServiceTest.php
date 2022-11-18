<?php declare(strict_types=1);

namespace Neusta\Pimcore\TranslationMigrationBundle\Tests\Unit\Service;

use Neusta\Pimcore\TranslationMigrationBundle\Service\ArrayTransformationService;
use PHPUnit\Framework\TestCase;

class ArrayTransformationServiceTest extends TestCase
{
    private ArrayTransformationService $arrayTransformationService;

    protected function setUp(): void
    {
        $this->arrayTransformationService = new ArrayTransformationService();
    }

    /** @test */
    public function one_yaml_as_input_must_return_an_array_with_the_content_of_that_yaml(): void
    {
        $inputArray = [
            'en' => [
                'translation.key' => 'Translated key',
            ],
        ];
        $expectedResult = [
            'translation.key' => [
                'en' => 'Translated key',
            ],
        ];

        $result = $this->arrayTransformationService->groupByTranslationKey($inputArray);

        self::assertSame($expectedResult, $result);
    }

    /** @test */
    public function merge_content_of_multiple_yamls_by_the_translation_keys(): void
    {
        $inputArray = [
            'en' => [
                'translation.key' => 'Translated key',
            ],
            'de' => [
                'translation.key' => 'Übersetzter Schlüssel',
            ],
            'es' => [
                'translation.key' => 'Clave traducida',
            ],
        ];
        $expectedResult = [
            'translation.key' => [
                'en' => 'Translated key',
                'de' => 'Übersetzter Schlüssel',
                'es' => 'Clave traducida',
            ],
        ];

        $result = $this->arrayTransformationService->groupByTranslationKey($inputArray);

        self::assertSame($expectedResult, $result);
    }

    /** @test */
    public function merge_only_the_present_keys(): void
    {
        $inputArray = [
            'en' => [
                'translation.key' => 'Translated key',
                'translation.other_key' => 'Other translated key',
            ],
            'de' => [
                'translation.key' => 'Übersetzter Schlüssel',
            ],
        ];
        $expectedResult = [
            'translation.key' => [
                'en' => 'Translated key',
                'de' => 'Übersetzter Schlüssel',
            ],
            'translation.other_key' => [
                'en' => 'Other translated key',
            ],
        ];

        $result = $this->arrayTransformationService->groupByTranslationKey($inputArray);

        self::assertSame($expectedResult, $result);
    }

    /** @test */
    public function merge_different_key_combinations(): void
    {
        $inputArray = [
            'en' => [
                'translation.key' => 'Translated key',
                'translation.other_key' => 'Other translated key',
            ],
            'de' => [
                'translation.key' => 'Übersetzter Schlüssel',
                'translation.another_key' => 'Anderer übersetzter Schlüssel',
            ],
            'es' => [
                'translation.some_key' => 'Clave traducida',
            ],
        ];
        $expectedResult = [
            'translation.key' => [
                'en' => 'Translated key',
                'de' => 'Übersetzter Schlüssel',
            ],
            'translation.other_key' => [
                'en' => 'Other translated key',
            ],
            'translation.another_key' => [
                'de' => 'Anderer übersetzter Schlüssel',
            ],
            'translation.some_key' => [
                'es' => 'Clave traducida',
            ],
        ];

        $result = $this->arrayTransformationService->groupByTranslationKey($inputArray);

        self::assertSame($expectedResult, $result);
    }
}
