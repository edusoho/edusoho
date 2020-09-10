<?php
/**
 * PayPal Item bag
 */

namespace Omnipay\PayPal;

use Omnipay\Common\ItemBag;
use Omnipay\Common\ItemInterface;

/**
 * Class PayPalItemBag
 *
 * @package Omnipay\PayPal
 */
class PayPalItemBag extends ItemBag
{
    /**
     * Add an item to the bag
     *
     * @see Item
     *
     * @param ItemInterface|array $item An existing item, or associative array of item parameters
     */
    public function add($item)
    {
        if ($item instanceof ItemInterface) {
            $this->items[] = $item;
        } else {
            $this->items[] = new PayPalItem($item);
        }
    }
}
