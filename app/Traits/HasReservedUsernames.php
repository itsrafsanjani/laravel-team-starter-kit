<?php

namespace App\Traits;

use App\Models\Team;
use Illuminate\Support\Str;

trait HasReservedUsernames
{
    /**
     * Reserved usernames that cannot be used as team slugs
     */
    private array $reservedUsernames = [
        'admin',
        'administrator',
        'root',
        'system',
        'api',
        'www',
        'mail',
        'ftp',
        'support',
        'help',
        'info',
        'contact',
        'about',
        'blog',
        'news',
        'shop',
        'store',
        'app',
        'dashboard',
        'panel',
        'control',
        'manage',
        'settings',
        'config',
        'test',
        'demo',
        'staging',
        'dev',
        'development',
        'prod',
        'production',
        'beta',
        'alpha',
        'preview',
        'backup',
        'temp',
        'tmp',
        'cache',
        'logs',
        'files',
        'assets',
        'static',
        'public',
        'private',
        'secure',
        'auth',
        'login',
        'logout',
        'register',
        'signup',
        'signin',
        'password',
        'reset',
        'forgot',
        'verify',
        'confirm',
        'activate',
        'deactivate',
        'delete',
        'remove',
        'create',
        'update',
        'edit',
        'save',
        'cancel',
        'submit',
        'send',
        'receive',
        'get',
        'post',
        'put',
        'patch',
        'delete',
        'head',
        'options',
        'trace',
        'connect',
    ];

    /**
     * Generate a unique slug that avoids reserved usernames
     */
    protected function generateUniqueSlug(string $name, ?string $excludeTeamId = null): string
    {
        $baseSlug = Str::slug($name);

        if (empty($baseSlug)) {
            $baseSlug = 'team';
        }

        // Check if the base slug is reserved
        if (in_array($baseSlug, $this->reservedUsernames)) {
            $baseSlug = $baseSlug.'-team';
        }

        $slug = $baseSlug;
        $counter = 1;

        // Ensure slug is unique and not reserved
        while (Team::where('slug', $slug)->where('id', '!=', $excludeTeamId)->exists() || in_array($slug, $this->reservedUsernames)) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if a slug is reserved
     */
    protected function isReservedUsername(string $slug): bool
    {
        return in_array($slug, $this->reservedUsernames);
    }
}
