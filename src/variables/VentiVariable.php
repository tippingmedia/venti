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
	public function events($criteria = null)
	{
		return craft()->elements->getCriteria('VentiEvent',$criteria);
	}

	public function nextEvent($criteria = null)
	{
		return craft()->venti_events->nextEvent($criteria);
	}

	public function groups($indexBy = null)
	{
		return Venti::getInstance()->groups->getAllGroups($indexBy);
		//return craft()->venti_groups->getAllGroups($indexBy);
	}

	public function groupIds()
	{
		return Venti::getInstance()->groups->getAllGroupIds();
	}

	public function getGroupById($groupId = null)
	{
		return Venti::getInstance()->groups->getGroupById($groupId);
	}

	public function group($groupId = null)
	{
		return Venti::getInstance()->groups->getGroupById($groupId);
	}

	public function getGroupByHandle($groupHandle = null)
	{
		return Venti::getInstance()->groups->getGroupByHandle($groupHandle);
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
		return Venti::getInstance()->calendar->getCalendarSettingSources();
	}
}
