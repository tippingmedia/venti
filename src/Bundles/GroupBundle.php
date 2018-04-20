<?php
namespace tippingmedia\venti\bundles;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class GroupBundle extends AssetBundle
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
            'https://cdn.jsdelivr.net/clipboard.js/1.5.12/clipboard.min.js'
        ];

        $this->css = [
            'css/venti.css',
        ];

        parent::init();
    }
}