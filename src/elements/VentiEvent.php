<?php
/**
 * Venti plugin
 *
 *
 * @link      http://tippingmedia.com
 * @copyright Copyright (c) 2017 tippingmedia
 */

namespace tippingmedia\venti\elements;

use tippingmedia\venti\Venti;
use tippingmedia\venti\elements\db\VentiEventQuery;
use tippingmedia\venti\services\Groups;
use tippingmedia\venti\services\Events;
use tippingmedia\venti\services\Rrule;
use tippingmedia\venti\models\Group;
use tippingmedia\venti\model\Event;
use tippingmedia\venti\elements\actions\Edit;
use tippingmedia\venti\elements\actions\Delete;
use tippingmedia\venti\elements\actions\View;
use tippingmedia\venti\records\Event as EventRecord;
use tippingmedia\venti\records\ExcludedDate as ExcludedDateRecord;
use tippingmedia\venti\records\IncludedDate as IncludedDateRecord;
use tippingmedia\venti\Bundles\EventListBundle;


use Craft;
use craft\base\Element;
use craft\db\Query;
use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;
use craft\elements\actions\SetStatus;
use ns\prefix\elements\db\ProductQuery;
use craft\helpers\ArrayHelper;
use craft\helpers\UrlHelper;
use craft\helpers\DateTimeHelper;
use craft\validators\DateTimeValidator;

use DateTime;
use DateTimeZone;

use craft\i18n\Formatter;
use craft\i18n\Locale;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 * VentiEvent Element
 *
 * Element is the base class for classes representing elements in terms of objects.
 *
 * @property FieldLayout|null      $fieldLayout           The field layout used by this element
 * @property array                 $htmlAttributes        Any attributes that should be included in the element’s DOM representation in the Control Panel
 * @property int[]                 $supportedSiteIds      The site IDs this element is available in
 * @property string|null           $uriFormat             The URI format used to generate this element’s URL
 * @property string|null           $url                   The element’s full URL
 * @property \Twig_Markup|null     $link                  An anchor pre-filled with this element’s URL and title
 * @property string|null           $ref                   The reference string to this element
 * @property string                $indexHtml             The element index HTML
 * @property bool                  $isEditable            Whether the current user can edit the element
 * @property string|null           $cpEditUrl             The element’s CP edit URL
 * @property string|null           $thumbUrl              The URL to the element’s thumbnail, if there is one
 * @property string|null           $iconUrl               The URL to the element’s icon image, if there is one
 * @property string|null           $status                The element’s status
 * @property Element               $next                  The next element relative to this one, from a given set of criteria
 * @property Element               $prev                  The previous element relative to this one, from a given set of criteria
 * @property Element               $parent                The element’s parent
 * @property mixed                 $route                 The route that should be used when the element’s URI is requested
 * @property int|null              $structureId           The ID of the structure that the element is associated with, if any
 * @property ElementQueryInterface $ancestors             The element’s ancestors
 * @property ElementQueryInterface $descendants           The element’s descendants
 * @property ElementQueryInterface $children              The element’s children
 * @property ElementQueryInterface $siblings              All of the element’s siblings
 * @property Element               $prevSibling           The element’s previous sibling
 * @property Element               $nextSibling           The element’s next sibling
 * @property bool                  $hasDescendants        Whether the element has descendants
 * @property int                   $totalDescendants      The total number of descendants that the element has
 * @property string                $title                 The element’s title
 * @property string|null           $serializedFieldValues Array of the element’s serialized custom field values, indexed by their handles
 * @property array                 $fieldParamNamespace   The namespace used by custom field params on the request
 * @property string                $contentTable          The name of the table this element’s content is stored in
 * @property string                $fieldColumnPrefix     The field column prefix this element’s content uses
 * @property string                $fieldContext          The field context this element’s content uses
 *
 * http://pixelandtonic.com/blog/craft-element-types
 *
 * @author    tippingmedia
 * @package   Venti
 * @since     2.0.0
 */
class VentiEvent extends Element
{

	const STATUS_LIVE = 'live';
	const STATUS_PENDING = 'pending';
	const STATUS_EXPIRED = 'expired';
	const STATUS_ENABLED = 'enabled';
	const STATUS_DISABLED = 'disabled';
	
	/**
     * @inheritdoc
     *
     * @return VentiEventQuery The newly created [[VentiEventQuery]] instance.
     */
	public static function find(): ElementQueryInterface
    {
        return new VentiEventQuery(static::class);
	}

	/**
	 * Returns the element type name.
	 *
	 * @return string
	 */
	public static function getName(): string
	{
		return Craft::t('venti', 'Event');
	}

 	/**
     * @inheritdoc
     */
    public static function refHandle()
    {
        return 'ventievent';
    }

	/**
	 * Returns whether this element type has content.
	 *
	 * @return bool
	 */
	public static function hasContent(): bool
	{
		return true;
	}

	/**
	 * Returns whether this element type has titles.
	 *
	 * @return bool
	 */
	public static function hasTitles(): bool
	{
		return true;
	}

	public static function hasUris(): bool
	{
	     return true;
	}

	public static function isLocalized(): bool
    {
        return true;
    }

	public static function hasStatuses(): bool
	{
		return true;
	}

    public static function statuses(): array
    {
        return [
			self::STATUS_ENABLED => Craft::t('app', 'Enabled'),
			self::STATUS_DISABLED => Craft::t('app', 'Disabled')
		];
    }
	

	/**
     * @inheritdoc
     */

	protected static function defineSources(string $context = null): array 
	{
		// cpindex only fetches original elements not recurrence event elements
		$sources = [];
		$allowAllEvents = true;

		$groups = new Groups();

		foreach ($groups->getAllGroups() as $group) {

			// Don't add group to sources if user can't edit events in the group
			$currentUser = Craft::$app->getUser()->getIdentity();
			if(!$currentUser->can('venti-manageEventsFor:'.$group->id)) {
				$allowAllEvents = false;
				continue;
			}

			$sources[] = [
				'key'      => 'group:'.$group->id,
				'label'    => Craft::t('site', $group->name),
				'data' =>[
					'handle' => $group->handle
				],
				'criteria' => [
					'groupId' => $group->id,
					'cpindex' => true
				]
			];
		}

		$allEvents = [
			'key' => '*',
			'label'    => Craft::t('venti','All Events'),
			'criteria' => [
				'cpindex' => true
			]
		];
		
		// Only allow to see all events if allowed to edit all groups
		if($allowAllEvents) {
			array_unshift($sources, $allEvents);
		}

		return $sources;
	}

	public function beforeSave(bool $isNew): bool
    {
        $group = $this->getGroup();

        // Verify that the group supports this site
        $groupSiteSettings = $group->getGroupSiteSettings();

        if (!isset($groupSiteSettings[$this->siteId])) {
            throw new Exception("The group '{$group->name}' is not enabled for the site '{$this->siteId}'");
		}
		
		if($this->allDay == '') {
			$this->allDay = 0;
		}

        return parent::beforeSave($isNew);
    }


	public function afterSave(bool $isNew)
    {
		$group = $this->getGroup();
		
        // Get the event record
        if (!$isNew) {
			$record = EventRecord::findOne($this->id);
			// $record = EventRecord::find()
			// 	->where(['elementId' => $this->id, 'siteId' => $this->siteId])
			// 	->one();

            if (!$record) {
                throw new Exception('Invalid event ID: '.$this->id);
            }
        } else {
            $record = new EventRecord();
            $record->id = $this->id;
		}
		
		$record->siteId = $this->siteId;
        $record->groupId = $this->groupId;
        $record->startDate = $this->startDate;
        $record->endDate = $this->endDate;
		$record->endRepeat = $this->endRepeat;
		$record->diff = $this->diff;
        $record->allDay = $this->allDay;
		$record->recurring = $this->recurring;
		$record->rRule = $this->rRule;
		$record->summary = $this->summary;


		//\yii\helpers\VarDumper::dump($record, 5, true);exit;

		$record->save(false);

		//\yii\helpers\VarDumper::dump($record, 5, true);exit;

		// Remove Excluded Dates & Included Dates regardless if is-recurring because event may have changed.
		$excludedDates = (new Query())
			->select([
				'venti_exdate.id'
			])
			->from(['{{%venti_exdate}} venti_exdate'])
			->where(['venti_exdate.event_id' => $this->id])
			->one();

		$includedDates = (new Query())
			->select([
				'venti_rdate.id'
			])
			->from(['{{%venti_rdate}} venti_rdate'])
			->where(['venti_rdate.event_id' => $this->id])
			->one();
		
		if($excludedDates) {
			Craft::$app->getDb()->createCommand()
				->delete('{{%venti_exdate}}', ['event_id' => $this->id])
				->execute();
		}

		if($includedDates) {
			Craft::$app->getDb()->createCommand()
				->delete('{{%venti_rdate}}', ['event_id' => $this->id])
				->execute();
		}

		// Save Excluded & Included Dates
		if($this->recurring == true) {
			$incExtDates = Venti::getInstance()->rrule->getIncludedExcludedDates($this->rRule);
			if($incExtDates) {
				if(array_key_exists('excludedDates', $incExtDates)) {
					foreach ($incExtDates['excludedDates'] as $date) {
						$record = new ExcludedDateRecord();
						$record->event_id = $this->id;
						$record->date = $date;
						$record->save(false);
					}
				}

				if(array_key_exists('includedDates',$incExtDates)) {
					foreach ($incExtDates['includedDates'] as $date) {
						$record = new IncludedDateRecord();
						$record->event_id = $this->id;
						$record->date = $date;
						$record->save(false);
					}
				}
			}
		}
		//Craft::dd($incExtDates);


        parent::afterSave($isNew);
    }

    /**
     * @inheritdoc
     */
    protected static function defineActions(string $source = null): array {

		// Now figure out what we can do with these
		$actions = [];

		$groups = Venti::getInstance()->groups->getEditableGroupIds();

		if (!empty($groups)) {
			#-- for now these are always on
			$userSessionService = Craft::$app->getUser();
			$canSetStatus = true;
			$canEdit = false;

			foreach ($groups as $groupId) {
				
				$canPublishEvents = $userSessionService->checkPermission('publishEvents:'.$groupId);

				// Only show the Set Status action if we're sure they can make changes in all the groups
				if (!
					$canPublishEvents
				)
				{
					$canSetStatus = false;
				}

				// Show the Edit action if they can publish changes to *any* of the groups
				// (the trigger will disable itself for events that aren't editable)
				if ($canPublishEvents) {
					$canEdit = true;
				}
			}


			if ($canSetStatus) {
				$actions[] = SetStatus::class;
			}


			if ($canEdit) {
				$actions[] = Craft::$app->getElements()->createAction([
                    'type' => Edit::class,
                    'label' => Craft::t('venti', 'Edit event'),
                ]);
			}

			$showViewAction = ($source === '*');

			if (!$showViewAction) {
                // They are viewing a specific section. See if it has URLs for the requested site
                $controller = Craft::$app->controller;
                if ($controller instanceof ElementIndexesController) {
					$siteId = $controller->getElementQuery()->siteId ?: Craft::$app->getSites()->currentSite->id;
                    if (isset($groups[0]->siteSettings[$siteId]) && $groups[0]->siteSettings[$siteId]->hasUrls) {
                        $showViewAction = true;
                    }
                }
			}
			
			// View
			if ($showViewAction) {
                $actions[] = Craft::$app->getElements()->createAction([
                    'type' => View::class,
                    'label' => Craft::t('venti', 'View event'),
                ]);
			}


			// Delete?
			if (
				$userSessionService->checkPermission('deleteEvents:'.$groupId)
			) {
				$actions[] = Craft::$app->getElements()->createAction([
					'type' => Delete::class,
					'confirmationMessage' => Craft::t('venti', 'Are you sure you want to delete the selected events?'),
					'successMessage' => Craft::t('venti', 'Events deleted.'),
				]);
			}
		}

		return $actions;
	}




	/**
     * @inheritdoc
     */
    protected static function defineSortOptions(): array
    {
        return [
            'title' => Craft::t('app', 'Title'),
            'uri' => Craft::t('app', 'URI'),
            'startDate' => Craft::t('venti', 'Start Date'),
            'endDate' => Craft::t('venti', 'End Date'),
            'elements.dateCreated' => Craft::t('app', 'Date Created'),
            'elements.dateUpdated' => Craft::t('app', 'Date Updated'),
        ];
    }



	/**
     * @inheritdoc
     */
	protected static function defineTableAttributes(): array
	{
		$attributes = [
			'id' => ['label' => Craft::t('app', 'ID')],
			'title' => ['label' => Craft::t('app', 'Title')],
			'groupId' => ['label' => Craft::t('venti', 'Group ID')],
			'group' => ['label' => Craft::t('venti', 'Group')],
			'slug' => ['label' => Craft::t('app', 'Slug')],
            'uri' => ['label' => Craft::t('app', 'URI')],
			'startDate' => ['label' => Craft::t('venti', 'Start Date')],
			'endDate' => ['label' => Craft::t('venti', 'End Date')],
			'endRepeat' => ['label' => Craft::t('venti', 'End Repeat')],
			'rRule' => ['label' => Craft::t('venti', 'Rrule')],
			'recurring' => ['label' => Craft::t('venti', 'Recurring')],
			'allDay' => ['label' => Craft::t('venti', 'All Day')],
			'summary' => ['label' => Craft::t('venti', 'Summary')],
            'dateCreated' => ['label' => Craft::t('app', 'Date Created')],
            'dateUpdated' => ['label' => Craft::t('app', 'Date Updated')],
		];
		
		return $attributes;
	}


	/**
     * @inheritdoc
     */
    protected function tableAttributeHtml(string $attribute): string
	{
		//todo:these need to return string then we are in business after we fix VentiEventQuery
		switch ($attribute)
		{
			case 'startDate':
			{
				$date = $this->startDate;

				if ($date)
				{
					if ($this->allDay == 1)
					{
						return Craft::$app->formatter->asDate($date,'short');
					}
					else
					{
						return Craft::$app->formatter->asDate($date, 'short') .' '. Craft::$app->formatter->asTime($date, 'short');
					}

				}
				else
				{
					return '';
				}
			}
			case 'endDate':
			{
				$date = $this->endDate;

				if ($date)
				{
					if ($this->allDay == 1)
					{
						return Craft::$app->formatter->asDate($date,'short');
					}
					else
					{
						return Craft::$app->formatter->asDate($date, 'short') .' '. Craft::$app->formatter->asTime($date, 'short');
					}

				}
				else
				{
					return '';
				}
			}
			case 'allDay':
			{
				if($this->allDay == 1)
				{
					return Craft::t('venti','Yes');
				}
				else
				{
					return Craft::t('venti','No');
				}
			}
			case 'group':
			{
				$group = $this->getGroup();
				$color = $group->color;
				return "<div class='group-label-color'><span class='menu-label-color' style='background-color:".$color.";'></span></div>";
			}
			case 'summary':
			{
				//$summary = "<a href='#view-dates'>". $element->$attribute . "</a>";
				$summary = $this->summary;
				return $this->summary != "" ? $summary : '';
			}
		}
		return parent::tableAttributeHtml($attribute);
	}


	// Properties
    // =========================================================================
	/**
     * @var int|null Group ID
     */
    public $groupId;
	/**
     * @var datetime|null Start Date
     */
    private $_startDate;
	/**
     * @var datetime|null End Date
     */
    private $_endDate;
	/**
     * @var string|null rRule
     */
    public $rRule;
	/**
     * @var int|null Recurring
     */
    public $recurring;
	/**
     * @var int|null AllDay
     */
    public $allDay;
	/**
     * @var string|null Summary
     */
    public $summary;
	/**
     * @var datetime|null End Repeat
     */
    public $endRepeat;
	/**
     * @var int|null Diff
     */
    public $diff;
	/**
     * @var int|null Author ID
     */
	public $authorId;
	/**
     * @var int|null Event Id
     */
	public $event_id;
	/**
     * @var datetime|null Scheduled Date
     */
	private $_scheduled_date;


	// Public methods
	// =========================================================================
	

	 /**
     * @inheritdoc
     */
    public function getEditorHtml(): string
	{
		
    	$namespacedId = Craft::$app->getView()->getNamespace();

        $dateFormat = Craft::$app->locale->getDateFormat('short',Locale::FORMAT_PHP);
        $timeFormat = Craft::$app->locale->getTimeFormat('short',Locale::FORMAT_PHP);

		Craft::$app->getView()->registerAssetBundle(EventListBundle::class);
		#-- Start/End Dates
		$html = Craft::$app->getView()->renderTemplate('venti/_editor', [
			'event' 			=> $this,
			'dateFormat' 		=> $dateFormat,
			'timeFormat' 		=> $timeFormat,
			'group' 			=> $this->getGroup(),
			'namespacedId' 		=> $namespacedId
		]);

		#-- Everything else
		$html .= parent::getEditorHtml();

		//\yii\helpers\VarDumper::dump($html, 5, true);exit;

		return $html;
	}


	 /**
     * Sets the events's scheduled date.
     *
     * @param DateTime $scheduled_date
     */
	public function setScheduled_date($scheduled_date)
	{
		$this->_scheduled_date = $scheduled_date;
	}

	/**
     * Returns the event's scheduled date.
     *
     * @return DateTime
     */
	public function getScheduled_date()
	{
		//\yii\helpers\VarDumper::dump($this->_scheduled_date, 5, true);
		return DateTimeHelper::toDateTime($this->_scheduled_date, true, false);
	}

	 /**
     * Sets the events's start date.
     *
     * @param DateTime $startDate
     */
	public function setStartDate($startDate)
	{
		$this->_startDate = $startDate;
	}

	/**
     * Returns the event's startDate or scheduledDate if recurring event.
     *
     * @return DateTime
     */
	public function getStartDate()
	{
		if($this->_scheduled_date !== null) {
			// need to add Start Dates time.
			$startDateTimeString = DateTimeHelper::toDateTime($this->_startDate, false, false)->format("H:i:s");
			return DateTimeHelper::toDateTime($this->_scheduled_date .' '. $startDateTimeString);
		}

		return DateTimeHelper::toDateTime($this->_startDate);
	}

	/**
     * Sets the events's end date.
     *
     * @param DateTime $endDate
     */
	public function setEndDate($endDate)
	{
		$this->_endDate = $endDate;
	}

	/**
     * Returns the event's endDate or create one by modifying scheduled_date with diff.
     *
     * @return DateTime
     */
	public function getEndDate()
	{
		if($this->_scheduled_date !== null && $this->diff !== null ) {
			// need to add Start Dates time.
			$startDateTimeString = DateTimeHelper::toDateTime($this->_startDate, false, false)->format("H:i:s");
			$scheduled_date = DateTimeHelper::toDateTime($this->_scheduled_date .' '. $startDateTimeString);
			return $scheduled_date->modify( "+". $this->diff ." seconds" );
		}

		return DateTimeHelper::toDateTime($this->_endDate);
	}

	

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();

        // Use the entry type's title label
        //$labels['title'] = $this->getType()->titleLabel;

        return $labels;
    }

	/**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();


        $rules[] = [['groupId', 'recurring', 'allDay','diff','event_id'], 'number', 'integerOnly' => true];
        $rules[] = [['startDate', 'endDate','endRepeat','scheduled_date'], DateTimeValidator::class];

        return $rules;
    }

	/**
     * @inheritdoc
     */
    public function getFieldLayout()
    {
        return parent::getFieldLayout() ?? $this->getGroup()->getFieldLayout();
    }

	/**
     * @inheritdoc
     */
    public function getSupportedSites(): array
    {
		$group = $this->getGroup();
        $sites = [];

        foreach ($group->getGroupSiteSettings() as $siteSettings) {
			if ($group->propagateEvents || $siteSettings->siteId == $this->siteId) {
				$sites[] = [
					'siteId' => $siteSettings->siteId,
					'enabledByDefault' => $siteSettings->enabledByDefault
				];
			}
        }

        return $sites;
    }

	/**
     * Returns the Events's group.
     *
     * @return Group
     * @throws InvalidConfigException if [[groupId]] is missing or invalid
     */
    public function getGroup(): Group
    {

        if ($this->groupId === null) {
            throw new InvalidConfigException('Event is missing its Group ID');
        }

        if (($group = Venti::getInstance()->groups->getGroupById($this->groupId)) === null) {
            throw new InvalidConfigException('Invalid event group ID: '.$this->groupId);
        }

        return $group;
	}
	
	/**
     * @inheritdoc
     * @throws InvalidConfigException if [[siteId]] is not set to a site ID that the event's group is enabled for
     */
    public function getUriFormat()
    {
        $groupSiteSettings = $this->getGroup()->getGroupSiteSettings();

        if (!isset($groupSiteSettings[$this->siteId])) {
             throw new InvalidConfigException('Event\'s group ('.$this->groupId.') is not enabled for site '.$this->siteId);
		}

        return $groupSiteSettings[$this->siteId]->uriFormat;

		//return $this->getGroup()->getUrlFormat();
	}


	/**
     * @inheritdoc
     */
    protected function route()
    {
		// Make sure that the event is actually live
        if ($this->getStatus() != self::STATUS_LIVE) {
            return null;
        }

		$siteId = Craft::$app->getSites()->currentSite->id;
		$groupSiteSettings = $this->getGroup()->getGroupSiteSettings();

		if (!isset($groupSiteSettings[$siteId]) || !$groupSiteSettings[$siteId]->hasUrls) {
            return null;
        }

		return [
            'templates/render', [
                'template' => $groupSiteSettings[$siteId]->template,
                'variables' => [
					'event' => $this
                ]
            ]
        ];
    }


 	/**
     * Returns the reference string to this element.
     *
     * @return string|null
     */
    public function getRef()
    {
        return $this->getGroup()->handle.'/'.$this->slug;
	}
	
	/**
     * @inheritdoc
     */
    public function getStatus()
    {
		$status = parent::getStatus();

        if ($status == self::STATUS_ENABLED && $this->endDate) {
            $currentTime = DateTimeHelper::currentTimeStamp();
            $endDate = $this->endDate->getTimestamp();
			$endRepeatDate = ($this->endRepeat ? DateTimeHelper::toDateTime($this->endRepeat)->getTimestamp() : null);
			
			if($currentTime > $endDate && ($endRepeatDate === null || $currentTime > $endRepeatDate)) {
				return self::STATUS_EXPIRED;
			}

			return self::STATUS_LIVE;
            
        }

        return $status;
	}


	// public function getUrl()
	// {
	// 	//$uriFormat = $this->getUriFormat(); $this->group->handle
	// 	//$path = "event/".$this->slug."/".$this->startDate->format('Y-m-d');
	// 	$path = "event/".$this->slug."/".$this->startDate->format('Y-m-d');
	// 	return UrlHelper::siteUrl($path, null, null, $this->siteId);
	// }
	

	/**
     * @inheritdoc
     */
    public function getIsEditable(): bool
    {
        return (
            Craft::$app->getUser()->checkPermission('venti-manageEventsFor:'.$this->groupId)
        );
    }



	/**
     * @inheritdoc
     */
    public function getCpEditUrl()
    {
        $group = $this->getGroup();

        // The slug *might* not be set if this is a Draft and they've deleted it for whatever reason
        $url = UrlHelper::cpUrl('venti/'.$group->handle.'/'.$this->id.($this->slug ? '-'.$this->slug : ''));

        if (Craft::$app->getIsMultiSite() && $this->siteId != Craft::$app->getSites()->currentSite->id) {
            $url .= '/'.$this->getSite()->handle;
        }

        return $url;
	}
	
	/**
     * @inheritdoc
     */
    public function setEagerLoadedElements(string $handle, array $elements)
    {
        parent::setEagerLoadedElements($handle, $elements);
    }


	public function excludedDates()
	{
		$datesDict = (new Query())
			->select(['date'])
			->from(['{{%venti_exdate}}'])
			->where(['event_id' => $this->id])
			->column();
		
		return $datesDict;
	}


	public function includedDates()
	{
		$datesDict = (new Query())
			->select(['date'])
			->from(['{{%venti_rdate}}'])
			->where(['event_id' => $this->id])
			->column();
		return $datesDict;
	}

	/**
     * Returns all recurrences of this element
     *
     * @return VentiEvent
     */
	public function getRecurrences()
	{
		if($this->recurring) {
			$query = $this::find();
			$query->id = $this->id;
			$query->siteId = $this->siteId;
			$query->status = null;
			$query->enabledForSite = false;

			return $query->all();
		}

		return null;
	}

	/**
     * Returns the next event in this elements recurring events
     *
     * @return VentiEvent
     */
	public function getNextRecurrence()
	{
		if($this->recurring) {
			$today = new DateTime();
			$query = $this::find();
			$query->id = $this->id;
			$query->siteId = $this->siteId;
			$query->status = null;
			$query->endDate = ">= ". $today->format('Y-m-d g:i:s');
			$query->cpindex = false;
			$query->enabledForSite = false;

			return $query->one();
		}
		return null;
	}

	/**
     * Returns passed date with startDate time
     *
     * @return DateTime
     */
	public function startDateTime(string $date): DateTime
	{
		$start = DateTimeHelper::toDateTime($this->startDate);
		return DateTimeHelper::toDateTime($date . ' ' . $start->format('g:i a'), true, false);
	}

	/**
     * Returns passed date with time different for end date
     *
     * @return DateTime
     */
	public function endDateTime(string $date): DateTime
	{
		$start = DateTimeHelper::toDateTime($this->startDate);
		$startDateTime = DateTimeHelper::toDateTime($date . ' ' . $start->format('g:i a'), true, false);
		return $startDateTime->modify( "+". $this->diff ." seconds" );
	}
}
