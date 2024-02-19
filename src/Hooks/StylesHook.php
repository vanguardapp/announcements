<?php

namespace Vanguard\Announcements\Hooks;

use Illuminate\Contracts\View\View;
use Vanguard\Plugins\Contracts\Hook;

class StylesHook implements Hook
{
    /**
     * Execute the hook action.
     */
    public function handle(): View
    {
        return view('announcements::partials.styles');
    }
}
