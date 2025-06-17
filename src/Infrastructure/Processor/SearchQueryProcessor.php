<?php

declare(strict_types=1);

namespace App\Infrastructure\Processor;

class SearchQueryProcessor
{
    /**
     * @return string[]
     */
    public static function process(string $query): array
    {
        $normalized = self::normalize($query);

        if (!preg_match_all('/[a-z0-9]+/i', $normalized, $matches)) {
            return [];
        }

        return array_shift($matches);
    }

    private static function normalize(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');

        if (function_exists('iconv')) {
            $result = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
            if (false !== $result) {
                return $result;
            }
        }

        return strtr($text, [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'ñ' => 'n',
        ]);
    }
}
