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
 * Ics Service
 *
 *
 * @author    tippingmedia
 * @package   Venti
 * @since     2.0.0
 */

class Ics extends Component
{


    public function renderICSFile($groupId = null)
    {
        $group = craft()->venti_groups->getGroupById($groupId);
        $events = $this->renderICSEvents($groupId);
        $ics = "BEGIN:VCALENDAR"."\r\n".
			    "VERSION:2.0\r\n".
			    "PRODID:-//tippingmedia/venti//EN"."\r\n".
				join("",$events).
				"END:VCALENDAR"."\r\n";

        header("Content-Type: text/calendar");
        header("Access-Control-Allow-Origin: *");
        header("Content-Disposition: inline; filename=".$group['name'].".ics");
        echo trim($ics);

    }

    public function renderICSEvents($groupId = null)
    {
        $events = array();
        $eventRecords = Venti_EventRecord::model()->findAllByAttributes(["groupId"=>$groupId]);
        $eventModels = Venti_EventModel::populateModels($eventRecords, 'id');

        foreach ($eventModels as $model)
        {
            $event = craft()->elements->getElementById($model->elementId);

            $events[] = "BEGIN:VEVENT\r\n".
            "DTSTAMP:".$this->_dateToCal($event->dateCreated->format('U'))."\r\n".
            $this->eventDetails($event).
            "SUMMARY:".$this->_escapeString($event->title)."\r\n".
            "URL;VALUE=URI:".$this->_escapeString($event->url)."\r\n".
            "UID:".$this->_dateToCal($event->dateCreated->format('U'))."-".$event->id."\r\n".
            "END:VEVENT\r\n";
        }

        return $events;

    }


    public function eventDetails($event)
    {

        $dateParts = "DTSTART:".$this->_dateToCal($event->startDate->format('U'))."\r\n".
                     "DTEND:".$this->_dateToCal($event->endDate->format('U'))."\r\n";

        if ($event->rRule != "")
        {   $ruleCollection = explode(";",$event->rRule);
            $rules = array();
            foreach ($ruleCollection as $rule)
            {
                $ruleValues = explode("=",$rule);

                if ($ruleValues[0] == "DTSTART" || $ruleValues[0] == "DTEND")
                {
                    continue;
                }
                elseif ($ruleValues[0] == "RDATE")
                {
                    foreach (explode(",",$ruleValues[1]) as $date)
                    {
                        $dateParts .= "RDATE:".$this->_dateToCal(strtotime($date))."\r\n";
                    }

                }
                elseif ($ruleValues[0] == "EXDATE")
                {
                    foreach (explode(",",$ruleValues[1]) as $date)
                    {
                        $dateParts .= "EXDATE:".$this->_dateToCal(strtotime($date))."\r\n";
                    }
                }
                else
                {
                    $rules[] = $rule;
                }
            }

            $dateParts .= "RRULE:".join(";",$rules)."\r\n";
        }

        if (count($event->location))
        {
            if (is_array($event->location) || is_object($event->location))
            {
                $dateParts .= "LOCATION:".$this->_escapeString($event->location[0]->fullAddress())."\r\n";
            }
        }

        return $dateParts;
    }


    /**
	 * Generate the specific date markup for a ics file
	 *
	 * @param  integer $timestamp Timestamp to be transformed
	 * @return string
	 */
	private function _dateToCal($timestamp)
	{
		return date('Ymd\THis\Z', ($timestamp) ? $timestamp : time());
	}


    /**
	 * Escape characters
	 *
	 * @param  string $string String to be escaped
	 * @return string
	 */
	private function _escapeString($string)
	{
		return preg_replace('/([\,;])/','\\\$1', ($string) ? $string : '');
	}

}
