<?php

namespace Clockwork\Core\Providers;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\ServiceProvider;

class MacroServiceProvider extends ServiceProvider
{
    /**
     * Register custom macros here.
     *
     * @return void
     */
    public function boot()
    {
//        Request::macro('parse', function ($field) {
//            return array_filter(explode(',', $this->get($field, '')));
//        });
        
        HasMany::macro('toHasOne', function () {
            return new HasOne(
                $this->query,
                $this->parent,
                $this->foreignKey,
                $this->localKey
            );
        });
    }
}
