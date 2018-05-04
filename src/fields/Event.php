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
use tippingmedia\venti\assetbundles\eventfield\EventFieldAsset;
use tippingmedia\venti\elements\VentiEvent;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\fields\BaseRelationField;
use craft\helpers\Db;
use yii\db\Schema;
use craft\helpers\Json;

/**
 * Event Field
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

class Event extends BaseRelationField
{

    //protected $inputTemplate = 'venti/fields/eventInput';

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('app', 'Events');
    }

    /**
     * @inheritdoc
     */
    public static function defaultSelectionLabel(): string
    {
        return Craft::t('app', 'Add an event');
    }

     /**
     * @inheritdoc
     */
    protected static function elementType(): string
    {
        return VentiEvent::class;
    }


    protected function inputSelectionCriteria(): array
    {
        return ['cpindex' => true];
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        /** @var Element|null $element */
        if ($element !== null && $element->hasEagerLoadedElements($this->handle)) {
            $value = $element->getEagerLoadedElements($this->handle);
        }

        /** @var ElementQuery|array $value */
        // Don't show event recurrences of elements
        $value->cpindex(true);
        $variables = $this->inputTemplateVariables($value, $element);

        return Craft::$app->getView()->renderTemplate($this->inputTemplate, $variables);
    }

}
