<?php
/**
 * Venti plugin
 *
 *
 * @link      http://tippingmedia.com
 * @copyright Copyright (c) 2017 tippingmedia
 */

namespace tippingmedia\venti\records;

use craft\db\ActiveRecord;
use yii\db\ActiveQueryInterface;

/**
 * Group Record
 *
 * @author    tippingmedia
 * @package   Venti
 * @since     2.0.0
 */
class Group extends ActiveRecord
{
	/**
	 * @return string
	 */
	public static function tableName(): string
	{
		return '{{%venti_groups}}';
	}


     /**
     * Returns the associated site settings.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getGroupSiteSettings(): ActiveQueryInterface
    {
        return $this->hasMany(GroupSiteSettings::class, ['groupId' => 'id']);
    }


	public function getEvents(): ActiveQueryInterface
    {
        return $this->hasMany(VentiEvent::class, ['groupId' => 'id']);
    }

}
