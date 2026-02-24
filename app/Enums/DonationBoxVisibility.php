<?php

namespace App\Enums;

enum DonationBoxVisibility: string
{
    case Public = 'public';
    case Unlisted = 'unlisted';
    case Private = 'private';
}
