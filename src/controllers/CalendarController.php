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

		$variables['groups'] = getAllGroups();
        $variables['timezone'] = Craft::$app->getTimeZone();

        //Which locales can be edited
        $currentUser = Craft::$app->getUser()->getIdentity();
        $locales = craft()->getI18n()->getSiteLocales();
        $editLocales = array();
        foreach ($locales as $locale)
        {
            $editLocales[$locale->id] = $currentUser->can('editLocale:'.$locale->id);
        }

        $variables['editLocales'] = $editLocales;
        
        //Render Template
		$this->renderTemplate('venti/calendar/_index', $variables);
	}

    public function actionCalendarFeed()
    {

        $start      = Craft::$app->getRequest()->getParam('start');
        $end        = Craft::$app->getRequest()->getParam('end');
        $groupId    = Craft::$app->getRequest()->getSegment(3);
        $localeId   = Craft::$app->getRequest()->getSegment(4);

        $output = getCalendarFeed($start, $end, $groupId, $localeId);

        $this->returnJson( $output );

    }
}
