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
use tippingmedia\venti\models\Group;
use tippingmedia\venti\model\Event;
use tippingmedia\venti\elements\actions\Edit;
use tippingmedia\venti\elements\actions\Delete;
use tippingmedia\venti\elements\actions\View;
use tippingmedia\venti\records\Event as EventRecord;


use Craft;
use craft\base\Element;
use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;
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
    const STATUS_EXPIRED = 'expired';
	

	public static function find(): ElementQueryInterface
    {
        return new VentiEventQuery(get_called_class());
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
			self::STATUS_LIVE => Craft::t('app', 'Live'),
            self::STATUS_DISABLED => Craft::t('app', 'Disabled')
		];
    }
	

	/**
     * @inheritdoc
     */

	protected static function defineSources(string $context = null): array 
	{
		$sources = [
			[
				'key' => '*',
				'label'    => Craft::t('venti','All Events'),
				'criteria' => [
					//'isrepeat' => 'null'
				]
			]
		];

		$groups = new Groups();

		foreach ($groups->getAllGroups() as $group) {
			$key = 'group:'.$group->id;
			$sources[] = [
				'key'      => $key,
				'label'    => Craft::t('site', $group->name),
				'data' =>[
					'handle' => $group->handle
				],
				'criteria' => [
					'groupId' => $group->id,
					//'isrepeat' => 'is not null'
				]
			];
		}

		$sources[] = ['heading' => 'Groups'];
		
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

		

        return parent::beforeSave($isNew);
    }


	 public function afterSave(bool $isNew)
    {
        $group = $this->getGroup();

        // Get the entry record
        if (!$isNew) {
            $record = EntryRecord::findOne($this->id);

            if (!$record) {
                throw new Exception('Invalid entry ID: '.$this->id);
            }
        } else {
            $record = new EventRecord();
            $record->id = $this->id;
        }

        $record->groupId = $this->groupId;
        $record->startDate = $this->startDate;
        $record->endDate = $this->endDate;
		$record->endRepeat = $this->endRepeat;
		$record->diff = $end->diff;
        $record->allDay = $this->allDay;
		$record->repeat = $this->repeat;
		$record->rRule = $this->rRule;
		$record->isrepeat = $this->isrepeat;
		$record->summary = $this->summary;
		$record->registration = $this->registration;
		$record->location = $this->location;
		$record->specificLocation = $this->specificLocation;
        $record->save(false);


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
			$canSetStatus = false;
			$canEdit = false;
			$canDelete = false;

			foreach ($groups as $groupId) {
				$canPublishEvents = $userSessionService->checkPermission('publishEvents:'.$groupId);
				$canDeleteEvents = $userSessionService->checkPermission('deleteEvents:'.$groupId);

				// Only show the Set Status action if we're sure they can make changes in all the groups
				if (!(
					$canPublishEvents && $canDeleteEvents
				))
				{
					$canSetStatus = false;
				}

				// Show the Edit action if they can publish changes to *any* of the groups
				// (the trigger will disable itself for events that aren't editable)
				if ($canPublishEvents) {
					$canEdit = true;
				}

				if($canDeleteEvents) {
					$canDelete = true;
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
                    if (isset($sections[0]->siteSettings[$siteId]) && $sections[0]->siteSettings[$siteId]->hasUrls) {
                        $showViewAction = true;
                    }
                }
            }

			if ($showViewAction) {
                // View
                $actions[] = Craft::$app->getElements()->createAction([
                    'type' => View::class,
                    'label' => Craft::t('venti', 'View event'),
                ]);
            }


			// Delete?
			if ($canDelete) {
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
			'repeat' => ['label' => Craft::t('venti', 'Repeat')],
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
		switch ($attribute)
		{
			case 'startDate':
			case 'endDate':
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
			case 'allDay':
			{
				if($this->allDay == 1)
				{
					return Craft::t('Venti','Yes');
				}
				else
				{
					return Craft::t('Venti','No');
				}
			}
			case 'group':
			{
				$group = $this->getGroup();
				$color = $group->getColor();
				return "<div class='group-label-color'><span class='menu-label-color' style='background-color:".$color.";'></span></div>";
			}
			case 'summary':
			{
				//$summary = "<a href='#view-dates'>". $element->$attribute . "</a>";
				$summary = $this->summary;
				return $this->summary != "" ? $summary : '';
			}

			$r = parent::tableAttributeHtml($attribute);
			return $r;
			
		}
	}



    /**
     * @inheritdoc
     */
    public function getEditorHtml(): string
	{

    	$namespacedId = Craft::$app->getView()->getNamespace();

		// $localeData = Craft::$app->getI18n()->getLocaleData(Craft::$app->language);
        // $dateFormatter = $localeData->getDateFormatter();
        $dateFormat = Craft::$app->getLocale()->getDateFormat('short');
        $timeFormat = Craft::$app->getLocale()->getTimeFormat('short');

		#-- Start/End Dates
		$html = Craft::$app->getView()->renderTemplate('venti/_editor', [
			'event' 			=> $this,
			'dateFormat' 		=> $dateFormat,
			'timeFormat' 		=> $timeFormat,
			'group' 			=> getGroupById($this->groupId),
			'namespacedId' 		=> $namespacedId,
			'permissionSuffix'  => ':'.getGroupById($this->groupId)
		]);

		#-- Everything else
		$html .= parent::getEditorHtml($this);

		return $html;
	}


	// Properties
    // =========================================================================

	/**
     * @var mixed|null Group 
     */
    public $group;
	/**
     * @var int|null Group ID
     */
    public $groupId;

	/**
     * @var datetime|null Start Date
     */
    public $startDate;
	/**
     * @var datetime|null End Date
     */
    public $endDate;
	/**
     * @var string|null rRule
     */
    public $rRule;
	/**
     * @var int|null Repeat
     */
    public $repeat;
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
     * @var int|null Is Repeat
     */
    public $isrepeat;
	/**
     * @var mixed|null Location
     */
    public $location;
	/**
     * @var string|null Specific Location
     */
    public $specificLocation;
	/**
     * @var string|null Summary
     */
    public $registration;




	/**
     * @inheritdoc
     */
    public function datetimeAttributes(): array
    {
        $names = parent::datetimeAttributes();
        $names[] = 'startDate';
        $names[] = 'endDate';

        return $names;
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


        $rules[] = [['groupId', 'repeat', 'allDay','diff','isrepeat'], 'number', 'integerOnly' => true];
        $rules[] = [['startDate', 'endDate','endRepeat'], DateTimeValidator::class];

        return $rules;
    }

	/**
     * @inheritdoc
     */
    public function getFieldLayout()
    {
        return $this->getGroup()->getFieldLayout();
    }

	/**
     * @inheritdoc
     */
    public function getSupportedSites(): array
    {
        $sites = [];

        foreach ($this->getGroup()->getGroupSiteSettings() as $siteSettings) {
            $sites[] = [
                'siteId' => $siteSettings->siteId,
                'enabledByDefault' => $siteSettings->enabledByDefault
            ];
        }

        return $sites;
    }

	 /**
     * @inheritdoc
     * @throws InvalidConfigException if [[siteId]] is not set to a site ID that the event's group is enabled for
     */
	// TODO: finish this out
    public function getUriFormat()
    {
        /*$sectionSiteSettings = $this->getGroup()->getSiteSettings();

        if (!isset($sectionSiteSettings[$this->siteId])) {
            throw new InvalidConfigException('Entry\'s section ('.$this->sectionId.') is not enabled for site '.$this->siteId);
        }

        return $sectionSiteSettings[$this->siteId]->uriFormat;*/

		return $this->getGroup()->getUrlFormat();
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
            throw new InvalidConfigException('Event is missing its group ID');
        }

        if (($group = Venti::getInstance()->groups->getGroupById($this->groupId)) === null) {
            throw new InvalidConfigException('Invalid event group ID: '.$this->groupId);
        }

        return $group;
    }



	/**
     * @inheritdoc
     */
    protected function route()
    {
        // Make sure that the event is actually live
        if ($this->getStatus() != Event::STATUS_LIVE) {
            return null;
        }

		$group = Venti::getInstance()->groups->getGroupById($this->groupId);

		$siteId = Craft::$app->getSites()->currentSite->id;
		$groupSiteSettings = $this->getGroup()->getGroupSiteSettings();


		if (!isset($groupSiteSettings[$siteId]) || !$groupSiteSettings[$siteId]->hasUrls) {
            return null;
        }

		return [
            'templates/render', [
                'template' => $groupSiteSettings[$siteId]->template,
                'variables' => [
                    'event' => $this,
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
    public function getIsEditable(): bool
    {
        return (
            Craft::$app->getUser()->checkPermission('ventiEditEvents:'.$this->groupId) && (
                $this->authorId == Craft::$app->getUser()->getIdentity()->id
            )
        );
    }



	/**
     * @inheritdoc
     */
    public function getCpEditUrl()
    {
        $group = $this->getGroup();

        // The slug *might* not be set if this is a Draft and they've deleted it for whatever reason
        $url = UrlHelper::cpUrl('events/'.$group->handle.'/'.$this->id.($this->slug ? '-'.$this->slug : ''));

        if (Craft::$app->getIsMultiSite() && $this->siteId != Craft::$app->getSites()->currentSite->id) {
            $url .= '/'.$this->getSite()->handle;
        }

        return $url;
    }

	public function excludedDates()
	{
		$datesDict = [];
		// if($this->repeat == true)
		// {
		// 	$exdates = getIncludedExcludedDates($this->rRule);
		// 	if ($exdates && array_key_exists('excludedDates',$exdates))
		// 	{
		// 		$datesDict = $exdates['excludedDates'];
		// 	}
		// }
		return $datesDict;
	}


	public function includedDates()
	{
		$datesDict = [];
		// if($this->repeat == true)
		// {
		// 	$rdates = getIncludedExcludedDates($this->rRule);
		// 	if ($rdates && array_key_exists('includedDates',$rdates))
		// 	{
		// 		$datesDict = $rdates['includedDates'];
		// 	}
		// }
		return $datesDict;
	}
}
