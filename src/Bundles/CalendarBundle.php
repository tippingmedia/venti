<?php
namespace tippingmedia\venti\bundles;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class CalendarBundle extends AssetBundle
{
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = '@tippingmedia/venti/resources';

        // define the dependencies
        $this->depends = [
            CpAsset::class,
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'lib/fullcalendar/lib/moment.min.js',
            'lib/fullcalendar/lib/moment-php-map.js',
            'lib/fullcalendar/fullcalendar.min.js',
            //'lib/qtip/jquery.qtip.min.js',
            //'lib/fullcalendar/locale-all.js',
            'js/venti.js'

        ];

        $this->css = [
            'css/venti.css',
            'lib/fullcalendar/fullcalendar.css',
            //'lib/qtip/jquery.qtip.min.css',
        ];

        parent::init();
    }
}