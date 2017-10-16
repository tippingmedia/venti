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

use mexitek\phpcolors\Color;

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


    public function getCalendarFeed($start, $end, $groupId, $localeId = null)
    {

        //$groups = craft()->vent_groups->getAllGroups();

        //$criteria = craft()->elements->getCriteria('Venti_Event');
        //$criteria->groupId = $groupId;
        //$criteria->locale = $localeId != null ? $localeId : craft()->language;
	    //$criteria->between = [$start,$end];

        $events = VentiEvent::find()
    		->groupId($groupId)
    		->between([$start,$end])
            ->all();

        $feedData = array();


        //$feed = new Venti_CalendarFeedModel();

        foreach ($events as $param) {

            $group = $param->getGroup();
            #-- Get appropriate color based on brightness and if a multi day event else use default dark color
            $color = new Color($group->color); //\Mexitek\PHPColors\
            $textColor = "#222222";
            if ($color->isDark() && ($param['startDate']->format('Y-m-d') != $param['endDate']->format('Y-m-d')))
            {
                $textColor = "#ffffff";
            }

            $feedData[] = array(
                "id"        => $param['id'],
                "eid"       => $param['eid'],
                "title"     => $param['title'],
                "start"     => $param['startDate']->format('c'),
                "end"       => $param['endDate']->format('c'),
                "allDay"    => $param['allDay'],
                "summary"   => $param['summary'],
                "locale"    => $param['locale'],
                "repeat"    => $param['repeat'],
                "rRule"     => $param['rRule'],
                "multiDay"  => $param['startDate']->format('Y-m-d') != $param['endDate']->format('Y-m-d') ? true : false,
                "group"     => $group->name,
                "color"     => $group->color,
                "textColor" => $textColor
            );
        }

        return $feedData;

    }

    public function getCalendarSettingSources()
    {
        $groups = getAllGroups();
        $sources = array();

        $currentUser = Craft::$app->getUser()->getIdentity();

        foreach ($groups as $group)
        {

            $sources[] = array(
                'url'           => "/admin/venti/feed/" . $group['id'] . "/" . craft()->language,
                'id'            => $group['id'],
                'label'         => $group['name'],
                'color'         => $group['color'],
                'overlap'       => true,
                'canEdit'       => $currentUser->can('publishEvents:'.$group['id']),
                'canDelete'     => $currentUser->can('deleteEvents:'.$group['id'])
            );
        }

        return $sources;

    }
}
