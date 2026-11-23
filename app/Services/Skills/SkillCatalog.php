<?php

namespace App\Services\Skills;

/**
 * Maps whatever a person (or an AI) calls a skill onto the canonical name
 * from config/jobmatch.php, so "nodejs" and "Node.js" count as one skill.
 */
class SkillCatalog
{
    public static function canonical(string $name): string
    {
        $name = trim($name);
        $lower = mb_strtolower($name);

        foreach (config('jobmatch.skill_dictionary') as $canonical => $aliases) {
            if (mb_strtolower($canonical) === $lower || in_array($lower, $aliases, true)) {
                return $canonical;
            }
        }

        return $name;
    }
}
