<?php

namespace App\Enums;

enum AttributeValueEnum: string
{
    // Remote work policy options
    case REMOTE_FULLY = 'Fully Remote';
    case REMOTE_HYBRID = 'Hybrid';
    case REMOTE_ONSITE = 'On-site';
    case REMOTE_FLEXIBLE = 'Flexible';

    // Required skills levels
    case SKILL_BASIC = 'Basic';
    case SKILL_INTERMEDIATE = 'Intermediate';
    case SKILL_ADVANCED = 'Advanced';
    case SKILL_EXPERT = 'Expert';
    case SKILL_MASTER = 'Master';

    // Work schedule options
    case SCHEDULE_REGULAR = 'Regular';
    case SCHEDULE_FLEXIBLE = 'Flexible';
    case SCHEDULE_CORE_HOURS = 'Core Hours';

    // Health insurance types
    case INSURANCE_BASIC = 'Basic';
    case INSURANCE_PREMIUM = 'Premium';
    case INSURANCE_FAMILY = 'Family';
    case INSURANCE_NONE = 'None';

    // Payment terms for freelance
    case PAYMENT_HOURLY = 'Hourly';
    case PAYMENT_FIXED = 'Fixed Price';
    case PAYMENT_MILESTONE = 'Milestone-based';

    // Contract durations
    case CONTRACT_3_MONTHS = '3 months';
    case CONTRACT_6_MONTHS = '6 months';
    case CONTRACT_12_MONTHS = '12 months';
    case CONTRACT_24_MONTHS = '24 months';
    case CONTRACT_36_MONTHS = '36 months';
    case CONTRACT_INDEFINITE = 'Indefinite';
}
