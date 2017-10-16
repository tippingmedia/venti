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
use tippingmedia\venti\records\Group as GroupRecord;
use tippingmedia\venti\services\Groups;
use tippingmedia\venti\elements\VentiEvent;

use Craft;
use craft\base\Model;
use craft\helpers\ArrayHelper;
use craft\validators\HandleValidator;
use craft\validators\UniqueValidator;
use craft\helpers\UrlHelper;
use craft\behaviors\FieldLayoutBehavior;
use craft\behaviors\FieldLayoutTrait;

/**
 * Group Model
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
class Group extends Model
{

	// Properties
    // =========================================================================

    /**
     * @var int|null ID
     */
    public $id;

    /**
     * @var string|null Name
     */
    public $name;

    /**
     * @var string|null Handle
     */
    public $handle;

	/**
     * @var string|#ffffff color
     */
    public $color = "#ffffff";

	/**
     * @var mixed|null description
     */
    public $description;

    /**
     * @var mixed|null description
     */
    public $fieldLayoutId;

    /**
     * @var
     */
    private $_groupSiteSettings;


	/**
	 * @var
	 */
	private $_sites;


	// Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'number', 'integerOnly' => true],
            [['handle'], HandleValidator::class, 'reservedWords' => ['id', 'dateCreated', 'dateUpdated', 'uid', 'title']],
            [['name', 'handle'], UniqueValidator::class, 'targetClass' => GroupRecord::class],
            [['name', 'handle','groupSiteSettings'], 'required'],
            [['name', 'handle'], 'string', 'max' => 255],
        ];
    }


    public function validate($attributeNames = null, $clearErrors = true)
    {
        $validates = parent::validate($attributeNames, $clearErrors);

        if ($attributeNames === null || in_array('groupSiteSettings', $attributeNames, true)) {
            foreach ($this->getGroupSiteSettings() as $groupSiteSettings) {
                if (!$groupSiteSettings->validate(null, $clearErrors)) {
                    $validates = false;
                }
            }
        }

        return $validates;
    }


	/**
	 * Use the translated group name as the string representation.
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return Craft::t('site', $this->name);
	}


	    /**
     * Returns the group's site-specific settings.
     *
     * @return Group_SiteSettings[]
     */
    public function getGroupSiteSettings(): array
    {
        if ($this->_groupSiteSettings !== null) {
            return $this->_groupSiteSettings;
        }

        if (!$this->id) {
            return [];
        }

        $groups = new Groups();

        // Set them with setGroupSiteSettings() so setGroup() gets called on them
        $this->setGroupSiteSettings(ArrayHelper::index($groups->getGroupSiteSettings($this->id), 'siteId'));


        return $this->_groupSiteSettings;
    }

    /**
     * Sets the group's site-specific settings.
     *
     * @param Group_SiteSettings[] $siteSettings
     *
     * @return void
     */
    public function setGroupSiteSettings(array $siteSettings)
    {
        $this->_groupSiteSettings = $siteSettings;

        foreach ($this->_groupSiteSettings as $settings) {
            $settings->setGroup($this);
        }
    }

    /**
     * Adds site-specific errors to the model.
     *
     * @param array $errors
     * @param int   $siteId
     *
     * @return void
     */
    public function addSiteSettingsErrors(array $errors, int $siteId)
    {
        foreach ($errors as $attribute => $siteErrors) {
            $key = $attribute.'-'.$siteId;
            foreach ($siteErrors as $error) {
                $this->addError($key, $error);
            }
        }
    }



	/**
	 * Returns the group's URL format (or URL) for the current site.
	 *
	 * @return string|null
	 */
	public function getUrlFormat()
	{
		$sites = $this->getGroupSiteSettings();

		if ($sites)
		{
			$siteIds = array_keys($sites);

			// Does this group target the current site?
			if (in_array(Craft::$app->getSites()->getAllSiteIds(), $siteIds))
			{
				$siteId = Craft::$app->getSites()->currentSite->id;
			}
			else
			{
				$siteId = $siteIds[0];
			}

			return $sites[$siteId]->uriFormat;
		}
	}


	public function getICSUrl()
	{
		return UrlHelper::siteUrl() . "calendar/ics/" . $this->id;
	}



	public function behaviors()
    {
        return [
            'fieldLayout' => [
                'class' => FieldLayoutBehavior::class,
                'elementType' => VentiEvent::class
            ],
        ];
    }
}
