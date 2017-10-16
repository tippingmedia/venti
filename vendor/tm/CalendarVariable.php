<?php
namespace Craft;

/**
 * Calendar variable class.
 *
* @author    Tipping Media, LLC. <support@tippingmedia.com>
 * @copyright Copyright (c) 2015, Tipping Media, LLC.
 * @package   venti.twigextensions
 * @since     1.0
 */
class CalendarVariable
{
    // Properties
    // =========================================================================

    /**
     * @var
     */
    public $head;


    /**
     * @var
     */
    public $month;

    /**
     * @var
     */
    public $year;

    // Public Methods
    // =========================================================================

    /**
     * Returns the Calendar Head
     *
     * @return string|null
     */
    public function getCalendarHead()
    {
        return $this->head;
    }

    /**
     * Returns each Day with Events
     *
     * @return string|null
     */
    public function getDayEvents()
    {
        return $this->dayEvents;
    }

    /**
     * Returns the calendar month
     *
     * @return string|null
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * Returns the calendar year
     *
     * @return string|null
     */
    public function getYear()
    {
        return $this->year;
    }

    public function getNextMonth()
    {
        $date = mktime( 0, 0, 0, $this->month, 1, $this->year );
        return strftime( '%e', strtotime( '+1 month', $date ) );
    }

    public function getPreviousMonth()
    {
        $date = mktime( 0, 0, 0, $this->month, 1, $this->year );
        return strftime( '%e', strtotime( '-1 month', $date ) );
    }

    public function getNextYear()
    {
        return ($this->year + 1);
    }

    public function getPreviousYear()
    {
        return ($this->year - 1);
    }

}
