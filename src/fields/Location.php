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
use tippingmedia\venti\assetbundles\locationfield\LocationFieldAsset;
use tippingmedia\venti\elements\VentiLocation;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Db;
use yii\db\Schema;
use craft\helpers\Json;

/**
 * Location Field
 *
 * Whenever someone creates a new field in Craft, they must specify what
 * type of field it is. The system comes with a handful of field types baked in,
 * and we’ve made it extremely easy for plugins to add new ones.
 *
 * https://craftcms.com/docs/plugins/field-types
 *
 * @author    tippingmedia
 * @package   Venti
 * @since     2.0.0
 */
  //??? NEEDS TO BE AN ELEMENT FIELD TYPE ???
class Location extends BaseRelationField
{
	/**
	 * @access protected
	 * @var string $elementType The element type this field deals with.
	 */
	protected $elementType = 'VentiLocation';

     /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('app', 'Locations');
    }

    protected static function elementType(): string
    {
        return VentiLocation::class;
    }

     /**
     * @inheritdoc
     */
    public static function defaultSelectionLabel(): string
    {
        return Craft::t('app', 'Add a location');
    }


	// public function getSearchKeywords($value)
	// {

	// 	return "location city state zipCode region province coordinates website";
	// }

}
