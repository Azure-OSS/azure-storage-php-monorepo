<?php

declare(strict_types=1);

if ($argc !== 3) {
    fwrite(STDERR, "Usage: php tools/release/extract-changelog-section.php <changelog-path> <version>\n");
    exit(1);
}

$changelogPath = $argv[1];
$version = $argv[2];

if (! is_file($changelogPath)) {
    fwrite(STDERR, "Changelog file does not exist: {$changelogPath}\n");
    exit(1);
}

$contents = file_get_contents($changelogPath);

if ($contents === false) {
    fwrite(STDERR, "Unable to read changelog file: {$changelogPath}\n");
    exit(1);
}

$escapedVersion = preg_quote($version, '/');
$sectionHeadingPattern = '/^##\s+(?:\['.$escapedVersion.'\]|'.$escapedVersion.')(?:\s*-\s*.+)?\s*$/';
$nextHeadingPattern = '/^##\s+.+$/';

$lines = preg_split('/\R/', $contents);
$capturing = false;
$sectionFound = false;
$buffer = [];

foreach ($lines as $line) {
    $trimmedLine = trim($line);

    if (! $capturing && preg_match($sectionHeadingPattern, $trimmedLine) === 1) {
        $capturing = true;
        $sectionFound = true;

        continue;
    }

    if ($capturing && preg_match($nextHeadingPattern, $trimmedLine) === 1) {
        break;
    }

    if ($capturing) {
        $buffer[] = $line;
    }
}

if (! $sectionFound) {
    fwrite(STDERR, "Version section not found in {$changelogPath}: {$version}\n");
    exit(1);
}

$sectionBody = trim(implode(PHP_EOL, $buffer));

if ($sectionBody === '') {
    fwrite(STDERR, "Version section is empty in {$changelogPath}: {$version}\n");
    exit(1);
}

fwrite(STDOUT, $sectionBody.PHP_EOL);
