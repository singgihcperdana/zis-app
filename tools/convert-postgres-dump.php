<?php

declare(strict_types=1);

$tableDefinitions = [
    'zakat_quality' => [
        'columns' => [
            'id',
            'name',
            'zakat_type',
            'active',
            'berat_per_jiwa_kg',
            'nominal_per_jiwa',
        ],
        'source_order' => [
            'id',
            'active',
            'berat_per_jiwa_kg',
            'name',
            'nominal_per_jiwa',
            'zakat_type',
        ],
    ],
    'zakat_payment' => [
        'columns' => [
            'id',
            'jumlah_jiwa',
            'alamat',
            'payer_name',
            'payer_phone',
            'received_by_name',
            'payment_method',
            'berat_beras_kg',
            'jumlah_uang',
            'jumlah_uang_zakat_mal',
            'jumlah_uang_infaq_sedekah',
            'jumlah_uang_fidiah',
            'payment_at',
            'canceled',
            'canceled_at',
            'cancel_reason',
            'canceled_by',
            'receipt_number',
            'receipt_year',
            'receipt_sequence',
            'zakat_quality_id',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
        ],
        'source_order' => [
            'id',
            'alamat',
            'berat_beras_kg',
            'cancel_reason',
            'canceled',
            'canceled_at',
            'canceled_by',
            'jumlah_jiwa',
            'jumlah_uang',
            'jumlah_uang_fidiah',
            'jumlah_uang_infaq_sedekah',
            'jumlah_uang_zakat_mal',
            'payer_name',
            'payer_phone',
            'payment_at',
            'payment_method',
            'receipt_number',
            'receipt_sequence',
            'receipt_year',
            'zakat_quality_id',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
            'received_by_name',
        ],
    ],
    'users' => [
        'columns' => [
            'id',
            'username',
            'email',
            'password',
            'role',
            'active',
        ],
    ],
    'institution_profile' => [
        'columns' => [
            'id',
            'nama_instansi',
            'kota_kabupaten',
            'alamat_lengkap',
            'nomor_telepon',
            'email',
            'nama_ketua',
            'nama_bendahara',
        ],
    ],
    'muzakki_person' => [
        'columns' => [
            'id',
            'nama',
            'payment_id',
            'sequence_no',
        ],
    ],
    'receipt_sequence' => [
        'columns' => [
            'receipt_year',
            'version',
            'last_issued',
        ],
    ],
];

$inputPath = $argv[1] ?? null;
$outputPath = $argv[2] ?? null;

if (!is_string($inputPath) || $inputPath === '') {
    fwrite(STDERR, "Usage: php tools/convert-postgres-dump.php input.sql [output.sql]\n");
    exit(1);
}

if (!is_file($inputPath)) {
    fwrite(STDERR, "Input file not found: {$inputPath}\n");
    exit(1);
}

$contents = file_get_contents($inputPath);

if ($contents === false) {
    fwrite(STDERR, "Failed to read input file: {$inputPath}\n");
    exit(1);
}

$converted = convertDump($contents, $tableDefinitions);

if (is_string($outputPath) && $outputPath !== '') {
    if (file_put_contents($outputPath, $converted) === false) {
        fwrite(STDERR, "Failed to write output file: {$outputPath}\n");
        exit(1);
    }

    fwrite(STDOUT, "Converted dump written to {$outputPath}\n");
    exit(0);
}

fwrite(STDOUT, $converted);

function convertDump(string $contents, array $tableDefinitions): string
{
    $lines = preg_split("/\\r\\n|\\n|\\r/", $contents);
    $output = [];

    foreach ($lines as $line) {
        $trimmed = trim($line);

        if ($trimmed === '') {
            $output[] = '';
            continue;
        }

        $convertedLine = convertInsertLine($trimmed, $tableDefinitions);
        $output[] = $convertedLine ?? $trimmed;
    }

    return implode(PHP_EOL, $output) . PHP_EOL;
}

function convertInsertLine(string $line, array $tableDefinitions): ?string
{
    $pattern = '/^INSERT\s+INTO\s+(?:(?:"?[\w]+"?)\.)?"?([\w]+)"?\s+VALUES\s*\((.*)\);\s*$/i';

    if (preg_match($pattern, $line, $matches) !== 1) {
        return null;
    }

    $table = strtolower($matches[1]);
    $rawValues = $matches[2];

    if (!isset($tableDefinitions[$table])) {
        return preg_replace('/^INSERT\s+INTO\s+(?:(?:"?[\w]+"?)\.)?"?([\w]+)"?/i', 'INSERT INTO $1', $line);
    }

    $values = splitSqlValues($rawValues);
    $normalizedValues = array_map('normalizeValue', $values);
    $definition = $tableDefinitions[$table];
    $columns = $definition['columns'];
    $sourceOrder = $definition['source_order'] ?? $columns;

    if (count($normalizedValues) !== count($sourceOrder)) {
        fwrite(STDERR, sprintf(
            "Warning: skipped %s because value count %d does not match expected column count %d.\n",
            $table,
            count($normalizedValues),
            count($sourceOrder)
        ));

        return $line;
    }

    if ($sourceOrder !== $columns) {
        $normalizedValues = reorderValues($normalizedValues, $sourceOrder, $columns);
    }

    return sprintf(
        'INSERT INTO %s (%s) VALUES (%s);',
        $table,
        implode(', ', $columns),
        implode(', ', $normalizedValues)
    );
}

function reorderValues(array $values, array $sourceOrder, array $targetOrder): array
{
    $mapped = [];

    foreach ($sourceOrder as $index => $columnName) {
        $mapped[$columnName] = $values[$index] ?? 'NULL';
    }

    $reordered = [];
    foreach ($targetOrder as $columnName) {
        $reordered[] = $mapped[$columnName] ?? 'NULL';
    }

    return $reordered;
}

function splitSqlValues(string $rawValues): array
{
    $values = [];
    $buffer = '';
    $inString = false;
    $length = strlen($rawValues);

    for ($i = 0; $i < $length; $i++) {
        $char = $rawValues[$i];

        if ($char === "'") {
            $buffer .= $char;

            if ($inString && $i + 1 < $length && $rawValues[$i + 1] === "'") {
                $buffer .= "'";
                $i++;
                continue;
            }

            $inString = !$inString;
            continue;
        }

        if ($char === ',' && !$inString) {
            $values[] = trim($buffer);
            $buffer = '';
            continue;
        }

        $buffer .= $char;
    }

    if ($buffer !== '') {
        $values[] = trim($buffer);
    }

    return $values;
}

function normalizeValue(string $value): string
{
    if (strcasecmp($value, 'true') === 0) {
        return '1';
    }

    if (strcasecmp($value, 'false') === 0) {
        return '0';
    }

    if (preg_match("/^'(\\d{4}-\\d{2}-\\d{2} \\d{2}:\\d{2}:\\d{2})(?:\\.\\d+)?(?:[+-]\\d{2}(?::?\\d{2})?)'$/", $value, $matches) === 1) {
        return "'" . $matches[1] . "'";
    }

    return $value;
}
