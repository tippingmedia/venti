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
use tippingmedia\venti\records\Location as LocationRecord;
use tippingmedia\venti\models\Location;
use tippingmedia\venti\events\LocationEvent;

use Craft;
use craft\base\Component;

/**
 * Locations Service
 *
 *
 * @author    tippingmedia
 * @package   Venti
 * @since     2.0.0
 */
class Locations extends Component
{

	// Constants
    // =========================================================================

    /**
     * @event LocationEvent The location that is triggered before a location is saved.
     */
    const EVENT_BEFORE_SAVE_LOCATION = 'beforeSaveLocation';

    /**
     * @event LocatioonEvent The event that is triggered after a location is saved.
     */
    const EVENT_AFTER_SAVE_LOCATION = 'afterSaveLocation';


	// Properties
    // =========================================================================

	private $_allLocationIds;
	private $_locationsById;
	private $_fetchedAllLocations = false;
	/**
	 * Returns a location by its ID.
	 *
	 * @param int $locationId
	 * @return Events_LocationModel|null
	 */
	public function getLocationById(int $locationId)
	{
		if (!$locationId) {
			return null;
		}
		$query = VentiLocation::find()
    		->id($locationId)
			->status(null);
		return $query->one();
	}


	public function getAllLocations($indexBy = null)
	{

		if ($this->_fetchedAllLocations) {
            return array_values($this->_locationssById);
        }

		 $results = $this->_createLocationQuery()
            ->all();

        $this->_locationsById = [];

        foreach ($results as $result) {
            $location = new Location($result);
            $this->_locationsById[$location->id] = $location;
        }

        $this->_fetchedAllLocations = true;

        return array_values($this->_locationsById);

	}



	/**
	 * Saves a location.
	 *
	 * @param Location $event
	 * @throws Exception
	 * @return array
	 */
	// TODO: does this need to be retooled
	public function saveLocation(Location $location, bool $runValidation = true)
	{

		if ($runValidation && !$location->validate()) {
            Craft::info('Location not saved due to validation error.', __METHOD__);

            return false;
        }

		//Location data
		if ($location->id) {

			$locationRecord = LocationRecord::find()
				->where(['id' => $location->id])
				->one();

			if (!$locationRecord) {
				throw new Exception(Craft::t('No location exists with the ID “{id}”', array('id' => $location->id)));
			}

			$oldLocation = new Location($locationRecord->toArray([
				'id',
				'address',
				'addressTwo',
				'city',
				'state',
				'zipCode',
				'counry',
				'longitude',
				'latitude',
				'website',
			]));

			$isNewLocation = false;

		} else {
			$locationRecord = new LocationRecord();
			$isNewLocation = true;
		}

		$locationRecord->address 		= $location->address;
		$locationRecord->addressTwo 	= $location->addressTwo;
		$locationRecord->city   		= $location->city;
		$locationRecord->state   		= $location->state;
		$locationRecord->zipCode    	= $location->zipCode;
		$locationRecord->country   		= $location->country;
		$locationRecord->longitude     	= $location->longitude;
		$locationRecord->latitude    	= $location->latitude;
		$locationRecord->website    	= $location->website;

		$this->trigger(self::EVENT_BEFORE_SAVE_LOCATION, new LocationEvent([
			'location' => $location,
			'isNew' => $isNewLocation
		]));

		$db = Craft::$app->getDb();
        $transaction = $db->beginTransaction();

		try {

			if (Craft::$app->getElements()->saveElement($location)) {
				
				$locationRecord->save(false);
				
				if ($isNewLocation) {
					$location->id = $locationRecord->id;
				}

				// Update search index with location
				Craft::$app->getSearch()->indexElementAttributes($location);

				/*$locationRecords = [];

				if (!$isNewLocation)
				{
					$allOldLocationRecords = LocationRecord::find()
						->where(['id' => $location->id])
						->one();
					
				}*/  

			}

			$transaction->commit();
		} catch (\Exception $e) {
			$transaction->rollback();

			throw $e;
		}


		 $this->trigger(self::EVENT_AFTER_SAVE_LOCATION, new LocationEvent([
            'location' => $location,
            'isNew' => $isNewLocation
        ]));

		return true;
	}


	// Private Methods
    // =========================================================================

    /**
     * Returns a Query object prepped for retrieving locations.
     *
     * @return Query
     */
    private function _createLocationQuery(): Query
    {
        return (new Query())
            ->select([
                'loc.id',
                'loc.address',
                'loc.addressTwo',
                'loc.city',
                'loc.state',
                'loc.zipCode',
				'loc.longitude',
				'loc.latitude',
				'loc.website'
            ])
            ->from(['{{%venti_locations}} loc']);
    }

}
