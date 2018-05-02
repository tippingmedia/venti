<?php

/**
 * Venti Calendar Controller
 *
 *
 * @link      http://tippingmedia.com
 * @copyright Copyright (c) 2017 tippingmedia
 */

 namespace tippingmedia\venti\controllers;

use tippingmedia\venti\Venti;
use tippingmedia\venti\services\groups;
use tippingmedia\venti\services\Calendar;
use tippingmedia\venti\bundles\CalendarBundle;

use Craft;
use craft\web\Controller;
use craft\i18n\Locale;

/**
 * Calendar Controller
 *
 *
 * @author    tippingmedia
 * @package   Venti
 * @since     2.0.0
 */

class CalendarController extends Controller
{
    /**
	 * Event index
	 */
	public function actionCalendarIndex() {
        // $localeData = craft()->getI18n()->getLocaleData(craft()->language);
        // $dateFormatter = $localeData->getDateFormatter();
        $dateFormat = Craft::$app->getLocale()->getDateFormat('short');
        $timeFormat = Craft::$app->getLocale()->getTimeFormat('short');

		$variables['dateFormat'] = $dateFormat;
		$variables['timeFormat'] = $timeFormat;

		$variables['groups'] = Venti::getInstance()->groups->getAllGroups();
        $variables['timezone'] = Craft::$app->getTimeZone();
        $variables['editSites'] = [];

        //Which sites can be edited
        if (Craft::$app->getIsMultiSite()) {
            $currentUser = Craft::$app->getUser()->getIdentity();
            $sites = Craft::$app->getSites()->getAllSites();
            $editSites = [];
            foreach ($sites as $site) {
                $editSites[$site->id] = $currentUser->can('editSite:'.$site->id);
            }
            $variables['editSites'] = $editSites;
        }
        
        //Render Template
        $this->getView()->registerAssetBundle(CalendarBundle::class);
		$this->renderTemplate('venti/calendar/_index', $variables);
	}

    public function actionCalendarFeed()
    {

        $start      = Craft::$app->getRequest()->getParam('start');
        $end        = Craft::$app->getRequest()->getParam('end');
        $groupId    = Craft::$app->getRequest()->getSegment(3);
        $siteId   = Craft::$app->getRequest()->getSegment(4);

        $output = Venti::getInstance()->calendar->getCalendarFeed($start, $end, $groupId, $siteId);

        $this->asJson($output);

    }
}
