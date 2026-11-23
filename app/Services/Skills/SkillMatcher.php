<?php

namespace App\Services\Skills;

class SkillMatcher
{
    /**
     * Compare the skills a job asks for with the skills the user has.
     *
     * @param  array<int, string>  $jobSkills
     * @param  array<int, string>  $userSkills
     * @return array{score: int|null, matched: array<int, string>, missing: array<int, string>}
     */
    public function match(array $jobSkills, array $userSkills): array
    {
        $have = array_map(fn (string $s) => mb_strtolower($s), $userSkills);

        $matched = [];
        $missing = [];

        foreach ($jobSkills as $skill) {
            if (in_array(mb_strtolower($skill), $have, true)) {
                $matched[] = $skill;
            } else {
                $missing[] = $skill;
            }
        }

        // A job with no recognised skills has no meaningful score.
        $score = $jobSkills === []
            ? null
            : (int) round(100 * count($matched) / count($jobSkills));

        return ['score' => $score, 'matched' => $matched, 'missing' => $missing];
    }
}
