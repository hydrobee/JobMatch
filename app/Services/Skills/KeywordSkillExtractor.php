<?php

namespace App\Services\Skills;

class KeywordSkillExtractor implements SkillExtractor
{
    public function extract(string $text): array
    {
        $found = [];

        foreach (config('jobmatch.skill_dictionary') as $canonical => $aliases) {
            foreach ($aliases as $alias) {
                // Whole-word match; the extra characters stop "c" matching inside "c++"/"c#".
                $pattern = '/(?<![A-Za-z0-9+#])'.preg_quote($alias, '/').'(?![A-Za-z0-9+#])/i';

                if (preg_match($pattern, $text) === 1) {
                    $found[] = $canonical;
                    break;
                }
            }
        }

        return $found;
    }
}
