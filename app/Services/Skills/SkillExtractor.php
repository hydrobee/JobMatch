<?php

namespace App\Services\Skills;

interface SkillExtractor
{
    /**
     * Pull skill names out of free text (a job title and description).
     *
     * @return array<int, string> canonical skill names, no duplicates
     */
    public function extract(string $text): array;
}
