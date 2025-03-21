<?php

namespace Database\Seeders;

use App\Enums\AttributeType;
use App\Models\Attribute;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            // Experience related
            [
                'name' => 'Years of Experience',
                'type' => AttributeType::NUMBER,
                'options' => null,
            ],
            [
                'name' => 'Experience Level',
                'type' => AttributeType::SELECT,
                'options' => ['Entry Level', 'Mid Level', 'Senior Level', 'Lead', 'Manager', 'Director'],
            ],
            [
                'name' => 'Required Experience',
                'type' => AttributeType::TEXT,
                'options' => null,
            ],

            // Education related
            [
                'name' => 'Education Level',
                'type' => AttributeType::SELECT,
                'options' => ['High School', 'Associate', 'Bachelor', 'Master', 'PhD'],
            ],
            [
                'name' => 'Required Education',
                'type' => AttributeType::TEXT,
                'options' => null,
            ],

            // Work related
            [
                'name' => 'Work Schedule',
                'type' => AttributeType::SELECT,
                'options' => ['Flexible', 'Fixed', 'Rotating', 'On-call'],
            ],
            [
                'name' => 'Travel Required',
                'type' => AttributeType::BOOLEAN,
                'options' => null,
            ],
            [
                'name' => 'Travel Percentage',
                'type' => AttributeType::NUMBER,
                'options' => null,
            ],

            // Benefits related
            [
                'name' => 'Health Insurance',
                'type' => AttributeType::BOOLEAN,
                'options' => null,
            ],
            [
                'name' => 'Dental Insurance',
                'type' => AttributeType::BOOLEAN,
                'options' => null,
            ],
            [
                'name' => 'Vision Insurance',
                'type' => AttributeType::BOOLEAN,
                'options' => null,
            ],
            [
                'name' => '401k',
                'type' => AttributeType::BOOLEAN,
                'options' => null,
            ],
            [
                'name' => '401k Match',
                'type' => AttributeType::NUMBER,
                'options' => null,
            ],
            [
                'name' => 'Paid Time Off',
                'type' => AttributeType::NUMBER,
                'options' => null,
            ],
            [
                'name' => 'Parental Leave',
                'type' => AttributeType::BOOLEAN,
                'options' => null,
            ],
            [
                'name' => 'Parental Leave Duration',
                'type' => AttributeType::NUMBER,
                'options' => null,
            ],
            [
                'name' => 'Stock Options',
                'type' => AttributeType::BOOLEAN,
                'options' => null,
            ],
            [
                'name' => 'Stock Options Vesting Period',
                'type' => AttributeType::NUMBER,
                'options' => null,
            ],

            // Project related
            [
                'name' => 'Project Duration',
                'type' => AttributeType::NUMBER,
                'options' => null,
            ],
            [
                'name' => 'Project Start Date',
                'type' => AttributeType::DATE,
                'options' => null,
            ],
            [
                'name' => 'Project End Date',
                'type' => AttributeType::DATE,
                'options' => null,
            ],
            [
                'name' => 'Project Description',
                'type' => AttributeType::TEXT,
                'options' => null,
            ],
            [
                'name' => 'Project Scope',
                'type' => AttributeType::TEXT,
                'options' => null,
            ],

            // Team related
            [
                'name' => 'Team Size',
                'type' => AttributeType::NUMBER,
                'options' => null,
            ],
            [
                'name' => 'Reporting Structure',
                'type' => AttributeType::TEXT,
                'options' => null,
            ],
            [
                'name' => 'Direct Reports',
                'type' => AttributeType::NUMBER,
                'options' => null,
            ],
        ];

        foreach ($attributes as $attribute) {
            Attribute::create($attribute);
        }
    }
}
