<?php

namespace App\Enums;

enum DemographicType: string
{
    case Age = 'age';
    case Gender = 'gender';
    case City = 'city';
    case Country = 'country';
}
