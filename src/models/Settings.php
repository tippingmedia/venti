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
    const HIDE_LOCATION             = 0;
    const HIDE_REGISTRATION         = 0;

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
    public $timeInterval = self::DEFAULT_TIME_INTERVAL;

    /**
       * @var Enum|null eventDuration
    */
    public $eventDuration = self::DEFAULT_DURATION;

    
    /**
       * @var string|null pluginName
    */
    public $pluginName = "Venti";

    /**
       * @var bool|null multisite
    */
    public $multisite = self::DEFAULT_MULTISITE;
    
    /**
       * @var string|null license
    */
    public $license;
    
    /**
       * @var string|null googleMapsApiKey
    */
    public $googleMapsApiKey;
    
    /**
       * @var string|null country
    */
    public $country;
    
    /**
       * @var bool|null hideRegistration
    */
    public $hideRegistration = self::HIDE_REGISTRATION;
    
    /**
       * @var bool|null hideLocation
    */
    public $hideLocation = self::HIDE_LOCATION;
    
    

  /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            ['pluginName', 'string'],
            ['pluginName', 'default', 'value' => 'Venti'],
            ['multisite', 'integer'],
            ['license', 'string'],
            ['googleMapsApiKey', 'string'],
            ['country', 'string'],
            ['hideRegistration', 'integer'],
            ['hideLocation', 'integer'],

        ];
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

    public function getTimeIntervals()
    {
        return self::TIMEINTERVALS;
    }

    public function getEventDurations()
    {
        return self::EVENTDURATIONS;
    }

}
