<?php

namespace Just\Warehouse\Observers;

use LogicException;
use Just\Warehouse\Models\OrderLine;
use Just\Warehouse\Jobs\PairInventory;
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
     * Handle the OrderLine "deleting" event.
     *
     * @param  \Just\Warehouse\Models\OrderLine  $line
     * @return void
     */
    public function deleting(OrderLine $line)
    {
        tap($line->inventory, function ($inventory) use ($line) {
            if (is_null($inventory)) {
                return $line->release();
            }

            $line->reservation->update([
                'order_line_id' => null,
            ]);

            PairInventory::dispatch($inventory);
        });
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

        if ($line->order_id !== $line->getOriginal('order_id')) {
            throw new LogicException('The order ID attribute can not be changed.');
        }
    }
}
