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
    public $start;

    /**
     * @var date|null until
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
     * @var int|null count
     */
    public $count;

    /**
       * @var string|null firstDayOfTheWeek
    */
    public $firstDayOfTheWeek;

    /**
       * @var int|null byMonth
    */
    public $byMonth;
    
    /**
       * @var string|null byDay
    */
    public $byDay;

    /**
       * @var int|null byYear
    */
    public $byYear;
    
    /**
       * @var int|null byWeekNo
    */
    public $byWeekNo;
    
    /**
       * @var int|null byMonthDay
    */
    public $byMonthDay;
    
    /**
       * @var string|null bySetPos
    */
    public $bySetPos;
    
}
