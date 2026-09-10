<?php

namespace Database\Seeders;

use App\Enums\InstitutionType;
use App\Models\Institution;
use App\Models\InstitutionAlias;
use App\Models\State;
use Illuminate\Database\Seeder;

class InstitutionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $delhi = State::where('iso_code', 'IN-DL')->first();
        $up = State::where('iso_code', 'IN-UP')->first();
        $bihar = State::where('iso_code', 'IN-BR')->first();
        $maharashtra = State::where('iso_code', 'IN-MH')->first();
        $tamilNadu = State::where('iso_code', 'IN-TN')->first();
        $rajasthan = State::where('iso_code', 'IN-RJ')->first();

        $institutions = [
            [
                'name' => 'Union Public Service Commission',
                'short_name' => 'UPSC',
                'slug' => 'upsc',
                'institution_type' => InstitutionType::CentralGov,
                'state_id' => null, // Central
                'website_url' => 'https://upsc.gov.in',
                'official_domain' => 'upsc.gov.in',
                'is_verified' => true,
                'aliases' => [
                    ['alias' => 'UPSC', 'locale' => 'en', 'is_primary' => true],
                    ['alias' => 'संघ लोक सेवा आयोग', 'locale' => 'hi', 'is_primary' => true],
                    ['alias' => 'Union Public Service Commission, New Delhi', 'locale' => 'en', 'is_primary' => false],
                ],
            ],
            [
                'name' => 'Staff Selection Commission',
                'short_name' => 'SSC',
                'slug' => 'ssc',
                'institution_type' => InstitutionType::CentralGov,
                'state_id' => null,
                'website_url' => 'https://ssc.gov.in',
                'official_domain' => 'ssc.gov.in',
                'is_verified' => true,
                'aliases' => [
                    ['alias' => 'SSC', 'locale' => 'en', 'is_primary' => true],
                    ['alias' => 'कर्मचारी चयन आयोग', 'locale' => 'hi', 'is_primary' => true],
                ],
            ],
            [
                'name' => 'Institute of Banking Personnel Selection',
                'short_name' => 'IBPS',
                'slug' => 'ibps',
                'institution_type' => InstitutionType::Banking,
                'state_id' => null,
                'website_url' => 'https://ibps.in',
                'official_domain' => 'ibps.in',
                'is_verified' => true,
                'aliases' => [
                    ['alias' => 'IBPS', 'locale' => 'en', 'is_primary' => true],
                    ['alias' => 'बैंकिंग कार्मिक चयन संस्थान', 'locale' => 'hi', 'is_primary' => true],
                ],
            ],
            [
                'name' => 'Railway Recruitment Control Board',
                'short_name' => 'RRB',
                'slug' => 'rrb',
                'institution_type' => InstitutionType::CentralGov,
                'state_id' => null,
                'website_url' => 'https://indianrailways.gov.in',
                'official_domain' => 'indianrailways.gov.in',
                'is_verified' => true,
                'aliases' => [
                    ['alias' => 'RRB', 'locale' => 'en', 'is_primary' => true],
                    ['alias' => 'रेलवे भर्ती नियंत्रण बोर्ड', 'locale' => 'hi', 'is_primary' => true],
                    ['alias' => 'Railway Recruitment Board', 'locale' => 'en', 'is_primary' => false],
                ],
            ],
            [
                'name' => 'National Testing Agency',
                'short_name' => 'NTA',
                'slug' => 'nta',
                'institution_type' => InstitutionType::Autonomous,
                'state_id' => null,
                'website_url' => 'https://nta.ac.in',
                'official_domain' => 'nta.ac.in',
                'is_verified' => true,
                'aliases' => [
                    ['alias' => 'NTA', 'locale' => 'en', 'is_primary' => true],
                    ['alias' => 'राष्ट्रीय परीक्षा एजेंसी', 'locale' => 'hi', 'is_primary' => true],
                ],
            ],
            [
                'name' => 'Uttar Pradesh Public Service Commission',
                'short_name' => 'UPPSC',
                'slug' => 'uppsc',
                'institution_type' => InstitutionType::StateGov,
                'state_id' => $up?->id,
                'website_url' => 'https://uppsc.up.nic.in',
                'official_domain' => 'uppsc.up.nic.in',
                'is_verified' => true,
                'aliases' => [
                    ['alias' => 'UPPSC', 'locale' => 'en', 'is_primary' => true],
                    ['alias' => 'उत्तर प्रदेश लोक सेवा आयोग', 'locale' => 'hi', 'is_primary' => true],
                ],
            ],
            [
                'name' => 'Bihar Public Service Commission',
                'short_name' => 'BPSC',
                'slug' => 'bpsc',
                'institution_type' => InstitutionType::StateGov,
                'state_id' => $bihar?->id,
                'website_url' => 'https://bpsc.bih.nic.in',
                'official_domain' => 'bpsc.bih.nic.in',
                'is_verified' => true,
                'aliases' => [
                    ['alias' => 'BPSC', 'locale' => 'en', 'is_primary' => true],
                    ['alias' => 'बिहार लोक सेवा आयोग', 'locale' => 'hi', 'is_primary' => true],
                ],
            ],
            [
                'name' => 'Maharashtra Public Service Commission',
                'short_name' => 'MPSC',
                'slug' => 'mpsc',
                'institution_type' => InstitutionType::StateGov,
                'state_id' => $maharashtra?->id,
                'website_url' => 'https://mpsc.gov.in',
                'official_domain' => 'mpsc.gov.in',
                'is_verified' => true,
                'aliases' => [
                    ['alias' => 'MPSC', 'locale' => 'en', 'is_primary' => true],
                    ['alias' => 'महाराष्ट्र लोकसेवा आयोग', 'locale' => 'mr', 'is_primary' => true],
                ],
            ],
            [
                'name' => 'Tamil Nadu Public Service Commission',
                'short_name' => 'TNPSC',
                'slug' => 'tnpsc',
                'institution_type' => InstitutionType::StateGov,
                'state_id' => $tamilNadu?->id,
                'website_url' => 'https://tnpsc.gov.in',
                'official_domain' => 'tnpsc.gov.in',
                'is_verified' => true,
                'aliases' => [
                    ['alias' => 'TNPSC', 'locale' => 'en', 'is_primary' => true],
                    ['alias' => 'தமிழ்நாடு அரசுப் பணியாளர் தேர்வாணையம்', 'locale' => 'ta', 'is_primary' => true],
                ],
            ],
            [
                'name' => 'Rajasthan Public Service Commission',
                'short_name' => 'RPSC',
                'slug' => 'rpsc',
                'institution_type' => InstitutionType::StateGov,
                'state_id' => $rajasthan?->id,
                'website_url' => 'https://rpsc.rajasthan.gov.in',
                'official_domain' => 'rpsc.rajasthan.gov.in',
                'is_verified' => true,
                'aliases' => [
                    ['alias' => 'RPSC', 'locale' => 'en', 'is_primary' => true],
                    ['alias' => 'राजस्थान लोक सेवा आयोग', 'locale' => 'hi', 'is_primary' => true],
                ],
            ],
            [
                'name' => 'Delhi Subordinate Services Selection Board',
                'short_name' => 'DSSSB',
                'slug' => 'dsssb',
                'institution_type' => InstitutionType::StateGov,
                'state_id' => $delhi?->id,
                'website_url' => 'https://dsssb.delhi.gov.in',
                'official_domain' => 'dsssb.delhi.gov.in',
                'is_verified' => true,
                'aliases' => [
                    ['alias' => 'DSSSB', 'locale' => 'en', 'is_primary' => true],
                    ['alias' => 'दिल्ली अधीनस्थ सेवा चयन बोर्ड', 'locale' => 'hi', 'is_primary' => true],
                ],
            ],
        ];

        foreach ($institutions as $data) {
            $aliases = $data['aliases'];
            unset($data['aliases']);

            $institution = Institution::firstOrCreate(
                ['slug' => $data['slug']],
                $data
            );

            foreach ($aliases as $alias) {
                InstitutionAlias::firstOrCreate(
                    [
                        'alias' => $alias['alias'],
                        'locale' => $alias['locale'],
                    ],
                    [
                        'institution_id' => $institution->id,
                        'is_primary' => $alias['is_primary'],
                    ]
                );
            }
        }
    }
}
