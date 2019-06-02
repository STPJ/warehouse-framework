<?php

namespace Just\Warehouse\Observers;

use LogicException;
use Just\Warehouse\Models\OrderLine;
use Just\Warehouse\Events\OrderLineCreated;
use Just\Warehouse\Exceptions\InvalidGtinException;

class OrderLineObserver
{
    /**
     * Handle the OrderLine "creating" event.
     *
     * @param  \Just\Warehouse\Models\OrderLine  $line
     * @return void
     */
    public function creating(OrderLine $line)
    {
        if (! is_gtin($line->gtin)) {
            throw new InvalidGtinException;
        }
    }

    /**
     * Handle the OrderLine "created" event.
     *
     * @param  \Just\Warehouse\Models\OrderLine  $line
     * @return void
     */
    public function created(OrderLine $line)
    {
        OrderLineCreated::dispatch($line);
    }

    /**
     * Handle the OrderLine "updating" event.
     *
     * @param  \Just\Warehouse\Models\OrderLine  $line
     * @return void
     */
    public function updating(OrderLine $line)
    {
        if ($line->gtin !== $line->getOriginal('gtin')) {
            throw new LogicException('The GTIN attribute can not be changed.');
        }
    }
}