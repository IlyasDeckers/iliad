<?php
namespace Iliad\Concerns;

use Illuminate\Support\Facades\Auth;

trait ResolveId
{
    /**
     * Resolve the ID of the logged User.
     */
    public static function resolveId(): mixed
    {
        return Auth::check() ? Auth::user()->getAuthIdentifier() : null;
    }
}