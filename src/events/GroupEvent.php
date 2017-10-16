<?php
/**
 * @link      https://tippingmedia.com/
 * @copyright Copyright (c) Tippingmedia LLC.
 * @license   https://tippingmedia.com/license
 */

namespace tippingmedia\venti\events;

use yii\base\Event;

/**
 * Event event class.
 *
 * @author Tippingmedia LLC. <support@tippingmedia.com>
 * @since  2.0
 */
class GroupEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var \tippingmedia\venti\models\group|null The group model associated with the event.
     */
    public $group;

    /**
     * @var bool Whether the event is brand new
     */
    public $isNew = false;
}