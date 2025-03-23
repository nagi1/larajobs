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
            [
                'name' => 'years_experience',
                'type' => AttributeType::NUMBER,
                'options' => null,
            ],
            [
                'name' => 'education_level',
                'type' => AttributeType::SELECT,
                'options' => ['Associate', 'Bachelor', 'Master', 'PhD'],
            ],
            [
                'name' => 'required_skills',
                'type' => AttributeType::SELECT,
                'options' => ['Basic', 'Intermediate', 'Advanced', 'Expert', 'Master'],
            ],
            [
                'name' => 'team_size',
                'type' => AttributeType::NUMBER,
                'options' => null,
            ],
            [
                'name' => 'travel_required',
                'type' => AttributeType::BOOLEAN,
                'options' => null,
            ],
            [
                'name' => 'on_call_required',
                'type' => AttributeType::BOOLEAN,
                'options' => null,
            ],
            [
                'name' => 'start_date',
                'type' => AttributeType::SELECT,
                'options' => ['Immediate', 'Within 2 weeks', 'Within 1 month', 'Within 2 months', 'Within 3 months'],
            ],
            [
                'name' => 'work_schedule',
                'type' => AttributeType::SELECT,
                'options' => ['Regular', 'Flexible', 'Core Hours'],
            ],
            [
                'name' => 'benefits',
                'type' => AttributeType::SELECT,
                'options' => [
                    'Health Insurance',
                    '401k',
                    'Paid Time Off',
                    'Stock Options',
                    'Annual Bonus',
                    'Relocation Assistance',
                    'Executive Benefits',
                ],
            ],
            [
                'name' => 'interview_process',
                'type' => AttributeType::SELECT,
                'options' => [
                    'Technical Screening',
                    'Technical Interview',
                    'Team Interview',
                    'System Design',
                    'Leadership Interview',
                    'Executive Interview',
                    'Culture Fit',
                ],
            ],
            [
                'name' => 'equity_offered',
                'type' => AttributeType::BOOLEAN,
                'options' => null,
            ],
            [
                'name' => 'bonus_percentage',
                'type' => AttributeType::NUMBER,
                'options' => null,
            ],
            [
                'name' => 'vacation_days',
                'type' => AttributeType::NUMBER,
                'options' => null,
            ],
            [
                'name' => 'health_insurance_type',
                'type' => AttributeType::SELECT,
                'options' => ['Basic', 'Premium', 'Family', 'None'],
            ],
            [
                'name' => 'remote_work_policy',
                'type' => AttributeType::SELECT,
                'options' => ['Fully Remote', 'Hybrid', 'On-site', 'Flexible'],
            ],
            [
                'name' => 'company_culture',
                'type' => AttributeType::TEXT,
                'options' => null,
            ],
            [
                'name' => 'growth_opportunities',
                'type' => AttributeType::TEXT,
                'options' => null,
            ],
            [
                'name' => 'project_scope',
                'type' => AttributeType::TEXT,
                'options' => null,
            ],
            [
                'name' => 'tech_stack',
                'type' => AttributeType::TEXT,
                'options' => null,
            ],
            [
                'name' => 'contract_duration',
                'type' => AttributeType::SELECT,
                'options' => ['3 months', '6 months', '12 months', '24 months', '36 months', 'Indefinite'],
            ],
            [
                'name' => 'contract_to_hire',
                'type' => AttributeType::BOOLEAN,
                'options' => null,
            ],
            [
                'name' => 'visa_sponsorship',
                'type' => AttributeType::BOOLEAN,
                'options' => null,
            ],
            [
                'name' => 'relocation_package',
                'type' => AttributeType::SELECT,
                'options' => ['None', 'Partial', 'Full', 'Custom'],
            ],
            [
                'name' => 'hours_per_week',
                'type' => AttributeType::NUMBER,
                'options' => null,
            ],
            [
                'name' => 'payment_terms',
                'type' => AttributeType::SELECT,
                'options' => ['Hourly', 'Fixed Price', 'Milestone-based'],
            ],
        ];

        foreach ($attributes as $attribute) {
            Attribute::create($attribute);
        }
    }
}
