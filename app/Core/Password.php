<?php

declare(strict_types=1);

namespace App\Core;

final class Password
{
    public static function hash(string $plainText): string
    {
        return password_hash($plainText, PASSWORD_DEFAULT);
    }

    public static function verify(string $plainText, string $hashedValue): bool
    {
        return password_verify($plainText, $hashedValue);
    }
}
