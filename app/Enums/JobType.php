<?php

namespace App\Enums;

enum JobType: string
{
    case FULL_TIME = 'full-time';
    case PART_TIME = 'part-time';
    case CONTRACT = 'contract';
    case FREELANCE = 'freelance';
}
