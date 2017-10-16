<?php
/**
 * Venti plugin
 *
 *
 * @link      http://tippingmedia.com
 * @copyright Copyright (c) 2017 tippingmedia
 */

namespace tippingmedia\venti\models;

use tippingmedia\venti\Venti;

use Craft;
use craft\base\Model;

/**
 * Calendar Model
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    tippingmedia
 * @package   Venti
 * @since     1.0.0
 */

class CalendarFeed extends Model
{

	// Properties
    // =========================================================================

    /**
     * @var int|null ID
     */
    public $id;

	/**
     * @var string|null title
     */
    public $title;

	/**
     * @var string|null start
     */
    public $start;

	/**
     * @var string|null end
     */
    public $end;

	/**
     * @var numbrer|null allDay
     */
    public $allDay;

	/**
     * @var bool|null overlap
     */
    public $overlap = true;

	/**
     * @var string|null color
     */
    public $color;

}
