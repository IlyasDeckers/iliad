<?php
namespace Clockwork\Core\Concerns;

use Auth;

trait ResolveId
{
    /**
     * Resolve the ID of the logged User.
     *
     * @return mixed|null
     */
    public static function resolveId(): mixed
    {
        return Auth::check() ? Auth::user()->getAuthIdentifier() : null;
    }
}