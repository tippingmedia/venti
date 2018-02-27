<?php
/**
 * Venti plugin
 *
 *
 * @link      http://tippingmedia.com
 * @copyright Copyright (c) 2017 tippingmedia
 */

namespace tippingmedia\venti\models;

use tippingmedia\venti\Venti;
use tippingmedia\venti\models\Group;
use tippingmedia\venti\services\Rule;
use tippingmedia\venti\services\Groups;
use tippingmedia\venti\elements\VentiEvent;
use tippingmedia\venti\elements\Location;

use Craft;
use craft\base\Model;
use DateTime;
use DateTimeZone;

use craft\i18n\Formatter;
use craft\i18n\Locale;

/**
 * Event Model
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    tippingmedia
 * @package   Venti
 * @since     2.0.0
 */

// ??? FIND OUT ABOUT ELEMENTMODEL WHAT TO USE NOW ???
class Event extends Model
{

	// Constants
    // =========================================================================
	const LIVE    = 'live';
	const EXPIRED  = 'expired';

	protected $elementType = 'VentiEvent';


	// Properties
    // =========================================================================
	/**
     * @var int|null Element ID
     */
    public $id;

	/**
     * @var mixed|null Group 
     */
    public $group;
	/**
     * @var int|null Group ID
     */
    public $groupId;
	/**
     * @var int|null Site ID
     */
    public $siteId;
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
     * @var int|null recurring
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
     * @var int|null endRepeat
     */
    public $endRepeat;
	/**
     * @var int|null diff
     */
    public $diff;
	/**
     * @var int|null IS RECURRIN
     */
    public $isrecurring;
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
	 * @access protected
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array_merge(parent::defineAttributes(), array(
			'id'         		=> AttributeType::Number,
			'elementId'  		=> AttributeType::Number,
			'groupId' 	 		=> AttributeType::Number,
			'startDate'  		=> array(AttributeType::DateTime, 'required'=> true),
			'endDate'    		=> array(AttributeType::DateTime, 'required'=> true, 'compare' => '>startDate'),
			'endRepeat'  		=> array(AttributeType::DateTime),
            'allDay'     		=> AttributeType::Number,
            'recurring'     	=> AttributeType::Number,
			'diff'     			=> AttributeType::Number,
			'isrecurring'     	=> AttributeType::Number,
            'rRule'      		=> AttributeType::String,
            'summary'    		=> AttributeType::String,
			'location'   		=> Attributetype::Mixed,
			'specificLocation' 	=> AttributeType::String,
			'registration' 		=> AttributeType::Mixed,
		));
	}

	/**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id','elementId,recurring,allDay'], 'number', 'integerOnly' => true],
            [['handle'], HandleValidator::class, 'reservedWords' => ['id', 'dateCreated', 'dateUpdated', 'uid', 'title']],
            [['name', 'handle'], UniqueValidator::class, 'targetClass' => GroupRecord::class],
            [['name', 'handle'], 'string', 'max' => 255],
			[['startDate'], 
				CompareValidator::class, 
				'compareAttribute' => '{endDate}', 
				'operator'=>'<',
				'message' => Craft::t('Venti','{attribute} must be less than {compareAttribute}.')
			]
        ];
    }


	/**
	 * @inheritDoc BaseModel::getAttribute()
	 *
	 * @param string $name
	 * @param bool   $flattenValue
	 *
	 * Updates location variable to return Venti_Location element criteria model else return normal.
	 * @return mixed
	 */
	public function getAttribute($name, $flattenValue = false)
	{

		if($name === 'location' && is_array(parent::getAttribute('location')) && Craft::$app->getRequest()->isSiteRequest())
		{
			
			$location = VentiLocation::find()
    			->id(parent::getAttribute('location')[0])
    			->localeEndabled(null)
    			->one();
	        return $location;
		}

		if($name === 'uri' && parent::getAttribute('uri') !== "")
		{
			$urlFormat = $this->getUrlFormat();
			$path = Craft::$app->getView()->renderObjectTemplate($urlFormat, $this);
			//$uri = UrlHelper::getSiteUrl($path, null, null, $this->locale);

			return $path;
		}

	    return parent::getAttribute($name, $flattenValue);
	}


	/**
	 * Returns whether the current user can edit the element.
	 *
	 * @return bool
	 */
	public function isEditable()
	{
		return true;
	}

	/**
	 * @inheritDoc BaseElementModel::getCpEditUrl()
	 *
	 * @return string|false
	 */
	public function getCpEditUrl()
	{
		$group = $this->getGroup();

		if ($group)
		{
			// The slug *might* not be set if this is a Draft and they've deleted it for whatever reason
			$url = UrlHelper::getCpUrl('venti/'.$group->handle.'/'.$this->id.($this->slug ? '-'.$this->slug : ''));

			if (craft()->isLocalized() && $this->locale != craft()->language)
			{
				$url .= '/'.$this->locale;
			}

			return $url;
		}
	}

	/**
	 * Returns the field layout used by this element.
	 *
	 * @return FieldLayoutModel|null
	 */
	public function getFieldLayout()
	{
		$group = $this->getGroup();

		if ($group)
		{
			return $group->getFieldLayout();
		}
	}


	/**
	 * Returns the event's group.
	 *
	 * @return Venti_GroupModel|null
	 */
	public function getGroup()
	{
		if ($this->groupId)
		{
			return getGroupById($this->groupId);
		}
	}

	/**
	 * Returns the event's group.
	 *
	 * @return Venti_GroupModel|null
	 */
	public function group()
	{
		if ($this->groupId)
		{
			return getGroupById($this->groupId);
		}
	}

    /**
	 * Returns the event's group color.
	 *
	 * @return String|null
	 */
	public function getColor()
	{
		if ($this->groupId)
		{
			$group = getGroupById($this->groupId);
            return $group->color;
		}
	}


	/**
	 * Returns location model
	 * @access public
	 * @return Venti_LocationModel
	 */
	public function getLocation()
	{
		$locValue = is_array($this->location) ? $this->location : false;
		$location = $event = VentiLocation::find()
    		->id($locValue)
    		->localeEndabled(null)
    		->one();

		return $location;
	}


	public function excludedDates()
	{
		$datesDict = array();
		if($this->recurring == true)
		{
			$exdates = getIncludedExcludedDates($this->rRule);
			if ($exdates && array_key_exists('excludedDates',$exdates))
			{
				$datesDict = $exdates['excludedDates'];
			}
		}
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



	/**
	 * @inheritDoc BaseElementModel::getLocales()
	 *
	 * @return array
	 */
	public function getLocales()
	{
		$locales = array();
		foreach ($this->getGroup()->getLocales() as $locale)
		{
			$locales[$locale->locale] = array('enabledByDefault' => $locale->enabledByDefault);
		}

		return $locales;
	}



	/**
	 * @inheritDoc BaseElementModel::getUrlFormat()
	 *
	 * @return string|null
	 */
	public function getUrlFormat()
	{
		$group = $this->getGroup();

		if ($group && $group->hasUrls)
		{
			$groupLocales = $group->getLocales();

			if (isset($groupLocales[$this->locale]))
			{

				return $groupLocales[$this->locale]->urlFormat;

			}
		}
	}



	/**
	 * Returns the reference string to this element.
	 *
	 * @return string|null
	 */
	public function getRef()
	{
		return $this->getGroup()->handle.'/'.$this->id."-".$this->slug;
	}




	/**
	 * @inheritDoc BaseElementModel::getStatus()
	 *
	 * @return string|null
	 */
	public function getStatus()
	{
		$status = parent::getStatus();
        if ($status == static::ENABLED)
		{
			$currentTime = DateTimeHelper::currentTimeStamp();
			$endDate    = $this->endDate->getTimestamp();

			return static::LIVE;

			// if ($endDate >= $currentTime)
			// {
			// 	return static::LIVE;
			// }
			// else
			// {
			// 	return static::EXPIRED;
			// }
		}

		return $status;
	}
}
