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
 * Rrule Model
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    tippingmedia
 * @package   Venti
 * @since     2.0.0
 */
class Rrule extends Model
{
    //protected $elementType = 'VentiEvent';

    /**
     * @var int|null elementId
     */
    public $elementId;

    /**
     * @var int|null siteId
     */
    public $siteId;

    /**
     * @var datetime|null start
     */
    public $start;

    /**
     * @var datetime|null until
     */
    public $until;

    /**
     * @var int|null frequency
     */
    public $frequency;

    /**
     * @var int|null interval
     */
    public $interval;

    /**
       * @var string|null bySecond
    */
    public $bySecond;

    /**
       * @var string|null byMinute
    */
    public $byMinute;
    
    /**
       * @var string|null byHour
    */
    public $byHour;
    
    /**
       * @var string|null byMonth
    */
    public $byMonth;
    
    /**
       * @var string|null byDay
    */
    public $byDay;
    
    /**
       * @var string|null byWeekNo
    */
    public $byWeekNo;
    
    /**
       * @var string|null byMonthDay
    */
    public $byMonthDay;
    
    /**
       * @var string|null yearDay
    */
    public $yearDay;
    
    /**
       * @var string|null wkSt
    */
    public $wkSt;
    
    /**
       * @var string|null bySetPos
    */
    public $bySetPos;
    
}
