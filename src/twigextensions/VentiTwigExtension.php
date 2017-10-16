<?php
namespace tippingmedia\venti\twigextensions;


use tippingmedia\venti\services\events;
use tippingmedia\venti\twigextensions\CalendarTokenParser;

use Craft;
use \Twig_Extension;
use \Twig_Filter_Method;
use DateTime;
use DateTimeZone;

class VentiTwigExtension extends \Twig_Extension
{

  public function getName()
  {
    return 'VentiTwig';
  }



  /**
    * Returns the token parser instances to add to the existing list.
    *
    * @return array An array of Twig_TokenParserInterface or
    * Twig_TokenParserBrokerInterface instances
    */
  public function getTokenParsers()
  {
      return [
          new CalendarTokenParser()
      ];
  }

  public function getFilters()
  {
    return [
      new \Twig_SimpleFilter('strtodate', [$this, 'strToDate'])
    ];
  }

  public function getFunctions()
  {
    return [
      new \Twig_SimpleFunction('strtodate', [$this, 'strToDate'])
    ];
  }


  public function strtodate($str, $format)
  {
      return DateTime::createFromFormat($format, $str);
  }
}
