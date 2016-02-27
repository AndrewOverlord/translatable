<?php 

namespace Components\Translatable;

use Illuminate\Support\ServiceProvider;

/**
* TranslatableServiceProvider
*/
class TranslatableServiceProvider extends ServiceProvider
{
	
	protected $defer = true;

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        # $this->app->events->subscribe('Components\Translatable\EventListener');
    }

}

?>