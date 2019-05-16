<?php
namespace tippingmedia\venti\bundles;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\web\assets\editentry\EditEntryAsset;

class EventListBundle extends AssetBundle
{
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = '@tippingmedia/venti/resources';

        // define the dependencies
        $this->depends = [
            CpAsset::class,
            EditEntryAsset::class
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            //'js/venti.min.js',
            //'lib/fullcalendar/lib/moment.min.js',
            //'lib/fullcalendar/lib/moment-php-map.js',
            'js/ventiInput.js'
        ];

        $this->css = [
            'css/venti.css'
        ];

        parent::init();
    }
}