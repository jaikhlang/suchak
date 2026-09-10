<?php

namespace App\Actions\Extraction;

use App\Models\Notice;

class DeduplicateNoticeAction
{
    /**
     * @return array{
     *     is_duplicate: bool,
     *     matched_notice_id: string|null,
     *     reason: string|null,
     *     parent_notice_id: string|null
     * }
     */
    public function execute(
        string $institutionId,
        ?string $referenceNumber,
        string $canonicalUrl,
        bool $isCorrigendum = false,
        ?string $corrigendumParentRef = null
    ): array {
        // 1. Check canonical URL duplication
        $byUrl = Notice::where('canonical_source_url', $canonicalUrl)->first();
        if ($byUrl) {
            return [
                'is_duplicate' => true,
                'matched_notice_id' => $byUrl->id,
                'reason' => 'canonical_url_match',
                'parent_notice_id' => null,
            ];
        }

        // 2. Corrigendum parent search
        $parentNoticeId = null;
        if ($isCorrigendum && $corrigendumParentRef) {
            $parent = $this->findParentNotice($institutionId, $corrigendumParentRef);
            if ($parent) {
                $parentNoticeId = $parent->id;
            }
        }

        // 3. Normalized Reference Number matching
        if ($referenceNumber) {
            $normalizedRef = $this->normalizeReferenceNumber($referenceNumber);

            $candidates = Notice::where('institution_id', $institutionId)
                ->whereNotNull('reference_number')
                ->get();

            foreach ($candidates as $candidate) {
                if ($this->normalizeReferenceNumber($candidate->reference_number) === $normalizedRef) {
                    if ($isCorrigendum) {
                        $parentNoticeId = $candidate->id;
                        break;
                    }

                    return [
                        'is_duplicate' => true,
                        'matched_notice_id' => $candidate->id,
                        'reason' => 'reference_number_match',
                        'parent_notice_id' => null,
                    ];
                }
            }
        }

        return [
            'is_duplicate' => false,
            'matched_notice_id' => null,
            'reason' => null,
            'parent_notice_id' => $parentNoticeId,
        ];
    }

    public function normalizeReferenceNumber(?string $ref): string
    {
        if (! $ref) {
            return '';
        }

        $cleaned = strtolower(trim($ref));
        $cleaned = preg_replace('/^(advt|advertisement|notification|notice|circular|ref|en)\.?\s*(no\.?)?\s*[:\-\/]?\s*/i', '', $cleaned) ?? $cleaned;

        return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $cleaned) ?? '');
    }

    protected function findParentNotice(string $institutionId, string $parentRef): ?Notice
    {
        $normalizedParent = $this->normalizeReferenceNumber($parentRef);

        $notices = Notice::where('institution_id', $institutionId)
            ->whereNotNull('reference_number')
            ->get();

        foreach ($notices as $notice) {
            if ($this->normalizeReferenceNumber($notice->reference_number) === $normalizedParent) {
                return $notice;
            }
        }

        return null;
    }
}
