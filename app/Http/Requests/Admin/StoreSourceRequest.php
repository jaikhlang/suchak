<?php

namespace App\Http\Requests\Admin;

use App\Enums\CrawlMethod;
use App\Enums\SourceType;
use App\Enums\TrustLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSourceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('manage-taxonomy') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'institution_id' => ['required', 'string', 'exists:institutions,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(SourceType::class)],
            'url' => ['required', 'url', 'max:2000'],
            'canonical_url' => ['nullable', 'url', 'max:2000'],
            'crawl_method' => ['required', Rule::enum(CrawlMethod::class)],
            'crawl_frequency_minutes' => ['required', 'integer', 'min:15', 'max:10080'],
            'status' => ['required', 'string', 'in:active,paused,failing,disabled'],
            'trust_level' => ['required', Rule::enum(TrustLevel::class)],
            'configuration' => ['nullable', 'array'],
        ];
    }
}
