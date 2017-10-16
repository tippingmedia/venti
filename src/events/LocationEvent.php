<?php
/**
 * @link      https://tippingmedia.com/
 * @copyright Copyright (c) Tippingmedia LLC.
 * @license   https://tippingmedia.com/license
 */

namespace craft\events;

use yii\base\Event;

/**
 * Event event class.
 *
 * @author Tippingmedia LLC. <support@tippingmedia.com>
 * @since  2.0
 */
class LocationEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var \tippingmedia\venti\models\location|null The location model associated with the event.
     */
    public $location;

    /**
     * @var bool Whether the event is brand new
     */
    public $isNew = false;
}