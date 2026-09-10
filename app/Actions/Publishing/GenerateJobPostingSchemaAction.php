<?php

namespace App\Actions\Publishing;

use App\Models\Notice;

class GenerateJobPostingSchemaAction
{
    /**
     * @return array<string, mixed>
     */
    public function execute(Notice $notice): array
    {
        $notice->loadMissing(['institution.state', 'positions', 'applicationDetail']);

        $institution = $notice->institution;
        $instName = $institution->name;
        $websiteUrl = $institution->website_url;
        $stateName = $institution->state?->name ?? 'All India';

        $firstPosition = $notice->positions->first();
        $salaryMin = $firstPosition?->salary_min;
        $salaryMax = $firstPosition?->salary_max;

        $employmentType = match ($firstPosition?->employment_type->value ?? 'permanent') {
            'contractual' => 'CONTRACTOR',
            'apprentice' => 'INTERN',
            default => 'FULL_TIME',
        };

        $schema = [
            '@context' => 'https://schema.org/',
            '@type' => 'JobPosting',
            'title' => $notice->title,
            'description' => $notice->summary ?: "<p>Recruitment notification for {$notice->title} by {$instName}. Total vacancies: {$notice->total_vacancies}.</p>",
            'datePosted' => ($notice->published_at ?? $notice->first_seen_at)->toIso8601String(),
            'employmentType' => $employmentType,
            'hiringOrganization' => [
                '@type' => 'GovernmentOrganization',
                'name' => $instName,
                'sameAs' => $websiteUrl,
            ],
            'jobLocation' => [
                '@type' => 'Place',
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressCountry' => 'IN',
                    'addressRegion' => $stateName,
                ],
            ],
            'totalJobOpenings' => $notice->total_vacancies,
        ];

        if ($notice->application_end_at) {
            $schema['validThrough'] = $notice->application_end_at->toIso8601String();
        }

        if ($salaryMin && $salaryMax) {
            $schema['baseSalary'] = [
                '@type' => 'MonetaryAmount',
                'currency' => 'INR',
                'value' => [
                    '@type' => 'QuantitativeValue',
                    'minValue' => $salaryMin,
                    'maxValue' => $salaryMax,
                    'unitText' => 'MONTH',
                ],
            ];
        }

        return $schema;
    }
}
