# Module 05: AI Extraction, Evidence & Anti-Hallucination Guardrails

## 1. Paradigm: AI as Structured Extractor, Not Authority

The AI layer is strictly an **information extraction and summarization instrument**. It is **never the authoritative source of truth**. 

To prevent hallucinations from leaking into public records:
1. Every critical extracted fact must be **verbatim-grounded** in raw artifact text or OCR output.
2. AI extraction outputs must conform to a **strict JSON Schema** before being cast into Laravel DTOs.
3. Ungrounded or low-confidence facts automatically trigger human moderation.

---

## 2. Extraction JSON Schema Contract

The AI extraction service must output structured data conforming to this JSON Schema:

```json
{
  "$schema": "https://json-schema.org/draft/2020-12/schema",
  "title": "RecruitmentNoticeExtraction",
  "type": "object",
  "required": [
    "title",
    "notice_type",
    "is_corrigendum",
    "positions",
    "application_details",
    "evidence",
    "overall_confidence"
  ],
  "properties": {
    "title": { "type": "string" },
    "reference_number": { "type": ["string", "null"] },
    "notice_type": {
      "type": "string",
      "enum": ["recruitment", "corrigendum", "extension", "exam_date", "result", "admit_card"]
    },
    "is_corrigendum": { "type": "boolean" },
    "corrigendum_parent_ref": { "type": ["string", "null"] },
    "dates": {
      "type": "object",
      "properties": {
        "published_at": { "type": ["string", "null"], "format": "date" },
        "application_start_at": { "type": ["string", "null"], "format": "date-time" },
        "application_end_at": { "type": ["string", "null"], "format": "date-time" },
        "fee_payment_end_at": { "type": ["string", "null"], "format": "date-time" },
        "correction_window_end_at": { "type": ["string", "null"], "format": "date-time" },
        "exam_date_text": { "type": ["string", "null"] }
      }
    },
    "positions": {
      "type": "array",
      "items": {
        "type": "object",
        "required": ["title", "total_vacancies"],
        "properties": {
          "title": { "type": "string" },
          "post_code": { "type": ["string", "null"] },
          "total_vacancies": { "type": "integer", "minimum": 0 },
          "employment_type": { "type": "string", "enum": ["permanent", "contractual", "deputation", "apprentice"] },
          "pay_level": { "type": ["string", "null"] },
          "pay_scale_text": { "type": ["string", "null"] },
          "reservations": {
            "type": "array",
            "items": {
              "type": "object",
              "required": ["category", "vacancies"],
              "properties": {
                "category": { "type": "string", "enum": ["UR", "OBC_NCL", "SC", "ST", "EWS", "PWBD", "EX_SERVICEMEN", "WOMEN"] },
                "quota_type": { "type": "string", "enum": ["vertical", "horizontal"] },
                "vacancies": { "type": "integer", "minimum": 0 }
              }
            }
          }
        }
      }
    },
    "eligibility": {
      "type": "object",
      "properties": {
        "minimum_age": { "type": ["integer", "null"] },
        "maximum_age": { "type": ["integer", "null"] },
        "age_as_on": { "type": ["string", "null"], "format": "date" },
        "age_relaxation": { "type": "object" },
        "qualification_summary": { "type": "string" },
        "raw_text": { "type": "string" }
      }
    },
    "application_details": {
      "type": "object",
      "required": ["application_mode"],
      "properties": {
        "application_mode": { "type": "string", "enum": ["online", "offline_postal", "walk_in", "email"] },
        "apply_url": { "type": ["string", "null"], "format": "uri" },
        "general_fee": { "type": ["integer", "null"] },
        "is_exempted_for_female": { "type": "boolean" },
        "is_exempted_for_sc_st": { "type": "boolean" },
        "is_exempted_for_pwbd": { "type": "boolean" }
      }
    },
    "evidence": {
      "type": "array",
      "items": {
        "type": "object",
        "required": ["field_name", "extracted_value", "verbatim_text_fragment", "page_number", "confidence"],
        "properties": {
          "field_name": { "type": "string" },
          "extracted_value": { "type": "string" },
          "verbatim_text_fragment": { "type": "string" },
          "page_number": { "type": "integer" },
          "confidence": { "type": "number", "minimum": 0, "maximum": 1 },
          "bounding_box": { "type": ["object", "null"] }
        }
      }
    },
    "overall_confidence": { "type": "number", "minimum": 0, "maximum": 1 }
  }
}
```

---

## 3. Strict Verbatim Grounding Rule

For any critical recruitment attribute (`application_end_at`, `total_vacancies`, `reference_number`, `pay_level`, `minimum_age`), the extraction engine **MUST** provide an exact character snippet from the document.

### 3.1 Grounding Verification Algorithm
Before persisting the extracted candidate notice into the database, Laravel executes `ValidateExtractionGroundingAction`:

```php
// In app/Actions/Extraction/ValidateExtractionGroundingAction.php
public function execute(ExtractedRecruitmentDTO $dto, string $documentCleanText): GroundingResult
{
    $unfoundedFields = [];

    foreach ($dto->evidence as $evidenceItem) {
        // Strict substring check in clean document text
        $fragment = trim($evidenceItem->verbatimTextFragment);
        
        if (!str_contains($documentCleanText, $fragment)) {
            $unfoundedFields[] = [
                'field' => $evidenceItem->fieldName,
                'claimed_value' => $evidenceItem->extractedValue,
                'missing_snippet' => $fragment,
            ];
        }
    }

    if (!empty($unfoundedFields)) {
        return GroundingResult::failed(
            reason: 'Ungrounded assertions detected in extraction payload.',
            unfoundedFields: $unfoundedFields
        );
    }

    return GroundingResult::passed();
}
```

### 3.2 Anti-Hallucination Policy
- If any required field lacks verbatim grounding, the notice is tagged with:
  ```json
  "metadata": {
    "hallucination_warning": true,
    "grounding_failures": ["application_end_at", "total_vacancies"]
  }
  ```
- **Action:** Auto-publication is permanently locked. The notice enters `status = 'pending_review'` with a high-priority warning flag in the moderation queue.

---

## 4. Multi-Dimensional Confidence Scoring Formula

Rather than relying on a single opaque confidence probability from an LLM, the system computes a deterministic weighted score:

$$\text{Confidence}_{\text{final}} = (w_1 \cdot C_{\text{source}}) + (w_2 \cdot C_{\text{grounding}}) + (w_3 \cdot C_{\text{ocr}}) + (w_4 \cdot C_{\text{schema}})$$

| Dimension | Weight | Description |
|---|---|---|
| **$C_{\text{source}}$** (Source Trust) | 0.30 | 1.0 for `.gov.in`/`.nic.in`; 0.85 for `.ac.in`; 0.40 for unverified sources. |
| **$C_{\text{grounding}}$** (Verbatim Grounding) | 0.35 | Proportion of critical fields that matched exact verbatim document snippets. |
| **$C_{\text{ocr}}$** (OCR / Text Clarity) | 0.20 | Mean character recognition confidence from PaddleOCR / native PDF text extraction. |
| **$C_{\text{schema}}$** (Schema Completeness) | 0.15 | Presence of all mandatory fields (Dates, Vacancies, Eligibility, Reference No). |

### Publication Routing Thresholds
- $\ge 0.94$: **Eligible for Auto-Publication** (provided source is `OFFICIAL_VERIFIED` and no duplicate conflict exists).
- $0.75 - 0.93$: **Standard Moderation Queue** (requires one-click moderator approval).
- $< 0.75$: **High-Risk Review Queue** (requires manual verification of document and field correction).
