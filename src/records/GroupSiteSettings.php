<?php
/**
 * Venti plugin
 *
 *
 * @link      http://tippingmedia.com
 * @copyright Copyright (c) 2017 tippingmedia
 */

namespace tippingmedia\venti\records;

use tippingmedia\venti\Venti;

use Craft;
use craft\db\ActiveRecord;

/**
 * GroupLocale Record
 *
 * Active record models (or “records”) are like models, except with a
 * database-facing layer built on top. On top of all the things that models
 * can do, records can:
 *
 * - Define database table schemas
 * - Represent rows in the database
 * - Find, alter, and delete rows
 *
 * Note: Records’ ability to modify the database means that they should never
 * be used to transport data throughout the system. Their instances should be
 * contained to services only, so that services remain the one and only place
 * where system state changes ever occur.
 *
 * When a plugin is installed, Craft will look for any records provided by the
 * plugin, and automatically create the database tables for them.
 *
 * https://craftcms.com/docs/plugins/records
 *
 * @author    tippingmedia
 * @package   Venti
 * @since     2.0.0
 */
class GroupSiteSettings extends ActiveRecord
{
	// Public Methods
	// =========================================================================

	/**
	 * @inheritDoc BaseRecord::getTableName()
	 *
	 * @return string
	 */
	public static function tableName(): string
	{
		return '{{%venti_groups_i18n}}';
	}

	/**
     * Returns the associated group.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getGroup(): ActiveQueryInterface
    {
        return $this->hasOne(Group::class, ['id' => 'groupId']);
    }

    /**
     * Returns the associated site.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getSite(): ActiveQueryInterface
    {
        return $this->hasOne(Site::class, ['id' => 'siteId']);
    }
}
