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
use tippingmedia\venti\models\Event;
use tippingmedia\venti\models\Group;
use tippingmedia\venti\records\Event as EventRecord;
use tippingmedia\venti\services\Rrule;
use tippingmedia\venti\events\EventEvent as VentiEvent;


use Craft;
use craft\base\Component;

/**
 * Events Service
 *
 *
 * @author    tippingmedia
 * @package   Venti
 * @since     2.0.0
 */
class Events extends Component
{

	// Constants
    // =========================================================================

    /**
     * @event EventEvent The event that is triggered before a event is saved.
     */
    const EVENT_BEFORE_SAVE_EVENT = 'beforeSaveEvent';

    /**
     * @event EventEvent The event that is triggered after a event is saved.
     */
    const EVENT_AFTER_SAVE_EVENT = 'afterSaveEvent';

	/**
     * @event EventEvent The event that is triggered before a event is deleted.
     */
    const EVENT_BEFORE_DELETE_EVENT = 'beforeDeleteEvent';

    /**
     * @event EventEvent The event that is triggered after a event is Deleted.
     */
    const EVENT_AFTER_DELETE_EVENT = 'afterDeleteEvent';


	// Properties
    // =========================================================================

	/**
     * @var
     */
    private $_eventsById;




	/**
	 * Returns an event by its ID.
	 *
	 * @param int $eventId
	 * @return Events_EventModel|null
	 */
	public function getEventById(int $eventId, int $siteId = null)
	{
		if (!$eventId) {
			return null;
		}

		$query = VentiEvent::find();
        $query->id($eventId);
		$query->siteId($siteId);
		$query->status(null);
        $query->enabledForSite(false);

		return $query->one();
	}



	/**
	 * Saves an event.
	 *
	 * @param Event $event
	 * @throws Exception
	 * @return array
	 */
	public function saveEvent(Event $event, bool $runValidation = true)
	{
		if ($runValidation && !$event->validate()) {
            Craft::info('Event not saved due to validation error.', __METHOD__);

            return false;
        }

		// Venti Settings
		$settings = Craft::$app->getPlugins()->getPlugin('venti')->getSettings();

		// Event data
		if ($event->id) {
			 //cid ?
			$eventRecord = EventRecord::find()
				->where(['id' => $event->id])
				->one();

			if (!$eventRecord) {
				throw new Exception(Craft::t('No event exists with the ID “{id}”', array('id' => $event->id)));
			}

			$oldEvent = new Event($eventRecord->toArray([
				'id',
				'groupId',
				'startDate',
				'endDate',
				'repeat',
				'allDay',
				'summary',
				'rRule',
				'siteId',
				'location',
				'specificLocation',
				'registration'
			]));
			$isNewEvent = false;
		} else {
			$eventRecord = new VentiRecord();
			$isNewEvent = true;
		}

		$eventRecord->groupId 	= $event->groupId;
		$eventRecord->startDate = $event->startDate;
		$eventRecord->endDate   = $event->endDate;
		$eventRecord->repeat    = $event->repeat;
		$eventRecord->allDay    = $event->allDay;
		$eventRecord->summary   = $event->summary;
		$eventRecord->rRule     = $event->rRule;
		$eventRecord->siteId    = $event->siteId;
		$eventRecord->location  = $event->location;
		$eventRecord->specificLocation = $event->specificLocation;
		$eventRecord->registration = $event->registration;

		$this->trigger(self::EVENT_BEFORE_SAVE_EVENT, new VentiEvent([
			'event' => $event,
			'isNew' => $isNewEvent
		]));

		$db = Craft::$app->getDb();
        $transaction = $db->beginTransaction();

		try {

			if (Craft::$app->getElements()->saveElement($event)) {

				$eventRecord->save(false);

				// Now that we have an element ID, save it on the model
				if ($isNewEvent) {
					$eventRecord->id = $event->id;
				}

				$this->_eventsById[$event->id] = $event;

			
				// TODO: save rrule record
	

				// Update search index with event
				Craft::$app->getSearch()->indexElementAttributes($event);


				// Update the locale records and content

				// We're saving all of the element's locales here to ensure that they all exist and to update the URI in
				// the event that the URL format includes some value that just changed

				$eventRecords = [];

				if (!$isNewEvent)
				{

					$existingEventRecords = EventRecord::find()
						->id($event->id)
						->all();

					foreach ($existingEventRecords as $record)
					{
						$eventRecords[$record->siteId] = $record;
					}
				}

				/* 
					$mainEventSiteId = $event->siteId;

					$sites = $event->getSites();
					$siteIds = [];

					if (!$sites) {
						throw new Exception('All elements must have at least one site associated with them');
					}

					foreach ($sites as $siteId => $siteInfo) {
						if (is_numeric($siteId) && is_string($siteInfo)) {
							$siteId = $siteInfo;
							$siteInfo = [];
						}

						$siteIds[] = $siteId;

						if (!isset($siteInfo['enabledByDefault'])) {
							$siteInfo['enabledByDefault'] = true;
						}

						if (isset($eventRecords[$siteId])) {

							$plugin = Craft::$app->getPlugins()->getPlugin('venti');
							// TODO: Update settings from Translateable -to- Multisite
							$multiSite = $plugin->getSettings()->getAttribute('multisite');

							if ($multiSite) {
								continue;
							}
							else
							{
								$localeEventRecord = $eventRecords[$localeId];

								
								$localeEventRecord->groupId		= $event->groupId;
								$localeEventRecord->siteId 		= $siteId;
								$localeEventRecord->startDate  	= $event->startDate;
								$localeEventRecord->endDate  	= $event->endDate;
								$localeEventRecord->rRule  		= $event->rRule;
								$localeEventRecord->summary  	= $event->summary;
								$localeEventRecord->allDay  	= $event->allDay;
								$localeEventRecord->repeat  	= $event->repeat;
								$localeEventRecord->location    = $event->location;
								$localeEventRecord->specificLocation    = $event->specificLocation;
								$localeEventRecord->registration    = $event->registration;
							}

						}
						else
						{
							$localeEventRecord = new EventRecord();

							
							$localeEventRecord->groupId		= $event->groupId;
							$localeEventRecord->siteId 		= $siteId;
							$localeEventRecord->startDate  	= $event->startDate;
							$localeEventRecord->endDate  	= $event->endDate;
							$localeEventRecord->rRule  		= $event->rRule;
							$localeEventRecord->summary  	= $event->summary;
							$localeEventRecord->allDay  	= $event->allDay;
							$localeEventRecord->repeat  	= $event->repeat;
							$localeEventRecord->location    = $event->location;
							$localeEventRecord->specificLocation    = $event->specificLocation;
							$localeEventRecord->registration    = $event->registration;

						}

						// Is this the main site?
						$isMainEvent = ($siteId == $mainEventSiteId);

						$siteEventRecord->validate();
						$event->addErrors($siteEventRecord->getErrors());

						if ($event->hasErrors())
						{
							return false;
						}


						$success = $siteEventRecord->save(false);

						if ($success)
						{
							// Save Repeat Events
							if ($event->repeat == 1 && $event->rRule != null)
							{
								if(craft()->venti_recurr->saveRecurrData($event, $localeEventRecord))
								{
									craft()->userSession->setNotice(Craft::t('Recurring events saved'));
								}

							}
							else
							{
								// If recurr data already present delete to make way for he new
								if (($recurrRecord = Venti_RecurrRecord::model()->findByAttributes(array("cid" => $localeEventRecord->getAttribute('id'))))) {
									$recurrRecord->deleteAllByAttributes(array("cid" => $localeEventRecord->getAttribute('id')));
								}

								// Saving Single Event
								$recurrModel = new Venti_RecurrModel();
								$recurrModel->setAttribute('cid', $localeEventRecord->getAttribute('id'));
								$recurrModel->setAttribute('startDate', $localeEventRecord->getAttribute('startDate'));
								$recurrModel->setAttribute('endDate', $localeEventRecord->getAttribute('endDate'));
								$recurrModel->setAttribute('isrepeat', 0);


								if(!craft()->venti_recurr->saveRecurrEvent($recurrModel))
								{
									craft()->userSession->setNotice(Craft::t('Single event not saved'));
								}

							}
						}
						else
						{
							// Pass any validation errors on to the element
							$event->addErrors($localeEventRecord->getErrors());
							if ($event->hasErrors())
							{
								return false;
							}

							// Don't bother with any of the other locales
							break;
						}
					}
				*/
				
			}
		
			
			$transaction->commit();

		} catch (\Exception $e) {
			$transaction->rollback();

			throw $e;
		}


		// Fire an 'afterSaveEvent' event
        $this->trigger(self::EVENT_AFTER_SAVE_EVENT, new VentiEvent([
            'event' => $event,
            'isNew' => $isNewEvent
        ]));

		return true;
	}

}
