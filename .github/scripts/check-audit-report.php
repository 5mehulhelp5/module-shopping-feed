<?php
declare(strict_types=1);

// Strict sign-off for a `composer audit --locked --format=json --abandoned=report` report.
// Any advisory, ignored advisory, filter-list match, or unreachable repository fails.
try {
    $label = $argv[2] ?? 'dependency audit';
    $report = json_decode((string) file_get_contents($argv[1] ?? ''), true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($report) || !isset($report['advisories']) || !is_array($report['advisories'])) {
        throw new RuntimeException('Invalid audit report; refusing dependency sign-off.');
    }

    $findings = [];
    foreach ($report['advisories'] as $package => $advisories) {
        if (!is_array($advisories) || $advisories === []) {
            $findings[] = sprintf('malformed advisory group for %s', $package);
            continue;
        }
        foreach ($advisories as $advisory) {
            $findings[] = sprintf('advisory %s for %s', $advisory['advisoryId'] ?? 'unknown', $package);
        }
    }
    foreach (['ignored-advisories', 'malware', 'filter', 'unreachable-repositories'] as $key) {
        if (!empty($report[$key])) {
            $findings[] = sprintf('non-empty "%s" section', $key);
        }
    }
    if ($findings !== []) {
        throw new RuntimeException(sprintf("%s failed:\n- %s", $label, implode("\n- ", $findings)));
    }

    $abandoned = is_array($report['abandoned'] ?? null) ? array_keys($report['abandoned']) : [];
    printf("%s: no security advisories, ignored advisories, or filter-list matches.\n", $label);
    printf("Abandoned packages reported for review (%d): %s\n", count($abandoned), implode(', ', $abandoned) ?: 'none');
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}
