<?php

declare(strict_types=1);

if ($argc < 2) {
    fwrite(STDERR, "Usage: php tools/hash-password.php \"password\"\n");
    exit(1);
}

$password = (string) $argv[1];

echo password_hash($password, PASSWORD_DEFAULT) . PHP_EOL;
