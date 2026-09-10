<?php

namespace App\Actions\Ingestion;

class RecordArtifactHashAction
{
    /**
     * Compute SHA-256 hash of raw binary content.
     */
    public function computeHash(string $binaryContent): string
    {
        return hash('sha256', $binaryContent);
    }

    /**
     * Normalize official reference number according to Module 03 rules.
     * e.g. "Advt. No. 04/2026/Rectt." -> "04-2026-RECTT"
     */
    public function normalizeReferenceNumber(?string $rawRef): ?string
    {
        if (blank($rawRef)) {
            return null;
        }

        // Remove prefixes like "Advt. No.", "Advertisement No:", "Notice No."
        $cleaned = preg_replace('/^(advt\.?\s*no\.?|advertisement\s*no\.?|notice\s*no\.?)\s*[:\-]?\s*/i', '', trim($rawRef));

        // Normalize separators (slashes, spaces, underscores) to hyphens
        $normalized = preg_replace('/[\/\s_]+/', '-', $cleaned);

        // Strip non-alphanumeric except hyphens
        $normalized = preg_replace('/[^a-zA-Z0-9\-]/', '', $normalized);

        return strtoupper(trim($normalized, '-'));
    }
}
