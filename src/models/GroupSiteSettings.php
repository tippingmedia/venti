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
use tippingmedia\venti\services\Groups;

use Craft;
use craft\base\Model;
use craft\validators\SiteIdValidator;
use craft\validators\UriFormatValidator;
use yii\base\InvalidConfigException;

/**
 * GroupSiteSettings Model
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

class GroupSiteSettings extends Model
{
	// Properties
	// =========================================================================
    /**
     * @var int|null ID
     */
    public $id;

    /**
     * @var int|null Group ID
     */
    public $groupId;

    /**
     * @var int|null Site ID
     */
    public $siteId;

    /**
     * @var bool Enabled by default
     */
    public $enabledByDefault = true;

    /**
     * @var bool|null Has URLs?
     */
    public $hasUrls = true;

    /**
     * @var string|null URI format
     */
    public $uriFormat;

    /**
     * @var string|null Entry template
     */
    public $template;

    /**
     * @var Section|null
     */
    private $_group;



	// Public Methods
	// =========================================================================


	/**
     * Returns the group.
     *
     * @return group
     * @throws InvalidConfigException if [[groupId]] is missing or invalid
     */
    public function getGroup(): Group
    {
        if ($this->_group !== null) {
            return $this->_group;
        }

        if (!$this->groupId) {
            throw new InvalidConfigException('Group site settings model is missing its group ID');
        }

        if (($this->_group = getGroupById($this->groupId)) === null) {
            throw new InvalidConfigException('Invalid group ID: '.$this->groupId);
        }

        return $this->_group;
    }


	/**
     * Sets the group.
     *
     * @param Group $group
     *
     * @return void
     */
    public function setGroup(Group $group)
    {
        $this->_group = $group;
    }


	/**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = [
            'template' => Craft::t('venti', 'Template'),
        ];

        $labels['uriFormat'] = Craft::t('venti', 'Event URI Format');        

        return $labels;
    }

	/**
	 * @inheritDoc BaseModel::rules()
	 *
	 * @return array
	 */
	public function rules()
	{
		$rules = [
            [['id', 'groupId', 'siteId'], 'number', 'integerOnly' => true],
            [['siteId'], SiteIdValidator::class],
            [['template'], 'string', 'max' => 500],
        ];

		$rules[] = ['uriFormat', UriFormatValidator::class];

		if ($this->hasUrls) {
            $rules[] = [['uriFormat'], 'required'];
        }

        return $rules;
	}
}
