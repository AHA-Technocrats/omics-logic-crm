<?php

namespace AHATechnocrats\OmicsLogic\Services;

class OrganizationNormalizer
{
    protected static array $abbreviations = [
        'univ' => 'university',
        'university' => 'university',
        'inst' => 'institute',
        'institute' => 'institute',
        'coll' => 'college',
        'college' => 'college',
        'sch' => 'school',
        'school' => 'school',
    ];

    public function normalize(string $name): string
    {
        $normalized = mb_strtolower(trim($name));
        $normalized = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $normalized) ?? $normalized;
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;
        $normalized = preg_replace('/\bthe\b/', '', $normalized) ?? $normalized;
        $normalized = trim($normalized);

        $words = explode(' ', $normalized);
        $words = array_map(function ($word) {
            return self::$abbreviations[$word] ?? $word;
        }, $words);

        return trim(implode(' ', array_filter($words)));
    }

    public function similarity(string $a, string $b): float
    {
        $na = $this->normalize($a);
        $nb = $this->normalize($b);

        if ($na === $nb) {
            return 1.0;
        }

        if ($na === '' || $nb === '') {
            return 0.0;
        }

        similar_text($na, $nb, $percent);

        return round($percent / 100, 2);
    }
}
