<?php

namespace App\Services\Resume;

use App\Services\Skills\KeywordSkillExtractor;

class ResumeAnalyzer
{
    public function __construct(
        private AtsChecker $ats,
        private KeywordSkillExtractor $extractor,
    ) {
    }

    /**
     * @param  array<int, string>|null  $jobSkills   skills the job asks for, or null for a general check
     * @param  array<int, string>  $userSkills       names from the user's own skills list
     * @return array{ats: array, keywords: array|null}
     */
    public function analyse(string $resume, ?array $jobSkills, array $userSkills): array
    {
        return [
            'ats' => $this->ats->check($resume),
            'keywords' => $jobSkills === null ? null : $this->keywords($resume, $jobSkills, $userSkills),
        ];
    }

    /**
     * Which of the job's skills the resume already mentions, which are safe to add
     * (you own them but the resume leaves them out), and which are real gaps.
     *
     * @param  array<int, string>  $jobSkills
     * @param  array<int, string>  $userSkills
     * @return array{found: array<int, string>, add_if_true: array<int, string>, gaps: array<int, string>}
     */
    private function keywords(string $resume, array $jobSkills, array $userSkills): array
    {
        $inResume = array_map('mb_strtolower', $this->extractor->extract($resume));
        $owned = array_map('mb_strtolower', $userSkills);

        $found = [];
        $addIfTrue = [];
        $gaps = [];

        foreach ($jobSkills as $skill) {
            $key = mb_strtolower($skill);

            if (in_array($key, $inResume, true)) {
                $found[] = $skill;
            } elseif (in_array($key, $owned, true)) {
                $addIfTrue[] = $skill;
            } else {
                $gaps[] = $skill;
            }
        }

        return ['found' => $found, 'add_if_true' => $addIfTrue, 'gaps' => $gaps];
    }
}
