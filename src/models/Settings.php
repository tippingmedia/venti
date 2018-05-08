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
use craft\models\FieldLayout;

/**
 * Settings Model
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
class Settings extends Model
{
    // Static
    // =========================================================================

    const DEFAULT_TIME_INTERVAL     = 30;
    const DEFAULT_DURATION          = 60;
    const DEFAULT_MULTISITE         = false;

    const TIMEINTERVALS = [
        15 => 15,
        30 => 30,
        60 => 60,
    ];

   const EVENTDURATIONS = [
        30  => 30,
        60  => 60,
        90  => 90,
        120 => 120,
    ];

    // Properties
    // =========================================================================

    /**
       * @var Enum|null timeInterval = self::$timeIntervals
    */
    public $timeInterval;

    /**
       * @var Enum|null eventDuration
    */
    public $eventDuration;

    
    /**
       * @var string|null pluginName
    */
    public $pluginName;

    /**
       * @var bool|null multisite
    */
    public $multisite;


    public function __construct($attributes = null)
    {
        parent::__construct($attributes);
        $this->timeInterval          = self::DEFAULT_TIME_INTERVAL;
        $this->eventDuration         = self::DEFAULT_DURATION;
        $this->pluginName            = "Venti";
        $this->multisite             = self::DEFAULT_MULTISITE;
    }
    

    // Methods
    // =========================================================================


    /**
     * @return FieldLayoutModel
     */
    public function getFieldLayout(): FieldLayout
    {
        return Craft::$app->fields->getLayoutByType('Venti_Event_Default');
    }

    public static function getTimeIntervals(): array
    {
        return self::TIMEINTERVALS;
    }

    public static function getEventDurations(): array
    {
        return self::EVENTDURATIONS;
    }

}
