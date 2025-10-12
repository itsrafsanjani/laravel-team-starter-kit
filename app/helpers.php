<?php

use App\Services\TeamResolver;

if (! function_exists('team')) {
    function team(): ?\App\Models\Team
    {
        return app(TeamResolver::class)->get();
    }
}
