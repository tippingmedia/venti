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

use Craft;
use craft\base\Model;

/**
 * EventSettings Model
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
class EventSettings extends Model
{

    // Properties
    // =========================================================================

    /**
     * @var mixed|null groupSources
     */
    public $groupSources;

	/**
     * @var int|null limit
     */
    public $limit;

    /**
     * @var string|null eventSelectionLabel
     */
    public $eventSelectionLabel = Craft::t('Venti','Select an event');

    
}
