<?php

namespace App\Enums;

/** Where the user sent their application. */
enum ApplicationChannel: string
{
    case Email = 'email';
    case CompanyWebsite = 'company_website';
    case JobStreet = 'jobstreet';
    case Indeed = 'indeed';
    case MyFutureJobs = 'myfuturejobs';
    case LinkedIn = 'linkedin';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Email => 'Email',
            self::CompanyWebsite => 'Company website',
            self::JobStreet => 'JobStreet',
            self::Indeed => 'Indeed',
            self::MyFutureJobs => 'MYFutureJobs',
            self::LinkedIn => 'LinkedIn',
            self::Other => 'Other',
        };
    }
}
