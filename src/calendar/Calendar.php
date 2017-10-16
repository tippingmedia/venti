<?php
  /* ------------------------------------------------------------ *\
      Author: Adam Randlett
              @adamrandlett
              randlett.net

      -Generate a custom calendar view
  \* ------------------------------------------------------------ */
  namespace tippingmedia\venti\calendar;

  use Craft;
  use craft\helpers\DateTimeHelper; 
  use DateTime;
  use DateTimeZone;

  class Calendar{

      protected $weekdays = array(
          'sun' => 0,
          'mon' => 1,
          'tue' => 2,
          'wed' => 3,
          'thu' => 4,
          'fri' => 5,
          'sat' => 6
        );
      protected $month;
      protected $year;
      protected $firstDayOfMonth;
      protected $lastDayOfMonth;
      protected $firstDayNum;
      protected $lastDayNum;
      protected $days_in_month;
      protected $eventsGroupedByDay;
      protected $timezone;


    public function __construct($month, $year)
    {
      $this->month = $month;
      $this->year = $year;
      $this->firstDayOfMonth = strtolower($this->firstOfMonth($this->month,$this->year));
      $this->lastDayOfMonth = strtolower($this->lastOfMonth($this->month,$this->year));
      $this->firstDayNum = $this->weekdays[$this->firstDayOfMonth];
      $this->lastDayNum = $this->weekdays[$this->lastDayOfMonth];
      $this->days_in_month = cal_days_in_month(0, $this->month, $this->year);
    }


    /* GET EVENTS INTO ARRAYS BY DAY AND OUTPUT CALENDAR */

    public function createCalendar($events,$timezone)
    {

      $this->timezone = $timezone;

      for( $d = 1; $d < $this->days_in_month + 1 ; $d++ )
      {
        //day of month year
        $idxDay = $d;
        //year.day.month
        $arrayId = $this->year.$this->month.$d;
        //set array with day as key
        $this->eventsGroupedByDay[$arrayId] = array(
          'date' => array(
            'day' => $d,
            'month' => $this->month,
            'year' => $this->year
          ),
          'events' => array()
        );

        foreach ($events as $event)
        {

          //$event = $key;
          $eventElm2 = $event->title;
          $startDate = $event->startDate;
          //create event element array
          $eventElm = array(
            'title' => $event->title,
            'url' => $event->url,
            'slug' => $event->slug,
            'eid' => $event->eid,
            'id' => $event->id,
            'dateString' => $startDate,
            'dateParts' => array(
              'day' => $startDate->format('j'),
              'month' => $startDate->format('n'),
              'year' => $startDate->format('Y')
            )
          );

          //\CVarDumper::dump($startDate->format('Ynj'), 5, true);

          if($startDate->format('Ynj') == $arrayId)
          {
            $count = (count($this->eventsGroupedByDay[$arrayId]['events']) + 1) - 1 ;
            $this->eventsGroupedByDay[$arrayId]['events'][$count] = $eventElm;
          }
        }
      }

      return $this->outputCalendar();
    }



    /* GENERATE EVENTS FROM CRAFT EVENTS INTO UL */

    private function generateEvents($day_id)
    {
      // \CVarDumper::dump($this->eventsGroupedByDay, 5, true);
      // exit;
      $listItems = "";
      $evtArry  = $this->eventsGroupedByDay[$day_id]['events'];
      if($evtArry)
      {
        foreach($evtArry as $key)
        {
          $listItems .= "<li>";
          $listItems .= "<a href='".$key['url']."/".$key['eid']."'>".$key['title']."</a>";
          $listItems .= "</li>";
        }
        return "<ul>" . $listItems . "</ul>";
      }
      else
      {
        return false;
      }

    }



    /* GET PARTS OF CALENDAR JOIN AND OUTPUT */

    private function outputCalendar()
    {
      $headerString = "<table><thead><tr>
                      <th>
                        <span class='tri'>Sun</span>
                        <span class='single'>S</span>
                      </th>
                      <th>
                        <span class='tri'>Mon</span>
                        <span class='single'>M</span>
                      </th>
                      <th>
                        <span class='tri'>Tue</span>
                        <span class='single'>T</span>
                      </th>
                      <th>
                        <span class='tri'>Wed</span>
                        <span class='single'>W</span>
                      </th>
                      <th>
                        <span class='tri'>Thu</span>
                        <span class='single'>T</span>
                      </th>
                      <th>
                        <span class='tri'>Fri</span>
                        <span class='single'>F</span>
                      </th>
                      <th>
                        <span class='tri'>Sat</span>
                        <span class='single'>S</span>
                      </th>
                    </tr></thead><tbody><tr>";
      $footerString = "</tbody></table>";
      $beginBlanks = $this->getBlankDays($this->firstDayNum,"begin");
      $endBlanks = $this->getBlankDays($this->lastDayNum,"end");
      $days = $this->getDays($this->days_in_month,$this->month,$this->year);

      return $headerString . $beginBlanks . $days . $endBlanks . $footerString;
    }



    /* GET BLANK NON MONTH DAYS FOR FILLERS */

    private function getBlankDays($blanks,$pos)
    {
      $blank_days = $blanks;
      $blank_string = "";
      $day_count = $pos == 'begin' ? 1 : 6;
      if($pos == 'begin')
      {

        while($blank_days > 0)
        {
          $blank_string .= "<td></td>";
          $blank_days = $blank_days - 1;
        }
        $day_count ++;

      }
      else
      {

        while($blank_days < 6)
        {
          $blank_string .= "<td></td>";
          $blank_days = $blank_days + 1;
        }
        $day_count --;

      }

      return $blank_string;
    }



    /* GET DAYS OF THE MONTH WITH EVENTS INPUT */

    private function getDays()
    {
      $day_num = 1;
      $day_count = $this->firstDayNum + 1;
      $days_string = "";
      $dt = new DateTime();
      $dt->setTimeZone(new DateTimeZone($this->timezone));
      $today = $dt->format('Ynj');
      while($day_num <= $this->days_in_month)
      {
        
        $day_id = $this->year.$this->month.$day_num;
        $days_string .= $today == $day_id ? '<td class="today">' : '<td>';
        $days_string .='<a href="/day/'.$this->year.'/'.$this->month.'/'.$day_num.'" class="title"><span>today</span><em>'.$day_num.'</em></a>';
        $days_string .= $this->generateEvents($day_id,$this->eventsGroupedByDay);
        $days_string .='</td>';

        $day_num++;
        $day_count++;

        if($day_count > 7)
        {
          $days_string .= "</tr><tr>";
          $day_count = 1;
        }
      }
      return $days_string;
    }


    /* GET FIRST WEEKDAY OF THE MONTH - SHORT FORM (Sun,Mon,Tue...) */

    private function firstOfMonth($month,$year)
    {
      return date("D", strtotime($month.'/01/'.$year.' 00:00:00'));
    }



    /* GET LAST WEEKDAY OF THE MONTH - SHORT FORM (Sun,Mon,Tue...) */

    private function lastOfMonth($month,$year)
    {
      return date("D", strtotime('-1 second',strtotime('+1 month',strtotime($month.'/01/'.$year.' 00:00:00'))));
    }


  }
