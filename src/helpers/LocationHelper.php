<?php
namespace tippingmedia\venti\helpers;

use tippingmedia\venti\Venti;

use Craft;
use craft\helpers\FileHelper;

/**
 * Location helper class.
 *
 * @author    Tipping Media LLC. <support@tippingmedia.com>
 * @copyright Copyright (c) 2016, Tipping Media LLC.
 * @see       http://tippingmedia.com
 * @package   venti.helpers
 * @since     2.0
 */

class LocationHelper
{
    public static function countries()
    {
        $string = file_get_contents(__DIR__ . "/countries.json");

        $json_a = json_decode($string, true);
        asort($json_a);
        return $json_a;
    }


    public static function countryOptions()
    {
        $countries = LocationHelper::countries();
        $options = array();
        foreach ($countries as $key => $value)
        {
            array_push($options,array("label" => $value, "value"=> $key));
        }
        return $options;
    }

    public static function country()
    {
        $settings = craft()->plugins->getPlugin('venti')->getSettings();
        return $settings['country'];
    }
}
