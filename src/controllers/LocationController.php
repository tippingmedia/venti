<?php
namespace Craft;
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
use tippingmedia\venti\services\Locations;
use tippingmedia\venti\helpers\LocationHelper;


use Craft;
use craft\web\Controller;

/**
 * Location Controller
 *
 *
 * @author    tippingmedia
 * @package   Venti
 * @since     2.0.0
 */
class LocationController extends BaseLocationController
{
	/**
	 * Group index
	 */
	public function actionLocationIndex() {
		$variables['locations'] = getAllLocations();

		$this->renderTemplate('venti/locations/_index', $variables);
	}

	/**
	 * Edit a Location.
	 *
	 * @param array $variables
	 * @throws HttpException
	 * @throws Exception
	 */
	public function actionEditLocation(array $variables = []): array {

		// Make sure they have permission to edit this entry
		$locationMOD = new Location();
		if (array_key_exists('locationId',$variables)) {
			$locationMOD->setAttribute('id',$variables['locationId']);
		}

		$this->enforceEditLocationPermissions($locationMOD);
		$currentUser = Craft::$app->getUser();

		$variables['brandNewLocation'] = false;
		$variables['fullPageForm'] = true;
		$variables['defaultCountry'] = LocationHelper::country();


		if (!empty($variables['locationId'])) {
			if (empty($variables['location'])) {
				$variables['location'] = getLocationById($variables['locationId']);

				if (!$variables['location']) {
					throw new HttpException(404);
				}
			}

			$variables['title'] = $variables['location']->title;
		} else {
			if (empty($variables['location'])) {
				$variables['location'] = new Location();
				$variables['brandNewLocation'] = true;
			}

			$variables['title'] = Craft::t('Venti','Create a new location');
		}

		$variables['crumbs'] = [
			['label' => Craft::t('Venti','Venti'), 'url' => UrlHelper::getUrl('venti')],
			['label' => Craft::t('Venti','Locations'), 'url' => UrlHelper::getUrl('venti/locations')],
		];
		// Can't just use the entry's getCpEditUrl() because that might include the locale ID when we don't want it
		$variables['baseCpEditUrl'] = 'venti/location/{id}-{slug}';

		$variables['canDeleteLocation'] = $variables['location']->id && (
			($currentUser->can('deleteLocations'))
		);

		// Set the "Continue Editing" URL
		$variables['continueEditingUrl'] = $variables['baseCpEditUrl'];

		$variables['countries'] = LocationHelper::countryOptions();
		Craft::$app->getView()->includeCssResource('css/entry.css');
		$this->renderTemplate('venti/locations/_edit', $variables);
	}

	/**
	 * Saves a location
	 */
	public function actionSaveLocation() {
		$this->requirePostRequest();

		$location = $this->_getLocationModel();
		$request = Craft::$app->getRequest();

		// Shared attributes
		$location->address         		= $request->getPost('address');
		$location->addressTwo         	= $request->getPost('addressTwo');
		$location->city        			= $request->getPost('city');
		$location->state         		= $request->getPost('state');
		$location->zipCode         		= $request->getPost('zipCode');
		$location->country				= $request->getPost('country');
		$location->longitude         	= $request->getPost('longitude');
		$location->latitude        		= $request->getPost('latitude');
		$location->website         		= $request->getPost('website');

		$location->getContent()->title = $request->getPost('title', $location->title);
		$location->setContentFromPost('fields');

		// Save it
		if (!saveLocation($location)) {
			if ($request->getAcceptsJson()) {
				return $this->asJson([
					'errors' => $location->getErrors(),
				]);
			} else {
				Craft::$app->getSession()->setError(Craft::t('Venti','Couldn’t save the location'));
				$this->redirectToPostedUrl($event);

				// Send the event back to the template
				Craft::$app->getUrlManager()->setRouteParams([
					'location' => $location,
				]);
			}
		} else {
			if ($request->getAcceptsJson()) {
				$return['success'] = true;

				if (craft()->request->isCpRequest()) {
					$return['cpEditUrl'] = $entry->getCpEditUrl();
				}
				return $this->asJson($return);
			} else {
				$this->redirectToPostedUrl($location);
			}
		}
	}

	/**
	 * Fetches or creates an Venti_LocationModel.
	 *
	 * @throws Exception
	 * @return Venti_LocationModel
	 */
	private function _getLocationModel() {
		$locationId = Craft::$app->getRequest()->getPost('locationId');

		if ($locationId) {
			$location = getLocationById($locationId);

			if (!$location) {
				throw new Exception(Craft::t('Venti','No location exists with the ID “{id}”.', array('id' => $locationId)));
			}
		} else {
			$location = new Location();

		}

		return $location;
	}

	/**
	 * Deletes an event.
	 */
	public function actionDeleteLocation() {
		$this->requirePostRequest();
		$locationId = Craft::$app->getRequest()->getRequiredPost('locationId');

		if (Craft::$app->getRequest()->getAcceptsJson()) {
            if(Craft::$app->getElements()->deleteElementById($locationId)) {
				Craft::$app->getSession()->setNotice(Craft::t('Venti','Location deleted'));
                return $this->asJson([
                    'success' => true
				]);
            } else {
				Craft::$app->getSession()->setError(Craft::t('Venti','Couldn’t delete location'));
                return $this->asJson([
                    'errors' => $location->getErrors(),
				]);
            }
        } else {
			if (Craft::$app->getElements()->deleteElementById($locationId)) {
				Craft::$app->getSession()->setNotice(Craft::t('Venti','Location deleted'));
				$this->redirectToPostedUrl();
			} else {
				Craft::$app->getSession()->setError(Craft::t('Venti','Couldn’t delete location'));
			}
		}
	}

}
