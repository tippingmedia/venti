<?php
/**
 * Venti plugin
 *
 *
 * @link      http://tippingmedia.com
 * @copyright Copyright (c) 2017 tippingmedia
 */
 
namespace tippingmedia\venti\services;

use tippingmedia\venti\Venti;

use Craft;
use craft\base\Component;

/**
 * Settings Service
 *
 *
 * @author    tippingmedia
 * @package   Venti
 * @since     2.0.0
 */

class Settings extends Component
{
    /* Venti_SettingsModel */
    private static $settingsModel;

    /**
     * @return int
     */
    public function getTimeInterval()
    {
        return $this->getSettingsModel()->timeInterval;
    }

    /**
     * @return int
     */
    public function getEventDuration()
    {
        return $this->getSettingsModel()->eventDuration;
    }

    /**
     * @return bool
     */
    public function isTranslate()
    {
        return $this->getSettingsModel()->translate;
    }

    /**
     * @return string
     */
    public function getPluginName()
    {
        return $this->getSettingsModel()->pluginName;
    }



    /**
     * @return Venti_SettingsModel
     */
    public function getSettingsModel()
    {
        if (is_null(self::$settingsModel)) {
            $plugin              = Craft::$app->getPlugins()->getPlugin('venti');
            self::$settingsModel = $plugin->getSettings();
        }

        return self::$settingsModel;
    }
}
