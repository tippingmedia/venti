<?php
/**
 * Venti plugin
 *
 *
 * @link      http://tippingmedia.com
 * @copyright Copyright (c) 2017 tippingmedia
 */

namespace tippingmedia\venti\fields;

use tippingmedia\venti\Venti;
//use tippingmedia\venti\assetbundles\eventfield\EventFieldAsset;
use tippingmedia\venti\elements\VentiEvent;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\fields\BaseOptionsField;
use craft\helpers\Db;
use yii\db\Schema;
use craft\helpers\Json;

/**
 * Group Field
 *
 * Whenever someone creates a new field in Craft, they must specify what
 * type of field it is. The system comes with a handful of field types baked in,
 * and we’ve made it extremely easy for plugins to add new ones.
 *
 * https://craftcms.com/docs/plugins/field-types
 *
 * @author    tippingmedia
 * @package   Venti
 * @since     3.0.0
 */

class Group extends Field
{

    public $calendarGroups;


    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('app', 'Calendar Groups');
    }

    /**
	 * @inheritdoc
	 * @see craft\base\Field
	 */
	public static function hasContentColumn(): bool
	{
		return true;
	}

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_TEXT;
    }

    /**
	 * @inheritdoc
	 * @see craft\base\Field
	 */
	public function rules(): array
	{
		$rules = parent::rules();
		
		$rules[] = [['calendarGroups'], 'required'];
		
		return $rules;
	}

    /**
	 * @inheritdoc
	 * @see craft\base\Field
	 */
	public function getInputHtml($value, ElementInterface $element = null): string
	{
        // Get groups based on input settings groups selected
        $groups = Venti::getInstance()->groups->getGroupsByIds($this->calendarGroups);
        $options = [];

        foreach ($groups as $group) {
            $options[] = ['label' => $group->name, 'value' => $group->id];
        }

		return Craft::$app->getView()->renderTemplate('venti/fields/groupField', [
			'field' => $this,
            'value' => $value,
            'options' => $options
		]);
    }
    
    /**
	 * @inheritdoc
	 * @see craft\base\SavableComponentInterface
	 */
	public function getSettingsHtml(): string
	{
		return Craft::$app->getView()->renderTemplate(
			'venti/fields/groupFieldSettings',
			[
                'field' => $this,
                'options' => $this->getOptions()
			]
		);
    }

    /**
     * @inheritdoc
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        return $value;
    }


    public function getOptions() {
        $groups = Venti::getInstance()->groups->getAllGroups();

        $options = [];
        
        foreach ($groups as $value) {
            array_push($options,[
                'label' => $value['name'],
                'value' => $value['id']
            ]);
        }

        return $options;
    }

}
