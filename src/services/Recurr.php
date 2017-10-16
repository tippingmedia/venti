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
use tippingmedia\venti\services\Rrule;
use tippingmedia\venti\models\Event;
use tippingmedia\venti\records\Event as EventsRecord;
// ******* NEEDS RECURR LIBRARY USED

use Recurr\Rule;
use Recurr\transformer\ArrayTransformer;
use Recurr\transformer\ArrayTransformerConfig;
use Recurr\transformer\TextTransformer;
use Recurr\transformer\Translator;

use Craft;
use craft\base\Component;
use DateTime;
use DateTimeZone;

/**
 * Recurr Service
 *
 *
 * @author    tippingmedia
 * @package   Venti
 * @since     2.0.0
 */


class Recurr extends Component
{


    /**
     * Generates End Date from difference in time of original Start & End Dates
     * Repeat dates need endDate but same time as startDate
     *
     * @return  DateTime
     */

     private function sameDateNewTime(DateTime $date1, DateTime  $date2, DateInterval $difr)
     {

        $newDate = new DateTime($date2->format('c'));
        $newDate->setTimezone(new DateTimeZone("UTC"));
        $newDate1 = $newDate->add($difr);

        return $newDate1;
     }


    /**
     * Get dates array based on recur template.
     *
     * @return  array
     */

    public function getRecurDates($start, $rrule)
    {

        $timezone           = Craft::$app->getTimeZone(); //'UTC','America/New_York','America/Denver'
        //$startDate          = $start->format(DateTime::MYSQL_DATETIME);


        #-- returns null or datetime
        $endOn              = Venti::getInstance()->rrule->getEndOn($rrule);
        $rule               = new Rule($rrule, $start, $endOn, $timezone);
        $transformer        = new ArrayTransformer();
        $transformerConfig  = new ArrayTransformerConfig();

        $transformerConfig->enableLastDayOfMonthFix();
        $transformer->setConfig($transformerConfig);


        // if ($endOn !== null)
        // {
        //     $constraint = new \Recurr\Transformer\Constraint\BetweenConstraint($start, $endOn, true);
        // }
        // else
        // {
        //     $constraint = new \Recurr\Transformer\Constraint\AfterConstraint(new \DateTime(), true);
        // }


        return $transformer->transform($rule);

    }

    /**
     * Saving New Element based on recurring dates
     * @return --
     */
     // TODO: this will probably be removed.
    public function saveRecurrData(Event $model, $eventRecord)
    {
        $startdate  = $model->getAttribute('startDate');
        $enddate    = $model->getAttribute('endDate');
        $diff       = $startdate->diff($enddate);
        $rule       = $model->getAttribute('rRule');
        $dates      = $this->getRecurDates($startdate,$rule);
        $dates      = $dates->toArray();



        #-- If recurr data already present delete to make way for he new
        if (($recurrRecord = EventsRecord::model()->findByAttributes(array("cid" => $eventRecord->getAttribute('id'))))) {
            $recurrRecord->deleteAllByAttributes(array("cid" => $eventRecord->getAttribute('id')));
        }


        $i = 0;
        foreach ($dates as $key => $value)
        {

            #-- Returns DateTime::startdate & DateTime::endDate from Recur\Recurrece object
            $startDate      = $value->getStart();
            $endDate        = $value->getEnd();
            $recurrModel    = new Venti_RecurrModel();

            $recurrModel->setAttribute('cid', $eventRecord->getAttribute('id'));
            $recurrModel->setAttribute('startDate', $startDate);
            $recurrModel->setAttribute('endDate', $endDate);
            //$this->sameDateNewTime($model->endDate, $date, $diff)
            $recurrModel->setAttribute('isrepeat', $i == 0 ? 0 : 1);

            if(!$this->saveRecurrEvent($recurrModel)){
                return false;
            }
            $i++;
        }

        return true;

    }


    /**
	 * Saves a recurr event.
	 *
	 * @param Venti_EventModel $event
	 * @throws Exception
	 * @return bool
	 */
     // TODO: This will probably be removed.
	public function saveRecurrEvent(Venti_RecurrModel $event)
	{
        $recurrRecord               = new Venti_RecurrRecord();
        $recurrRecord->cid          = $event->cid;
        $recurrRecord->startDate    = $event->startDate;
        $recurrRecord->endDate      = $event->endDate;
        $recurrRecord->isrepeat     = $event->isrepeat;

        $recurrRecord->validate();
        $event->addErrors($recurrRecord->getErrors());

        if (!$event->hasErrors())
        {
            $recurrRecord->save(false);
            return true;
        }

		return false;
    }


    /**
     * Convert date to MYSQL_DATETIME in UTC
     *
     * @return  Craft\DateTime
     */
    public function formatToMysqlDate(\Datetime $date)
    {
        $temp = DateTimeHelper::formatTimeForDb( $date->getTimestamp() );
        return  DateTime::createFromFormat( DateTime::MYSQL_DATETIME, $temp );
    }


    /**
       *
       * @param $recurRule recurrence rule - FREQ=YEARLY;INTERVAL=2;COUNT=3;
       * @return string recurrence string - every year for 3 times
       */
    public function recurTextTransform($recurRule, $lang = null)
    {
        #--- Recurr's supported locales
        $locales = ['de','en','eu','fr','it','sv','es'];

        $locale = in_array(craft()->language, $locales) ? craft()->language : "en";
        if ($lang != null && in_array($lang, $locales))
        {
            $locale = $lang;
        }

        $rule = new \Recurr\Rule($recurRule, new DateTime());

        $textTransformer = new \Recurr\Transformer\TextTransformer(
            new \Recurr\Transformer\Translator($locale)
        );

        return $textTransformer->transform($rule);
    }


}
