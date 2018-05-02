<?php
/**
 * Venti plugin
 *
 *
 * @link      http://tippingmedia.com
 * @copyright Copyright (c) 2017 tippingmedia
 */

namespace tippingmedia\venti\controllers;

use tippingmedia\venti\Venti;
use tippingmedia\venti\models\Event;
use tippingmedia\venti\elements\VentiEvent;

use Craft;
use craft\web\Controller;

/**
 * BaseEvent Controller
 *
 *
 * @author    tippingmedia
 * @package   Venti
 * @since     2.0.0
 */

abstract class BaseEventController extends Controller
{
	// Protected Methods
	// =========================================================================

	/**
	 * Enforces all Edit Event permissions.
	 *
	 * @param Venti_EventModel $event
	 *
	 * @return null
	 */
	protected function enforceEditEventPermissions(VentiEvent $event)
	{
		$userSessionService = Craft::$app->getUser();
		$permissionSuffix = ':'.$event->groupId;

		if (Craft::$app->getIsMultiSite()) {
            // Make sure they have access to this site
            $this->requirePermission('editSite:'.$event->siteId);
        }


		// Make sure the user is allowed to edit events in this group
		$this->requirePermission('venti-manageEventsFor'.$permissionSuffix);

		// Is it a new event?
		if (!$event->id)
		{
			// Make sure they have permission to create new entries in this group
			$this->requirePermission('venti-manageEvents'.$permissionSuffix);
		}
	}
}
