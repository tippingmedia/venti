<?php

/**
 * Venti by TippingMedia
 *
 * @package   Venti
 * @author    Adam Randlett
 * @copyright Copyright (c) 2015, TippingMedia
 */

namespace tippingmedia\venti\services;

use tippingmedia\venti\Venti;
use tippingmedia\venti\services\groups;
use tippingmedia\venti\elements\VentiEvent;
use tippingmedia\venti\calendar\Calendar as CalendarGenerator;

use Mexitek\PHPColors\Color;

use Craft;
use craft\base\Component;
use DateTime;


/**
 * Events Service
 *
 *
 * @author    tippingmedia
 * @package   Venti
 * @since     2.0.0
 */


class Calendar extends Component
{

    public function getCalendar($events,$month,$year)
    {
        $cal = new CalendarGenerator($month,$year);
        return $cal->createCalendar($events,Craft::$app->getTimeZone());
    }


    public function getCalendarFeed($start, $end, $groupId, $siteId = null)
    {

        //$groups = craft()->vent_groups->getAllGroups();

        //$criteria = craft()->elements->getCriteria('Venti_Event');
        //$criteria->groupId = $groupId;
        //$criteria->locale = $localeId != null ? $localeId : craft()->language;
	    //$criteria->between = [$start,$end];

        $events = VentiEvent::find()
    		->groupId($groupId)
            ->between([$start,$end])
            ->siteId($siteId)
            ->all();

        $feedData = [];


        //$feed = new Venti_CalendarFeedModel();

        foreach ($events as $param) {

            $group = $param->group;
            #-- Get appropriate color based on brightness and if a multi day event else use default dark color
            $color = new Color($group->color); //\Mexitek\PHPColors\
            $textColor = "#222222";
            if ($color->isDark() && ($param['startDate']->format('Y-m-d') != $param['endDate']->format('Y-m-d')))
            {
                $textColor = "#ffffff";
            }

            // Needed to add intval to integer for full calendar to recognize event obj
            $feedData[] = array(
                "id"        => intval($param['id']),
                "siteId"    => $param['siteId'],
                "title"     => $param['title'],
                "start"     => $param['startDate']->format('c'),
                "end"       => $param['endDate']->format('c'),
                "allDay"    => intval($param['allDay']),
                "summary"   => $param['summary'],
                "recurring" => intval($param['recurring']),
                "rRule"     => $param['rRule'],
                "multiDay"  => $param['startDate']->format('Y-m-d') != $param['endDate']->format('Y-m-d') ? true : false,
                "group"     => $group->name,
                "groupId"   => intval($group->id),
                "color"     => $group->color,
                "textColor" => $textColor
            );
        }

        return $feedData;

    }

    public function getCalendarSettingSources()
    {
        $groups = Venti::getInstance()->groups->getAllGroups();
        $sources = [];

        $currentUser = Craft::$app->getUser()->getIdentity();

        foreach ($groups as $group)
        {
            // If current user can't edit events in group don't add as a source
            $currentUser = Craft::$app->getUser()->getIdentity();
			if(!$currentUser->can('venti-manageEventsFor:'.$group['id'])) {
				continue;
            }
            
            $sources[] = array(
                'url'           => "/admin/venti/calendar/" . $group['id'] . "/" . Craft::$app->sites->getPrimarySite()->id,
                'id'            => $group['id'],
                'label'         => $group['name'],
                'color'         => $group['color'],
                'className'     => $group['handle'] . '-event',
                'overlap'       => true,
                'canManageEvents'  => $currentUser->can('venti-manageEventsFor:'.$group['id'])
            );
        }

        return $sources;

    }
}
