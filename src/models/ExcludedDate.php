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
 * ExcludedDate Model
 *
 * @author    tippingmedia
 * @package   Venti
 * @since     3.0.0
 */
class ExcludedDate extends Model
{

    /**
     * @var int|null id
     */
    public $id;

    /**
     * @var int|null event_id
     */
    public $event_id;

    /**
     * @var date|null start
     */
    public $date;
    
}
