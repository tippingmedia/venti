<?php
/**
 * Venti Plugin
 *
 * @link http://tippingmedia.com
 * @author Tipping Media
 */

namespace tippingmedia\venti;

use Craft;
use craft\base\Plugin;
use craft\services\Elements;
use craft\services\Fields;
use craft\services\Plugins;
use craft\services\Resources;
use craft\web\UrlManager;
use craft\events\PluginEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\ResolveResourcePathEvent;
use craft\web\twig\variables\CraftVariable;

use yii\base\Event;
use craft\helpers\UrlHelper;

use tippingmedia\venti\variables\VentiVariable;
use tippingmedia\venti\models\Settings;
use tippingmedia\venti\elements\Event as EventElement;
use tippingmedia\venti\elements\Location as LocationElement;
use tippingmedia\venti\fields\Event as EventField;
use tippingmedia\venti\fields\Location as LocationField;
use tippingmedia\venti\services\groups;

use tippingmedia\venti\twigextensions\VentiTwigExtension;
use tippingmedia\venti\twigextensions\CalendarNode;
use tippingmedia\venti\twigextensions\CalendarTokenParser;



class Venti extends Plugin
{

	public static $plugin;
	public $hasCpSettings = true;
	public $hasSettings = true;

	public function init()
    {
		parent::init();
        self::$plugin = $this;
		$this->name = $this->getName();
		
		Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('venti', VentiVariable::class);
            }
        );
		
        // Add in our Twig extensions
        Craft::$app->view->twig->addExtension(new VentiTwigExtension());

		$this->setComponents([
            'groups' => \tippingmedia\venti\services\Groups::class,
            'events' => \tippingmedia\venti\services\Events::class,
			'calendar' => \tippingmedia\venti\services\Calendar::class,
            'locations' => \tippingmedia\venti\services\locations::class,
            'ics' => \tippingmedia\venti\services\Ics::class,
			'settings' => \tippingmedia\venti\services\Settings::class,
			'rrule' => \tippingmedia\venti\services\Rrule::class,
			'recurr' => \tippingmedia\venti\services\Recurr::class,
        ]);

		Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {

			// Groups
			$event->rules['venti/groups'] = 'venti/groups/group-index';
			$event->rules['venti/groups/<groupId:\d+>'] = 'venti/groups/edit-group';
			$event->rules['venti/groups/new'] = 'venti/groups/edit-group';
			// Events
    		$event->rules['venti'] = 'venti/event/index';
    		$event->rules['venti/<groupHandle:{handle}>/new'] = 'venti/event/edit-event';
			$event->rules['venti/<groupHandle:{handle}>/new?/<siteHandle:\w+>'] = 'venti/event/edit-event';
			$event->rules['venti/<groupHandle:{handle}>/<eventId:\d+><slug:(?:-[^\/]*)?>'] = 'venti/event/edit-event';
			$event->rules['venti/<groupHandle:{handle}>/<eventId:\d+><slug:(?:-[^\/]*)?>/<siteHandle:{handle}>'] = 'venti/event/edit-event';

			// Locations
			$event->rules['venti/locations'] = ['template' => 'venti/location/locationIndex'];
			$event->rules['venti/locations/new'] = ['template' => 'venti/locations/editLocation'];
			$event->rules['venti/location/(?P<locationId>\d+)(?:-{slug})'] = ['template' => 'venti/locations/editLocation'];

			// Calendar
			$event->rules['venti/calendar'] = ['template' => 'venti/calendar/calendarIndex'];
			$event->rules['venti/feed/(?P<groupId>\d+)/(?P<siteId>\w+)'] = ['template' => 'venti/calendar/calendarFeed'];

			// Settings
			$event->rules['venti/settings'] = 'venti/settings/index';
			$event->rules['venti/settings/license'] = 'venti/settings/license';
    		$event->rules['venti/settings/general'] = 'venti/settings/general';
			$event->rules['venti/settings/events'] = 'venti/settings/events';
			$event->rules['venti/settings/groups'] = 'venti/settings/groups';
		});
    }

	public function getName()
	{
		// $plug = Craft::$app->getPlugins()->getPlugin('Venti', false);
		// if($plug->isInstalled && $plug->isEnabled)
		// {
		// 	$settings = $this->getSettings();
		// 	if ($settings->pluginName)
		// 	{
		// 		return $settings->pluginName;
		// 	}
		// }

	    return Craft::t('venti','Venti');
	}

	public function defineTemplateComponent()
    {
        return VentiVariable::class;
    }

	public function getIconPath()
    {
        return Craft::$app->getPath()->getPluginsPath().'/resources/img/venti.svg';
    }




	public function registerSiteRoutes()
	{
	    return array(
	        //'event/(?P<slug>{slug})/(?P<eid>\w+)' => array('action' => 'venti/event/viewEventByEid'),
			'event/(?P<slug>{slug})/(?P<year>\d{4})-(?P<month>(?:0?[1-9]|1[012]))-(?P<day>(?:0?[1-9]|[12][0-9]|3[01]))' => array('action' => 'venti/event/viewEventByStartDate'),
			'calendar/ics/(?P<groupId>\d+)' => array('action' => 'venti/event/viewICS')
	    );
	}


	public function registerCachePaths()
	{
	    return array(
	        Craft::$app->getPath()->getStoragePath().'venti/' => Craft::t('venti','Venti'),
	    );
	}



	/**
	 * @return array
	 */
	public function registerUserPermissions()
	{
		$groups = getAllGroups();
		$groupEditPermissions = array();
		foreach ($groups as $group) {
			$groupEditPermissions['editGroupEvents:'.$group['id']] = array('label' => Craft::t('venti','Edit') . " ". $group['name'] . " ". Craft::t('venti','events'));
			$groupEditPermissions['editGroupEvents:'.$group['id']]['nested']['createEvents:'.$group['id']] = array('label'=> Craft::t('venti','Create events in') ." ". $group['name']);
			$groupEditPermissions['editGroupEvents:'.$group['id']]['nested']['publishEvents:'.$group['id']] = array('label'=> Craft::t('venti','Publish events in') ." ". $group['name']);
			$groupEditPermissions['editGroupEvents:'.$group['id']]['nested']['deleteEvents:'.$group['id']] = array('label'=> Craft::t('venti','Delete events in') ." ". $group['name']);
		}

		$permissions =  array(
			'ventiEditSettings' 		=> array('label' => Craft::t('venti','Venti: Settings')),
			'ventiEditLocations' 		=> array('label' => Craft::t('venti','Venti: Locations'),
				'nested' 	=> array(
					'createLocations' 	=> array('label' => Craft::t('venti','Create')),
					'publishLocations' 	=> array('label' => Craft::t('venti','Publish')),
					'deleteLocations'   => array('label' => Craft::t('venti','Delete'))
				)
			),
			'ventiEditEvents' 			=> array('label' => Craft::t('venti','Venti: Edit Events'), 'nested' => $groupEditPermissions),
		);

		return $permissions;

	}


	/**
     * @return Venti_SettingsModel
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }



    /**
     * Get Settings URL
     */
    public function getSettingsResponse()
    {
		 $url = UrlHelper::cpUrl('venti/settings');
        return Craft::$app->controller->redirect($url);
        //return '';
		// if (Craft::$app->getRequest()->isCpRequest() && Craft::$app->getUser()->isLoggedIn() && Craft::$app->getUser()->isAdmin()) {
		// 	return Craft::$app->getView()->render('venti/settings/_index');
		// }
    }

}
