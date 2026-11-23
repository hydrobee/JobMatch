<?php

namespace App\Enums;

enum ApplicationStage: string
{
    case Saved = 'saved';
    case Applied = 'applied';
    case Interview = 'interview';
    case Offer = 'offer';
    case Rejected = 'rejected';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /** Stages where a follow-up reminder still makes sense. */
    public function isActive(): bool
    {
        return in_array($this, [self::Saved, self::Applied, self::Interview], true);
    }
}
