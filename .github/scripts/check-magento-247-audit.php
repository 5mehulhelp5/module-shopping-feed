<?php
declare(strict_types=1);

try {
    $report = json_decode((string) file_get_contents($argv[1] ?? ''), true, 512, JSON_THROW_ON_ERROR);
    if (!isset($report['advisories']) || !is_array($report['advisories'])
        || !empty($report['ignored-advisories']) || !empty($report['malware'])
    ) {
        throw new RuntimeException('Invalid audit report or hidden advisories; refusing compatibility sign-off.');
    }
    $seen = false;
    foreach ($report['advisories'] as $package => $advisories) {
        if (!is_array($advisories)) {
            throw new RuntimeException('Malformed advisory group in the audit report.');
        }
        foreach ($advisories as $advisory) {
            if ($package !== 'league/flysystem' || ($advisory['advisoryId'] ?? '') !== 'PKSA-w9tt-7782-78jx') {
                throw new RuntimeException('An additional security advisory blocks the compatibility job.');
            }
            $seen = true;
        }
    }
    if (!$seen) {
        throw new RuntimeException('The expected advisory is absent. Review and remove the exception if resolved.');
    }
    echo "Known upstream advisory remains visible: league/flysystem PKSA-w9tt-7782-78jx.\n";
    echo "No additional advisories found. Compatibility testing is not platform security approval.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}
