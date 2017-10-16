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
use tippingmedia\venti\models\Location;

use Craft;
use craft\web\Controller;

/**
 * BaseLocation Controller
 *
 *
 * @author    tippingmedia
 * @package   Venti
 * @since     2.0.0
 */

abstract class BaseLocationController extends Controller
{
	// Protected Methods
	// =========================================================================

	/**
	 * Enforces all Edit Locaation permissions.
	 *
	 * @param Venti_LocationModel $location
	 *
	 * @return null
	 */
	protected function enforceEditLocationPermissions(Location $location)
	{
		$userSessionService = Craft::$app->getUser();

        // if (craft()->isLocalized())
		// {
		// 	// Make sure they have access to this locale
		// 	$userSessionService->requirePermission('editLocale:'.$event->locale);
		// }

		// Make sure the user is allowed to edit locations
		$userSessionService->requirePermission('publishLocations');

		// Is it a new event?
		if (!$location->id)
		{
			// Make sure they have permission to create new locations
			$userSessionService->requirePermission('createLocations');
		}
	}
}
