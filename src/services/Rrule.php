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
use tippingmedia\venti\models\Rrule as RRuleModel;
use tippingmedia\venti\model\Exdate;
use tippingmedia\venti\model\Rdate;
use tippingmedia\venti\models\Event;
use tippingmedia\venti\records\Rrule as RRuleRecord;
use tippingmedia\venti\elements\VentiEvent;

use Recurr\Rule;
use Recurr\transformer\ArrayTransformer;
use Recurr\transformer\ArrayTransformerConfig;
use Recurr\transformer\TextTransformer;
use Recurr\transformer\Translator;


use Craft;
use craft\base\Component;
use craft\helpers\DateTimeHelper;
use DateTime;
use DateTimeZone;

use craft\i18n\Formatter;
use craft\i18n\Locale;

/**
 * Rule Service
 *
 *
 * @author    tippingmedia
 * @package   Venti
 * @since     2.0.0
 */

class Rrule extends Component
{

    /**
    * Save Rrule
    * @param VentiEvent element
    * @return Event model
    */

    public function saveRrule( VentiEvent $event )
    {

        $dateFormat = Craft::$app->locale->getDateFormat('short',Locale::FORMAT_PHP);
        $timeFormat = Craft::$app->locale->getTimeFormat('short',Locale::FORMAT_PHP);
        $format = $dateFormat . ' ' . $timeFormat;

        $timezone = new DateTimeZone(Craft::$app->getTimeZone());
        
        if(!$event->recurring || $event->rRule == "") {
            return false;
        }

        $rruleModel = $this->_populateRRuleModel($event);

        $RuleRecord = RRuleRecord::find()
            ->where(['event_id' => $event->id])
            ->one();

        if (!$RuleRecord) {
            $RuleRecord = new RRuleRecord();
        }

        $RuleRecord->event_id = $event->id;

        //$start = DateTime::createFromFormat('Ymd\Tgia\Z', $rruleModel->start, new DateTimeZone('UTC'));
        //$start->setTimezone(new DateTimeZone('UTC'));
        $start = new DateTime($rruleModel->start);

        $RuleRecord->start = $start;
        $RuleRecord->until = $rruleModel->until;
        $RuleRecord->count = $rruleModel->count;
        $RuleRecord->interval = $rruleModel->interval;
        $RuleRecord->frequency = $rruleModel->frequency;
        $RuleRecord->byDay = $rruleModel->byDay;
        $RuleRecord->byMonth = $rruleModel->byMonth;
        $RuleRecord->byYear = $rruleModel->byYear;
        $RuleRecord->byWeekNo = $rruleModel->byWeekNo;
        $RuleRecord->firstDayOfTheWeek = $rruleModel->firstDayOfTheWeek;

        $RuleRecord->save(false);

        return true;
       // \yii\helpers\VarDumper::dump($event, 5, true); exit;

    }


    /**
    * Human Readable ICalendar Rule String
    * @param  array
    * @return string
    *
    * Every month on Monday, Wednesday, Friday for 30 times
    */
    public function getRuleString( Array $params )
    {

        /**
        * VARIABLES
        */
        $ruleString = [];
        $frequencyLabels = [
            'Daily',
            'Every weekday (Monday to Friday)',
            'Every Monday, Wednesday, and Friday',
            'Every Tuesday, and Thrusday',
            'Weekly',
            'Monthly',
            'Yearly'
        ];


        /**
        * POST VALUES
        */

        $frequency      = array_key_exists('frequency', $params) ? $params['frequency'] : null;
        $repeatEvery    = array_key_exists('every', $params) ? $params['every'] : null;
        $repeatOnDays   = array_key_exists('on', $params) ? $params['on'] : null;                // array
        $repeatBy       = array_key_exists('by', $params) ? $params['by'] : null;                // array
        $starts         = array_key_exists('starts', $params) ? $params['starts'] : null;
        $ends           = array_key_exists('ends', $params) ? $params['ends'] : null;
        $endsOn         = array_key_exists('endsOn', $params) ? $params['endsOn'] : null;        // array
        $endDate        = array_key_exists('enddate', $params) ? $params['enddate']['date'] : null;
        $occur          = array_key_exists('occur', $params) ? $params['occur'] : null;
        $exclude        = array_key_exists('exclude', $params) ? $params['exclude'] : null;      // array
        $include        = array_key_exists('include', $params) ? $params['include'] : null;      // array



        /**
        * Expected Values
        * 0: Daily
        * 1: Every weekday (Monday to Friday)
        * 2: Every Monday, Wednesday, and Friday
        * 3: Every Tuesday, and Thursday
        * 4: Weekly
        * 5: Monthly
        * 6: Yearly
        */

        if ( $frequency != null)
        {
            switch ( intval($frequency) )
            {

                #-- Daily
                case 0:
                    if ( $repeatEvery != null )
                    {
                        if ( $repeatEvery == 1 )
                        {
                            array_push( $ruleString, $frequencyLabels[$frequency] );
                        }
                        else
                        {
                            $everyBlankDays = "Every " . $repeatEvery . " days";
                            array_push( $ruleString, $everyBlankDays);
                        }
                    }
                    break;


                #-- Weekday
                case 1:
                    array_push( $ruleString, $frequencyLabels[$frequency] );
                    break;


                #-- Every Monday, Wednesday, Friday
                case 2:
                    array_push( $ruleString, $frequencyLabels[$frequency] );
                    break;


                #-- Every week on Tuesday, and Thursday
                case 3:
                    array_push( $ruleString, $frequencyLabels[$frequency] );
                    break;


                #-- Weekly + days of the week if selected
                case 4:

                    if ( $repeatOnDays != null )
                    {
                        if( $repeatEvery != null )
                        {
                            if ( intval($repeatEvery) > 1 )
                            {
                                #--  Every 5 weeks on Monday, Wednesday, Saturday
                                $daysString = join( ", ", $repeatOnDays);
                                $everyBlankWeeks = "Every " . $repeatsEvery . " weeks on " . strtoupper($daysString);
                                array_push( $ruleString, $everyBlankWeeks );
                            }
                            else
                            {
                                #-- Every week on Monday, Wednesday, Saturday
                                $daysString = join( ", ", $repeatOnDays);
                                $weeklyString = "Every week on " . strtoupper($daysString);
                                array_push( $ruleString, $weeklyString );
                            }
                        }
                        else
                        {
                            array_push( $ruleString, "Every week" );
                        }
                    }
                    break;


                #-- Every month
                case 5:
                    if( $repeatBy != null )
                    {
                        //$stime = strtotime($starts);
                        $startDate = new DateTime($starts, new DateTimeZone(Craft::$app->getTimeZone()));
                        $everyMonthPre = "Monthly on the ";

                        #--  Every month on the 17th
                        if ( intval($repeatBy) == 0 )
                        {
                            $monthDay = $startDate->format('jS');
                            array_push( $ruleString, $everyMonthPre . $monthDay );
                        }

                        #-- Every month on the fifth Friday
                        if ( intval($repeatBy) == 1 )
                        {
                            $wkDay = new DateTime($starts, new DateTimeZone(Craft::$app->getTimeZone()));
                            $nthDay = $this->getNthDay($startDate->format('w'));
                            array_push( $ruleString, $everyMonthPre . $nthDay . " " . $wkDay  );
                        }
                    }
                    break;


                #-- Every year
                case 6:
                    if ( $repeatEvery != null )
                    {
                        if ( $repeatEvery == 1 )
                        {
                            array_push( $ruleString, "Every year"  );
                        }
                        else
                        {
                            #-- Every 3rd year
                            $everyBlankYear = "Every " . $repeatsEvery . $this->getNumberSuffix($repeatsEvery) . " year";
                            array_push( $ruleString, $everyBlankYear );
                        }
                    }

                    break;

            }


            #-- Ends On by occurrence or date
            if ( $endsOn != null )
            {
                if (intval($endsOn[0]) == 1 && $occur != null)
                {
                    $endAfter = "for " . $occur . " times";
                    array_push( $ruleString, $endOnDate );
                }

                if (intval($endsOn[0]) == 2 && $endDate != null)
                {
                    $endOnDate = "until " . $endDate;
                    array_push( $ruleString, $endOnDate );
                }
            }


            return join(' ',$ruleString);
        }


    }


    /**
    * ICALENDAR RULE
    * @param  array
    * @return string
    *
    * FREQ=MONTHLY;BYDAY=-4FR;COUNT=5 : every month on the 4th last Friday for 5 times
    */

    public function getRRule( Array $params )
    {
        /**
        * POST VALUES
        */
        $frequency      = array_key_exists('frequency', $params) ? $params['frequency'] : null;
        $repeatEvery    = array_key_exists('every', $params) ? $params['every'] : null;
        $repeatOnDays   = array_key_exists('on', $params) ? $params['on'] : null;                // array
        $repeatBy       = array_key_exists('by', $params) ? $params['by'] : null;                // array
        $starts         = array_key_exists('starts', $params) ? $params['starts'] : null;
        $startsTime     = array_key_exists('startsTime',$params) ? $params['startsTime'] : null;
        $ends           = array_key_exists('ends', $params) ? $params['ends'] : null;
        $endsTime       = array_key_exists('endTime', $params) ? $params['endTime'] : null;
        $endsOn         = array_key_exists('endsOn', $params) ? $params['endsOn'] : null;        // array
        $endDate        = array_key_exists('enddate', $params) ? array_key_exists('date',$params['enddate']) ? $params['enddate']['date'] : null : null;
        $occur          = array_key_exists('occur', $params) ? $params['occur'] : null;
        $exclude        = array_key_exists('exclude', $params) ? $params['exclude'] : null;      // array
        $include        = array_key_exists('include', $params) ? $params['include'] : null;      // array

        $ruleString = [];

        // $localeData = craft()->i18n->getLocaleData(craft()->language);
        // $dateFormatter = $localeData->getDateFormatter();
        $dateFormat = Craft::$app->locale->getDateFormat('short',Locale::FORMAT_PHP);
        $timeFormat = Craft::$app->locale->getTimeFormat('short',Locale::FORMAT_PHP);
        $format = $dateFormat . ' ' . $timeFormat;

        $timezone = new DateTimeZone(Craft::$app->getTimeZone());


        $rrule = [
            'FREQ'          => null,
            'DTSTART'       => null,
            'DTEND'         => null,
            'COUNT'         => null,
            'BYDAY'         => null,
            'UNTIL'         => null,
            'INTERVAL'      => null,
            'BYMONTH'       => null,
            'BYMONTHDAY'    => null,
            'WKST'          => "MO",
            'EXDATE'        => null,
            'RDATE'         => null,
        ];

        $needles = [":","-","+0000"];
        $fill = ["","","Z"];


        if ( $frequency != null)
        {
            switch ( intval($frequency) )
            {
                #-- Daily
                case 0:
                    if ( $repeatEvery != null )
                    {
                        if ( $repeatEvery == 1 )
                        {
                            $rrule['FREQ'] = "DAILY";
                        }
                        else
                        {
                            $rrule['FREQ'] = "DAILY";
                            $rrule['INTERVAL'] = $repeatEvery;
                        }
                    }
                    break;

                #-- Weekday
                case 1:
                    $rrule['FREQ'] = "WEEKLY";
                    $rrule['BYDAY'] = "MO,TU,WE,TH,FR";
                    break;


                #-- Every week on MO, TU, WE
                case 2:
                    $rrule['FREQ'] = "WEEKLY";
                    $rrule['BYDAY'] = "MO,WE,FR";
                    break;


                #-- Every week on TU, TH
                case 3:
                    $rrule['FREQ'] = "WEEKLY";
                    $rrule['BYDAY'] = "TU,TH";
                    break;

                #-- Every week on TU, TH
                case 4:
                    if ( $repeatOnDays != null )
                    {
                        if( $repeatEvery != null )
                        {
                            if ( intval($repeatEvery) > 1 )
                            {
                                #--  Every 5 weeks on Monday, Wednesday, Saturday
                                $rrule['FREQ'] = "WEEKLY";
                                $rrule['INTERVAL'] = $repeatEvery;
                                $rrule['BYDAY'] = join(',', array_map(array($this,'getDOW'),$repeatOnDays));
                            }
                            else
                            {
                                #-- Every week on Monday, Wednesday, Saturday
                                $rrule['FREQ'] = "WEEKLY";
                                $rrule['BYDAY'] = join(',', array_map(array($this,'getDOW'),$repeatOnDays));
                            }
                        }
                        else
                        {
                            #-- Every week on day of start date
                            $day = DateTime::createFromFormat($dateFormat, $starts, $timezone);
                            $rrule['FREQ'] = "WEEKLY";
                            $rrule['BYDAY'] = $this->getDOW($day->format('l'));
                        }
                    }
                    else
                    {
                        #-- Every week on day of start date
                        $day = DateTime::createFromFormat($dateFormat, $starts, $timezone);
                        $rrule['FREQ'] = "WEEKLY";
                        $rrule['BYDAY'] = $this->getDOW($day->format('l'));
                    }
                    break;

                    #-- Every month
                    case 5:

                        if( $repeatBy != null )
                        {

                            $startDate = DateTime::createFromFormat($dateFormat, $starts, $timezone);
                            $weekDay = $this->getDOW( $startDate->format('l') );


                            #--  Every month on the 17th
                            if ( intval($repeatBy[0]) == 0 )
                            {
                                $monthDay = $startDate->format('j');
                                $rrule['FREQ'] = "MONTHLY";

                                $rrule['BYMONTHDAY'] = $monthDay;

                                if ($repeatEvery != null && intval($repeatEvery) > 1 ) {
                                    $rrule['INTERVAL'] = $repeatEvery;
                                }
                            }

                            #-- Every month on the fifth Friday
                            if ( intval($repeatBy[0]) == 1 )
                            {
                                $wkDay = DateTime::createFromFormat($dateFormat, $starts, $timezone);
                                $nthDay = $this->getNthDay($startDate);
                                $rrule['FREQ'] = "MONTHLY";
                                if ($this->isFirst($startDate))
                                {
                                    #-- +1DOW
                                    $rrule['BYDAY'] = "+1" . $weekDay;
                                }
                                elseif ($this->isLast($startDate))
                                {
                                    #-- -1DOW
                                    $rrule['BYDAY'] = "-1" . $weekDay;
                                }
                                else
                                {
                                    #-- 28th
                                    $rrule['BYDAY'] = "+" . $nthDay . $this->getDOW( $wkDay->format('l') );
                                }

                                if ($repeatEvery != null && intval($repeatEvery) > 1 ) {
                                    $rrule['INTERVAL'] = $repeatEvery;
                                }
                            }
                        }


                        break;


                    #-- Every year
                    case 6:
                        if ( $repeatEvery != null )
                        {
                            if ( $repeatEvery == 1 )
                            {
                                $rrule['FREQ'] = "YEARLY";
                            }
                            else
                            {
                                #-- Every 3rd year
                                $rrule['FREQ'] = "YEARLY";
                                $rrule['INTERVAL'] = $repeatEvery;
                            }
                        }
                        break;
            }


            #-- Starts
            if ($starts != null)
            {

                /*$stime = $starts ." ". $startsTime;
                //$sdate = new \DateTime($stime,new \DateTimeZone(Craft::$app->getTimeZone()));
                //$sdate->setTimezone(new \DateTimeZone('UTC'));*/
                //$rrule['DSTART'] = str_replace($needles, $fill, gmdate('c',$stime));

                #-- Date string converted to datetime object in CMS timezone then to UTC.

                $sdate = DateTime::createFromFormat($format, $starts.$startsTime, $timezone);
                $sdate->setTimezone(new DateTimeZone('UTC'));

                $rrule['DTSTART'] = $sdate->format("Ymd\THis\Z");

            }

            #-- Ends (non-inclusive end)
            if ($ends != null)
            {
                $edate = DateTime::createFromFormat($format, $ends, $timezone);
                $edate->setTimezone(new DateTimeZone('UTC'));
                //$edate = craft()->venti_recurr->formatToMysqlDate($edate);
                $rrule['DTEND'] = $edate->format("Ymd\THis\Z");
            }


            #-- Ends On by occurrence or date
            if ( $endsOn != null )
            {
                if (intval($endsOn[0]) == 0 && $occur == null)
                {
                    $rrule['COUNT'] = null;
                }

                if (intval($endsOn[0]) == 1 && $occur != null)
                {
                    $rrule['COUNT'] = $occur;
                }

                if (intval($endsOn[0]) == 2 && $endDate != null)
                {
                    #-- Get offset in mintues of current timezone.
                    $time_in_tmz = new DateTime('now', $timezone);
                    $offsetMinutes = $time_in_tmz->getOffset() / 60;
                    #-- Add time so end date is included in recurrence schedule
                    /* $etime = strtotime("+23 hours",strtotime($endDate));
                    $rrule['UNTIL'] = str_replace($needles, $fill, gmdate('c',$etime)); */
                    $endOfDayTime = date($timeFormat, strtotime('tomorrow -'.$offsetMinutes.' minutes'));
                    $etime = DateTime::createFromFormat($format, $endDate .' '. $endOfDayTime, $timezone);
                    $etime->setTimezone(new DateTimeZone('UTC'));
                    $rrule['UNTIL'] = $etime->format("Ymd\THis\Z");
                }
            }


            #-- Excluded Dates
            if ( $exclude != null )
            {
                $ex = [];

                for ($i=0; $i < count($exclude) ; $i++)
                {
                    $exdate = DateTime::createFromFormat($dateFormat, $exclude[$i], $timezone);
                    array_push($ex, $exdate->format('Ymd'));
                }

                $rrule['EXDATE'] = join(',',$ex);
            }

            #-- Included Dates
            if ( $include != null )
            {
                $in = [];

                for ($i=0; $i < count($include) ; $i++)
                {
                    $indate = DateTime::createFromFormat($format, $include[$i] . ' ' . $startsTime, $timezone);
                    $indate->setTimezone(new DateTimeZone('UTC'));
                    array_push($in, $indate->format("Ymd\THis\Z"));
                }

                $rrule['RDATE'] = join(',',$in);
            }

        }


        foreach ($rrule as $key => $value) {
            if($value != null){
                array_push($ruleString, $key . "=" . $value);
            }
        }


        return join(';',$ruleString);

    }



    /**
    * Day of the week short format
    * @return String
    */
    public function getDOW ( $day )
    {
        $dayOfTheWeek = [
            "monday"    => "MO",
            "tuesday"   => "TU",
            "wednesday" => "WE",
            "thursday"  => "TH",
            "friday"    => "FR",
            "saturday"  => "SA",
            "sunday"    => "SU"
        ];
        return $dayOfTheWeek[strtolower($day)];
    }



    /**
    *  Suffix of number nd, th, rd
    *  @return String
    */
    public function getNumberSuffix( $number )
    {
        $last_number = substr($number,-1); //fetch the last number

        // if last number is 0 than it assign value 4
        if($last_number == "0" || $last_number == 0){
            $last_number = 4;
        }
        return date("S",mktime(0,0,0,1,$last_number,2009));
    }


   /**
    *  Parse the RRule into RRuleModel
    *  @return RRuleModel
    */

    public function _populateRRuleModel(VentiEvent $event): RRuleModel
    {
        $dateFormat = Craft::$app->locale->getDateFormat('short',Locale::FORMAT_PHP);
        $timeFormat = Craft::$app->locale->getTimeFormat('short',Locale::FORMAT_PHP);
        $format = $dateFormat . ' ' . $timeFormat;

        $ruleAry = explode(";",$event->rRule);
        $keyValueAry = [];

        $outputAry = [];

        for ($i=0; $i < count($ruleAry); $i++)
        {
            $keyAry = explode("=",$ruleAry[$i]);
            $keyValueAry[$keyAry[0]] = $keyAry[1];
        }

        $rrule = new RRuleModel();

        $rrule->event_id = $event->id;

        // Loop through assign values to rrule model
        foreach ($keyValueAry as $key => $value)
        {
            switch ($key)
            {
                case 'FREQ':
                    $rrule->frequency = strtolower($value);
                    break;

                case 'BYDAY':

                    if(array_key_exists('FREQ', $keyValueAry)) {
                        if($keyValueAry['FREQ'] == 'MONTHLY') {
                            // matches -1FR, +2TU adds number to byWeekNo and Day of week to byDay
                            preg_match('/([-|+]\d*)(MO|TU|WE|TH|FR|SA|SU)/', $value, $bydayMatches);
                            $rrule->byWeekNo = $bydayMatches[1];
                            $rrule->byDay = strtolower($bydayMatches[2]);
                        } else {
                            $rrule->byDay = strtolower($value);
                        }
                    }
                    break;

                case 'COUNT':
                    $rrule->count = $value;
                    break;

                case 'DTSTART':
                    //$date = new DateTime($value, new DateTimeZone(Craft::$app->getTimeZone()));
                    //$date->format($dateFormat) .' '.$date->format($timeFormat);
                    $rrule->start = $value;
                    //Procedure uses byYear for the day of the year.
                    if(array_key_exists('FREQ',$keyValueAry)) {
                        if($keyValueAry['FREQ'] == 'YEARLY') {
                            $rrule->byYear = $date->format('d');
                        }
                    }
                    break;

                case 'INTERVAL':
                    $rrule->interval = $value;
                    break;

                case 'UNTIL':
                    #-- remove Z from date for date output to be correct.
                    //$dte = str_replace("Z", "", $value);
                    $date = DateTime::createFromFormat("Ymd\THis\Z", $value, Craft::$app->getTimeZone());
                    $rrule->until = $date;
                    break;

                case 'BYMONTHDAY':
                    $rrule->byMonthDay = $value;
                    break;

                case 'WKST':
                    $rrule->firstDayOfTheWeek = strtolower($value);
                    break;
            }
        }
        
    
        return $rrule;

    }



    /**
    *  Map Array for populating modal
    *  @return Array
    */
    public function modalValuesArray($rule)
    {

        // $localeData = craft()->i18n->getLocaleData(craft()->language);
        // $dateFormatter = $localeData->getDateFormatter();
        $dateFormat = Craft::$app->locale->getDateFormat('short',Locale::FORMAT_PHP);
        $timeFormat = Craft::$app->locale->getTimeFormat('short',Locale::FORMAT_PHP);
        $format = $dateFormat . ' ' . $timeFormat;

        $ruleAry = explode(";",$rule);
        $keyValueAry = [];

        $outputAry = [];

        for ($i=0; $i < count($ruleAry); $i++)
        {
            $keyAry = explode("=",$ruleAry[$i]);
            $keyValueAry[$keyAry[0]] = $keyAry[1];
        }

        // If there is no count or until it never ends
        if (!array_key_exists('COUNT',$keyValueAry) && !array_key_exists('UNTIL',$keyValueAry))
        {
            $outputAry['endsOn'] = ['0'];
        }

        // Loop through assign values to output array
        foreach ($keyValueAry as $key => $value)
        {
            switch ($key)
            {
                case 'FREQ':
                    if (strtolower($value) == 'daily')
                    {
                        $outputAry['frequency'] = 0;
                    }
                    else if(strtolower($value) == 'weekly')
                    {
                        if(array_key_exists('BYDAY',$keyValueAry))
                        {
                            if($keyValueAry['BYDAY'] == "MO,TU,WE,TH,FR")
                            {
                                $outputAry['frequency'] = 1;
                            }
                            else if($keyValueAry['BYDAY'] == "MO,WE,FR")
                            {
                                $outputAry['frequency'] = 2;
                            }
                            else if($keyValueAry['BYDAY'] == "TU,TH")
                            {
                                $outputAry['frequency'] = 3;
                            }
                            else
                            {
                                $outputAry['frequency'] = 4;
                            }
                        }
                    }
                    else if(strtolower($value) == 'monthly')
                    {
                        $outputAry['frequency'] = 5;
                        if(array_key_exists('BYMONTHDAY',$keyValueAry))
                        {
                            $outputAry['by'] = 0;
                        }
                        else
                        {
                            $outputAry['by'] = 1;
                        }
                    }
                    else if(strtolower($value) == 'yearly')
                    {
                        $outputAry['frequency'] = 6;
                    }
                    break;

                case 'BYDAY':
                    $reg = "/\\b(MO|TU|WE|TH|FR|SA|SU)/";
                    if ( preg_match_all($reg, $value, $matches) )
                    {
                        $outputAry['on'] = array_map( array($this, "dayOfTheWeek"), $matches[0] );
                    }
                    break;

                case 'COUNT':
                    $outputAry['occur'] = $value;
                    $outputAry['endsOn'] = ['1'];
                    break;

                case 'DTSTART':
                    $date = new DateTime($value, new DateTimeZone(Craft::$app->getTimeZone()));
                    $outputAry['starts'] = $date->format($dateFormat);
                    $outputAry['startsTime'] = $date->format($timeFormat);
                    break;

                case 'DTEND':
                    $edate = new DateTime($value, new DateTimeZone(Craft::$app->getTimeZone()));
                    $outputAry['ends'] = $edate->format('n/j/Y');
                    break;

                case 'INTERVAL':
                    $outputAry['every'] = $value;
                    break;

                case 'UNTIL':
                    #-- remove Z from date for date output to be correct.
                    //$dte = str_replace("Z", "", $value);
                    $date = DateTime::createFromFormat("Ymd\THis\Z", $value, new DateTimeZone(Craft::$app->getTimeZone()));
                    $outputAry['enddate'] = $date;
                    $outputAry['endsOn'] = ['2'];
                    break;

                case 'EXDATE':
                    $datesAry = explode(',',$value);
                    $exdates = [];
                    for ($i=0; $i < count($datesAry); $i++) {
                        $exdate = DateTime::createFromFormat("Ymd", $datesAry[$i], new DateTimeZone(Craft::$app->getTimeZone()));
                        $exdates[$i] = $exdate->format($dateFormat);
                    }
                    $outputAry['exclude'] = $exdates;
                    break;

                case 'RDATE':
                    $datesAry = explode(',',$value);
                    $indates = [];
                    for ($i=0; $i < count($datesAry); $i++) {
                        $indate = DateTime::createFromFormat("Ymd\THis\Z", $datesAry[$i], new DateTimeZone(Craft::$app->getTimeZone()));
                        $indates[$i] = $indate->format($dateFormat);
                    }
                    $outputAry['include'] = $indates;
                    break;
            }

        }

        return $outputAry;
    }

    public function getIncludedExcludedDates ($rule)
    {
        $ruleDict = explode(";",$rule);
        $keyValueDict = array();
        $outputDict = array();

        for ($i=0; $i < count($ruleDict); $i++)
        {
            $keyDict = explode("=",$ruleDict[$i]);
            $keyValueDict[$keyDict[0]] = $keyDict[1];
        }

        if(array_key_exists('EXDATE',$keyValueDict))
        {
            $outputDict['excludedDates'] = array_map( array($this,'dateFromString'), explode(',',$keyValueDict['EXDATE']));
        }

        if(array_key_exists('RDATE',$keyValueDict))
        {
            $outputDict['includedDates'] = array_map( array($this,'dateFromString'), explode(',',$keyValueDict['RDATE']));
        }


        return count($outputDict) > 0 ? $outputDict : false;
    }


    public function dateFromString($str)
	{
		return new DateTime($str, new DateTimeZone(Craft::$app->getTimeZone()));
	}


    public function dayOfTheWeek($str)
    {
         $dow = [
            "MO" => "monday",
            "TU" => "tuesday",
            "WE" => "wednesday",
            "TH" => "thursday",
            "FR" => "friday",
            "SA" => "saturday",
            "SU" => "sunday"
        ];
        return $dow[$str];
    }


    public function byDayToOn($str)
    {
        $dow = [
            "MO" => "monday",
            "TU" => "tuesday",
            "WE" => "wednesday",
            "TH" => "thursday",
            "FR" => "friday",
            "SA" => "saturday",
            "SU" => "sunday"
        ];

        $strAry = explode(',',$str);
        $output = [];
        foreach ($strAry as $key => $value)
        {
            array_push($output,$dow[$value]);
        }
        return $output;
    }


    /**
    *  Nth day of the month.
    *  @return String
    */
    public function getNthDay( $date )
    {
        return ceil($date->format('j') / 7);
    }


    /**
    *  If First DOW in month.
    *  @return Bool
    */
    public function isFirst( $date )
    {
        $dateStr = $date->format('m/d/Y');
        $current = new DateTime($dateStr);
        $proxy = new DateTime($dateStr);
        $str = 'first ' . strtolower($date->format('l')) . ' of this month';
        $firstDay = $proxy->modify($str);

        return $firstDay == $current ? true : false;
    }


    /**
    *  If Last DOW in month.
    *  @return Bool
    */
    public function isLast ( $date )
    {

        $dateStr = $date->format('m/d/Y');
        $current = new DateTime($dateStr);
        $proxy = new DateTime($dateStr);
        $str = 'last ' . strtolower($date->format('l')) . ' of this month';
        $lastDay = $proxy->modify($str);

        return $lastDay == $current ? true : false;
    }


    /**
    *  Get EndOn Date for Recurr Rule
    *  @return Datetime
    */
    public function getEndOn ( $rule )
    {
        $ruleAry = explode(";",$rule);
        $keyValueAry = [];
        $endOn;

        for ($i=0; $i < count($ruleAry); $i++)
        {
            $keyAry = explode("=",$ruleAry[$i]);
            $keyValueAry[$keyAry[0]] = $keyAry[1];
        }

        if ( array_key_exists('UNTIL',$keyValueAry) )
        {
            $endOn = new DateTime($keyValueAry['UNTIL'], new DateTimeZone(Craft::$app->getTimeZone()));
        }
        else
        {
            $endOn = null;
        }

        return $endOn;
    }


    /**
       *
       * @param $recurRule recurrence rule - FREQ=YEARLY;INTERVAL=2;COUNT=3;
       * @return string recurrence string - every year for 3 times
       */
    public function recurTextTransform($recurRule, $lang = null)
    {

        //- Recurr's supported locales
        $locales = ['de','en','eu','fr','it','sv','es'];
        //$locales = array();

        // foreach (glob(Craft::$app->getPath()->getPluginsPath().'/venti/vendor/simshaun/recurr/translations/*.php') as $filepath)
        // {
        //     $path = pathinfo($filepath);
        //     array_push($locales,$path['filename']);
        // }

        $locale = in_array(Craft::$app->locale->id, $locales) ? Craft::$app->locale->id : "en";
        if ($lang != null && in_array($lang, $locales))
        {
            $locale = $lang;
        }

        $rule = new Rule($recurRule, new \DateTime());

        $textTransformer = new TextTransformer(
            new Translator($locale)
        );

        return $textTransformer->transform($rule);
    }

    /**
     * Update DTSTART & DTEND values in recurr rule string
     * @return String
     */
    public function updateDTStartEnd($rule, DateTime $start, DateTime $end)
    {
        // $localeData = craft()->i18n->getLocaleData(craft()->language);
        // $dateFormatter = $localeData->getDateFormatter();
        $dateFormat = Craft::$app->getLocale()->getDateFormat('short');
        $timeFormat = Craft::$app->getLocale()->getTimeFormat('short');
        $format = $dateFormat . ' ' . $timeFormat;

        $start->setTimezone(new DateTimeZone('UTC'));
        $dtstart = $start->format("Ymd\THis\Z");

        $end->setTimezone(new DateTimeZone('UTC'));
        $dtend = $end->format("Ymd\THis\Z");

        $patterns = array(
            "/DTSTART=(\d*T\d*Z)/",
            "/DTEND=(\d*T\d*Z)/"
        );

        $replacements = array(
            "DTSTART=".$dtstart,
            "DTEND=".$dtend
        );

        return preg_replace($patterns, $replacements, $rule);

    }

    /**
     * Add a date to rrule's EXDATE string
     * @param $id - event id, $date - date to exclude, $locale - locale of event.
     * @return string
     */
    public function addExcludedDate($rrule, DateTime $date, $locale)
    {
        // $localeData = craft()->i18n->getLocaleData(craft()->language);
        // $dateFormatter = $localeData->getDateFormatter();
        $dateFormat = Craft::$app->getLocale()->getDateFormat('short');
        $timeFormat = Craft::$app->getLocale()->getTimeFormat('short');
        $format = $dateFormat . ' ' . $timeFormat;

        $date->setTimezone(new DateTimeZone('UTC'));
        $exdate = $date->format("Ymd");

        $ruleParts = explode(";", $rrule);
        $hasEXDATE = false;
        $key = null;

        foreach ($ruleParts as $key => $value) {
            if(strpos($value,"EXDATE") !== false)
            {
                $hasEXDATE = true;
                $key = $key;
            }
        }

        if($hasEXDATE === true)
        {
            $ruleParts[$key] .= "," . $exdate;
        }
        else
        {
            array_push($ruleParts, "EXDATE=" . $exdate);
        }

        return join(";",$ruleParts);
    }

}
