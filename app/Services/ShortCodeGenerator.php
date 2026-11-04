<?php

namespace App\Services;

class ShortCodeGenerator
{
    private const ALPHABET = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    public function generate(int $length = 8): string
    {
        $code = '';
        $lastIndex = strlen(self::ALPHABET) - 1;

        for ($index = 0; $index < $length; $index++) {
            $code .= self::ALPHABET[random_int(0, $lastIndex)];
        }

        return $code;
    }
}
