<?php
namespace tippingmedia\venti\twigextensions;

/**
 * Represents a calendar node.
 *
 * @author    Tipping Media, LLC. <support@tippingmedia.com>
 * @copyright Copyright (c) 2015, Tipping Media, LLC.
 * @package   venti.twigextensions
 * @since     1.0
 */

use \Twig_ExtensionInterface;
use \Twig_Extension;
use tippingmedia\venti\Venti;

class CalendarNode extends \Twig_Node
{
    // Public Methods
    // =========================================================================

    /**
     * Compiles the node to PHP.
     *
     * @param \Twig_Compiler $compiler
     *
     * @return null
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            // the (array) cast bypasses a PHP 5.2.6 bug
            //->write("\$context['_parent'] = (array) \$context;\n")
            ->write('$calendar = new \Craft\Venti_Calendar(')
            ->subcompile($this->getNode('criteria'))
            ->raw(");\n")
            ->write('list(')
            ->subcompile($this->getNode('calendarTarget'))
            ->raw(', ')
            ->subcompile($this->getNode('elementsTarget'))
            ->raw(') = $calendar->createCalendar(')
            ->raw(");\n");
    }
}
