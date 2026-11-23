<?php

return [

    /*
    | How skills are pulled out of a job description.
    |   keyword - dictionary matching below (free, works offline)
    |   ai      - Gemini API, falls back to keyword matching on any failure
    */
    'extractor' => env('JOBMATCH_EXTRACTOR', 'keyword'),

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('JOBMATCH_AI_MODEL', 'gemini-3.5-flash-lite'),
        // Used for resume tailoring. Point it at a stronger model if you want better writing.
        'resume_model' => env('JOBMATCH_RESUME_MODEL', 'gemini-3.5-flash-lite'),
    ],

    /*
    | Canonical skill name => aliases that count as the same skill.
    | Add your own; matching is case-insensitive and whole-word.
    */
    'skill_dictionary' => [
        'PHP' => ['php'],
        'Laravel' => ['laravel'],
        'Livewire' => ['livewire'],
        'JavaScript' => ['javascript', 'ecmascript'],
        'TypeScript' => ['typescript'],
        'Angular' => ['angular', 'angularjs'],
        'React' => ['react', 'reactjs', 'react.js'],
        'Vue' => ['vue', 'vuejs', 'vue.js'],
        'Node.js' => ['node.js', 'nodejs', 'node js'],
        'jQuery' => ['jquery'],
        'HTML' => ['html', 'html5'],
        'CSS' => ['css', 'css3'],
        'Tailwind CSS' => ['tailwind', 'tailwindcss', 'tailwind css'],
        'Bootstrap' => ['bootstrap'],
        'Python' => ['python'],
        'Django' => ['django'],
        'Flask' => ['flask'],
        'FastAPI' => ['fastapi'],
        'Java' => ['java'],
        'Spring Boot' => ['spring boot', 'springboot', 'spring'],
        'Kotlin' => ['kotlin'],
        'Swift' => ['swift'],
        'Flutter' => ['flutter'],
        'Dart' => ['dart'],
        'C#' => ['c#', 'c sharp'],
        'C++' => ['c++'],
        '.NET' => ['.net', 'dotnet', 'dot net'],
        'ASP.NET' => ['asp.net', 'aspnet'],
        'Go' => ['golang'],
        'Ruby' => ['ruby'],
        'Ruby on Rails' => ['ruby on rails', 'rails'],
        'SQL' => ['sql'],
        'MySQL' => ['mysql'],
        'PostgreSQL' => ['postgresql', 'postgres'],
        'SQL Server' => ['sql server', 'mssql'],
        'Oracle' => ['oracle'],
        'MongoDB' => ['mongodb', 'mongo'],
        'Redis' => ['redis'],
        'REST API' => ['rest api', 'rest apis', 'restful', 'restful api'],
        'GraphQL' => ['graphql'],
        'Git' => ['git'],
        'GitHub Actions' => ['github actions'],
        'CI/CD' => ['ci/cd', 'cicd', 'continuous integration'],
        'Docker' => ['docker'],
        'Kubernetes' => ['kubernetes', 'k8s'],
        'Linux' => ['linux'],
        'AWS' => ['aws', 'amazon web services'],
        'Azure' => ['azure'],
        'Google Cloud' => ['gcp', 'google cloud'],
        'PHPUnit' => ['phpunit'],
        'Selenium' => ['selenium'],
        'Jest' => ['jest'],
        'Agile' => ['agile'],
        'Scrum' => ['scrum'],
        'Jira' => ['jira'],
        'Figma' => ['figma'],
        'Excel' => ['excel', 'microsoft excel'],
        'Power BI' => ['power bi', 'powerbi'],
        'Tableau' => ['tableau'],
        'IT Support' => ['it support', 'helpdesk', 'help desk', 'technical support'],
        'Networking' => ['networking', 'tcp/ip'],
    ],
];
