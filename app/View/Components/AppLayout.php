<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * @param  'narrow'|'medium'|'wide'  $width  Max width of the page header and content, so both always line up.
     */
    public function __construct(public string $width = 'wide') {}

    /**
     * The Tailwind max-width class for the chosen page width.
     */
    public function maxWidthClass(): string
    {
        return match ($this->width) {
            'narrow' => 'max-w-3xl',
            'medium' => 'max-w-5xl',
            default => 'max-w-7xl',
        };
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}
