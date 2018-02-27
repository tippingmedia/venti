<?php
/**
 * Venti plugin
 *
 *
 * @link      http://tippingmedia.com
 * @copyright Copyright (c) 2017 tippingmedia
 */

namespace tippingmedia\venti\variables;

use tippingmedia\venti\Venti;
use tippingmedia\venti\elements\VentiEvent;
use tippingmedia\venti\elements\db\VentiEventQuery;

use Craft;

/**
 * Venti Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.venti }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    tippingmedia
 * @package   Venti
 * @since     2.0.0
 */
 
class VentiVariable
{

	public function events($criteria = null): VentiEventQuery
    {
        $query = VentiEvent::find();
        if ($criteria) {
            Craft::configure($query, $criteria);
        }
        return $query;
    }

	public function nextEvent($criteria = null)
	{
		return craft()->venti_events->nextEvent($criteria);
	}

	public function groups($indexBy = null)
	{
		return Venti::$plugin->groups->getAllGroups($indexBy);
		//return craft()->venti_groups->getAllGroups($indexBy);
	}

	public function groupIds()
	{
		return Venti::$plugin->groups->getAllGroupIds();
	}

	public function getGroupById($groupId = null)
	{
		return Venti::$plugin->groups->getGroupById($groupId);
	}

	public function group($groupId = null)
	{
		return Venti::$plugin->groups->getGroupById($groupId);
	}

	public function getGroupByHandle($groupHandle = null)
	{
		return Venti::$plugin->groups->getGroupByHandle($groupHandle);
	}

	public function locations()
	{
		return craft()->elements->getCriteria('Venti_Location');
	}

	public function settings()
    {
    	return Venti::getInstance()->settings;
    }

	public function getCalendarSettingSources()
	{
		return Venti::$plugin->calendar->getCalendarSettingSources();
	}
}
