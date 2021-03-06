<?php

namespace Just\Warehouse;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;

class WarehouseServiceProvider extends ServiceProvider
{
    use EventMap,
        ObserverMap;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerEvents();
        $this->registerObservers();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->configure();
        $this->offerPublishing();
        $this->registerCommands();

        $this->app->bind('stock', function () {
            return new Stock;
        });
    }

    /**
     * Setup the configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/warehouse.php',
            'warehouse'
        );
    }

    /**
     * Setup the resource publishing.
     *
     * @return void
     */
    protected function offerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/warehouse.php' => config_path('warehouse.php'),
            ], 'warehouse-config');
        }
    }

    /**
     * Register the Artisan commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\FreshCommand::class,
                Console\Commands\InstallCommand::class,
                Console\Commands\MigrateCommand::class,
                Console\Commands\MakeLocationCommand::class,
                Console\Commands\OrdersUnholdCommand::class,
            ]);
        }
    }

    /**
     * Register the events.
     *
     * @return void
     */
    protected function registerEvents()
    {
        $dispatcher = $this->app->make(Dispatcher::class);

        foreach ($this->events as $event => $listeners) {
            foreach ($listeners as $listener) {
                $dispatcher->listen($event, $listener);
            }
        }
    }

    /**
     * Register the model observers.
     *
     * @return void
     */
    public function registerObservers()
    {
        foreach ($this->observers as $model => $observer) {
            $model::observe($observer);
        }
    }
}
