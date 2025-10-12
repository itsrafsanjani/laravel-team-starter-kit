<?php

namespace App\Facades;

use App\Context\TeamContext as TeamContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;

/**
 * @method static TeamContextService resolve(Request $request)
 * @method static \App\Models\Team|null getCurrentTeam()
 * @method static array|null getCurrentTeamData()
 * @method static array getUserTeams()
 * @method static array getPermissions()
 * @method static bool hasPermission(string $permission)
 * @method static bool isTeamOwner()
 * @method static string|null getTeamRole()
 * @method static array|null getUserData()
 * @method static array getInertiaData()
 * @method static TeamContextService reset()
 */
class TeamContext extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'team.context';
    }
}
