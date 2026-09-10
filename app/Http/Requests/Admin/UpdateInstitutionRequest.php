<?php

namespace App\Http\Requests\Admin;

use App\Enums\InstitutionType;
use App\Models\Institution;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInstitutionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->canManageTaxonomy() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Institution $institution */
        $institution = $this->route('institution');

        return [
            'name' => ['required', 'string', 'max:255'],
            'short_name' => ['nullable', 'string', 'max:50'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('institutions', 'slug')->ignore($institution->id)],
            'institution_type' => ['required', Rule::enum(InstitutionType::class)],
            'parent_id' => ['nullable', 'string', Rule::exists('institutions', 'id')],
            'state_id' => ['nullable', 'string', Rule::exists('states', 'id')],
            'website_url' => ['required', 'url', 'max:500'],
            'official_domain' => ['required', 'string', 'max:255'],
            'is_verified' => ['boolean'],
            'is_active' => ['boolean'],
            'aliases' => ['nullable', 'array'],
            'aliases.*.alias' => ['required', 'string', 'max:255'],
            'aliases.*.locale' => ['required', 'string', 'max:10'],
            'aliases.*.is_primary' => ['boolean'],
        ];
    }
}
