<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Processor;

use App\Application\Processor\SearchQueryProcessor;
use ReflectionClass;

describe('SearchQueryProcessor', function () {
    describe('process', function () {
        it('processes simple query with single word', function () {
            // Given
            $query = 'test';

            // When
            $result = SearchQueryProcessor::process($query);

            // Then
            expect($result)
                ->toBeArray()
                ->toHaveCount(1)
                ->and($result[0])->toBe('test');
        });

        it('processes query with multiple words', function () {
            // Given
            $query = 'hello world game';

            // When
            $result = SearchQueryProcessor::process($query);

            // Then
            expect($result)
                ->toBeArray()
                ->toHaveCount(3)
                ->and($result[0])->toBe('hello')
                ->and($result[1])->toBe('world')
                ->and($result[2])->toBe('game');
        });

        it('processes query with mixed case', function () {
            // Given
            $query = 'Hello WORLD Game';

            // When
            $result = SearchQueryProcessor::process($query);

            // Then
            expect($result)
                ->toBeArray()
                ->toHaveCount(3)
                ->and($result[0])->toBe('hello')
                ->and($result[1])->toBe('world')
                ->and($result[2])->toBe('game');
        });

        it('processes query with numbers', function () {
            // Given
            $query = 'game 2024 version 1';

            // When
            $result = SearchQueryProcessor::process($query);

            // Then
            expect($result)
                ->toBeArray()
                ->toHaveCount(4)
                ->and($result[0])->toBe('game')
                ->and($result[1])->toBe('2024')
                ->and($result[2])->toBe('version')
                ->and($result[3])->toBe('1');
        });

        it('processes query with alphanumeric combinations', function () {
            // Given
            $query = 'game2k24 test123 abc456def';

            // When
            $result = SearchQueryProcessor::process($query);

            // Then
            expect($result)
                ->toBeArray()
                ->toHaveCount(3)
                ->and($result[0])->toBe('game2k24')
                ->and($result[1])->toBe('test123')
                ->and($result[2])->toBe('abc456def');
        });

        it('removes special characters and punctuation', function () {
            // Given
            $query = 'hello, world! game? test: example;';

            // When
            $result = SearchQueryProcessor::process($query);

            // Then
            expect($result)
                ->toBeArray()
                ->toHaveCount(5)
                ->and($result[0])->toBe('hello')
                ->and($result[1])->toBe('world')
                ->and($result[2])->toBe('game')
                ->and($result[3])->toBe('test')
                ->and($result[4])->toBe('example');
        });

        it('handles query with multiple spaces and tabs', function () {
            // Given
            $query = "  hello   world\t\tgame  ";

            // When
            $result = SearchQueryProcessor::process($query);

            // Then
            expect($result)
                ->toBeArray()
                ->toHaveCount(3)
                ->and($result[0])->toBe('hello')
                ->and($result[1])->toBe('world')
                ->and($result[2])->toBe('game');
        });

        it('normalizes accented characters', function () {
            // Given
            $query = 'café naïve résumé';

            // When
            $result = SearchQueryProcessor::process($query);

            // Then
            expect($result)
                ->toBeArray()
                ->toHaveCount(3)
                ->and($result[0])->toBe('cafe')
                ->and($result[1])->toBe('naive')
                ->and($result[2])->toBe('resume');
        });

        it('normalizes various accented characters', function () {
            // Given
            $query = 'àáâãä èéêë ìíîï òóôõö ùúûü ç ñ';

            // When
            $result = SearchQueryProcessor::process($query);

            // Then
            expect($result)
                ->toBeArray()
                ->toHaveCount(7)
                ->and($result[0])->toBe('aaaaa')
                ->and($result[1])->toBe('eeee')
                ->and($result[2])->toBe('iiii')
                ->and($result[3])->toBe('ooooo')
                ->and($result[4])->toBe('uuuu')
                ->and($result[5])->toBe('c')
                ->and($result[6])->toBe('n');
        });

        it('returns empty array for empty string', function () {
            // Given
            $query = '';

            // When
            $result = SearchQueryProcessor::process($query);

            // Then
            expect($result)
                ->toBeArray()
                ->toBeEmpty();
        });

        it('returns empty array for whitespace-only string', function () {
            // Given
            $query = '   ';

            // When
            $result = SearchQueryProcessor::process($query);

            // Then
            expect($result)
                ->toBeArray()
                ->toBeEmpty();
        });

        it('returns empty array for special characters only', function () {
            // Given
            $query = '!@#$%^&*()_+-=[]{}|;:,.<>?';

            // When
            $result = SearchQueryProcessor::process($query);

            // Then
            expect($result)
                ->toBeArray()
                ->toBeEmpty();
        });

        it('handles unicode characters properly', function () {
            // Given
            $query = 'test 日本語 example';

            // When
            $result = SearchQueryProcessor::process($query);

            // Then
            expect($result)
                ->toBeArray()
                ->toHaveCount(2)
                ->and($result[0])->toBe('test')
                ->and($result[1])->toBe('example');
        });

        it('processes complex mixed content', function () {
            // Given
            $query = 'Super Mario Bros. 3 (1990) - Nintendo Entertainment System!';

            // When
            $result = SearchQueryProcessor::process($query);

            // Then
            expect($result)
                ->toBeArray()
                ->toHaveCount(8)
                ->and($result[0])->toBe('super')
                ->and($result[1])->toBe('mario')
                ->and($result[2])->toBe('bros')
                ->and($result[3])->toBe('3')
                ->and($result[4])->toBe('1990')
                ->and($result[5])->toBe('nintendo')
                ->and($result[6])->toBe('entertainment')
                ->and($result[7])->toBe('system');
        });

        it('handles very long queries', function () {
            // Given
            $words = array_fill(0, 100, 'word');
            $query = implode(' ', $words);

            // When
            $result = SearchQueryProcessor::process($query);

            // Then
            expect($result)
                ->toBeArray()
                ->toHaveCount(100)
                ->and(array_unique($result))->toEqual(['word']);
        });

        it('preserves single character words', function () {
            // Given
            $query = 'a b c 1 2 3';

            // When
            $result = SearchQueryProcessor::process($query);

            // Then
            expect($result)
                ->toBeArray()
                ->toHaveCount(6)
                ->and($result[0])->toBe('a')
                ->and($result[1])->toBe('b')
                ->and($result[2])->toBe('c')
                ->and($result[3])->toBe('1')
                ->and($result[4])->toBe('2')
                ->and($result[5])->toBe('3');
        });

        it('handles hyphenated words by splitting them', function () {
            // Given
            $query = 'action-adventure role-playing';

            // When
            $result = SearchQueryProcessor::process($query);

            // Then
            expect($result)
                ->toBeArray()
                ->toHaveCount(4)
                ->and($result[0])->toBe('action')
                ->and($result[1])->toBe('adventure')
                ->and($result[2])->toBe('role')
                ->and($result[3])->toBe('playing');
        });

        it('handles underscores by splitting them', function () {
            // Given
            $query = 'game_title test_example';

            // When
            $result = SearchQueryProcessor::process($query);

            // Then
            expect($result)
                ->toBeArray()
                ->toHaveCount(4)
                ->and($result[0])->toBe('game')
                ->and($result[1])->toBe('title')
                ->and($result[2])->toBe('test')
                ->and($result[3])->toBe('example');
        });
    });

    describe('normalize (private method testing via reflection)', function () {
        it('normalizes text to lowercase', function () {
            // Given
            $processor = new ReflectionClass(SearchQueryProcessor::class);
            $normalizeMethod = $processor->getMethod('normalize');
            $normalizeMethod->setAccessible(true);

            // When
            $result = $normalizeMethod->invoke(null, 'HELLO WORLD');

            // Then
            expect($result)->toBe('hello world');
        });

        it('handles empty string', function () {
            // Given
            $processor = new ReflectionClass(SearchQueryProcessor::class);
            $normalizeMethod = $processor->getMethod('normalize');
            $normalizeMethod->setAccessible(true);

            // When
            $result = $normalizeMethod->invoke(null, '');

            // Then
            expect($result)->toBe('');
        });

        it('normalizes accented characters using fallback mapping', function () {
            // Given
            $processor = new ReflectionClass(SearchQueryProcessor::class);
            $normalizeMethod = $processor->getMethod('normalize');
            $normalizeMethod->setAccessible(true);

            // When
            $result = $normalizeMethod->invoke(null, 'Café Naïve');

            // Then
            expect($result)->toContain('cafe');
            expect($result)->toContain('naive');
        });

        it('preserves special characters during normalization', function () {
            // Given
            $processor = new ReflectionClass(SearchQueryProcessor::class);
            $normalizeMethod = $processor->getMethod('normalize');
            $normalizeMethod->setAccessible(true);

            // When
            $result = $normalizeMethod->invoke(null, 'Hello! World?');

            // Then
            expect($result)->toBe('hello! world?');
        });

        it('handles UTF-8 characters properly', function () {
            // Given
            $processor = new ReflectionClass(SearchQueryProcessor::class);
            $normalizeMethod = $processor->getMethod('normalize');
            $normalizeMethod->setAccessible(true);

            // When
            $result = $normalizeMethod->invoke(null, 'Test 测试');

            // Then
            expect($result)->toContain('test');
            // The Chinese characters might be handled differently based on iconv availability
        });
    });

    describe('edge cases and error handling', function () {
        it('handles null-like strings gracefully', function () {
            // Given
            $query = '0';

            // When
            $result = SearchQueryProcessor::process($query);

            // Then
            expect($result)
                ->toBeArray()
                ->toHaveCount(1)
                ->and($result[0])->toBe('0');
        });

        it('handles strings with only numbers', function () {
            // Given
            $query = '123 456 789';

            // When
            $result = SearchQueryProcessor::process($query);

            // Then
            expect($result)
                ->toBeArray()
                ->toHaveCount(3)
                ->and($result[0])->toBe('123')
                ->and($result[1])->toBe('456')
                ->and($result[2])->toBe('789');
        });

        it('handles malformed unicode gracefully', function () {
            // Given
            $query = "test\xc3\x28invalid";

            // When
            $result = SearchQueryProcessor::process($query);

            // Then
            expect($result)->toBeArray();
            expect($result[0])->toBe('test');
        });

        it('handles very long single words', function () {
            // Given
            $longWord = str_repeat('a', 1000);
            $query = $longWord;

            // When
            $result = SearchQueryProcessor::process($query);

            // Then
            expect($result)
                ->toBeArray()
                ->toHaveCount(1)
                ->and($result[0])->toBe($longWord);
        });
    });
});
