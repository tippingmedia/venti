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
use tippingmedia\venti\events\EventEvent;
use tippingmedia\venti\elements\VentiEvent;



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
	public function saveEvent(VentiEvent $event, bool $runValidation = true)
	{
		$isNewEvent = !$event->id;

		//\yii\helpers\VarDumper::dump($event, 5, true); exit;
		if ($runValidation && !$event->validate()) {
            Craft::info('Event not saved due to validation error.'.print_r($event->errors, true), __METHOD__);
			
            return false;
        }

		// Venti Settings
		$settings = Craft::$app->getPlugins()->getPlugin('venti')->getSettings();

		if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE_EVENT)) {
			$this->trigger(self::EVENT_BEFORE_SAVE_EVENT, new EventEvent([
				'event' => $event,
				'isNew' => $isNewEvent
			]));
		}

		$db = Craft::$app->getDb();
		$transaction = $db->beginTransaction();
		
		try {

			if (Craft::$app->getElements()->saveElement($event)) {

				$this->_eventsById[$event->id] = $event;
				//\yii\helpers\VarDumper::dump($event, 5, true);exit;
				if($event->recurring == true) {
					if(!Venti::getInstance()->rrule->saveRrule($event)){
						throw new Exception(Craft::t('RRule was not saved for event “{id}”', array('id' => $event->id)));	
					}
				}
	
				// Update search index with event
				Craft::$app->getSearch()->indexElementAttributes($event);
			}
			
			$transaction->commit();

		} catch (\Exception $e) {
			$transaction->rollback();

			throw $e;
		}

		// Fire an 'afterSaveEvent' event
		if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE_EVENT)) {
			$this->trigger(self::EVENT_AFTER_SAVE_EVENT, new EventEvent([
				'event' => $event,
				'isNew' => $isNewEvent
			]));
		}

		return true;
	}

}
