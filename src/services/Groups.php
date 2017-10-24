<?php
/**
 * Venti plugin
 *
 *
 * @link      http://tippingmedia.com
 * @copyright Copyright (c) 2017 tippingmedia
 */

namespace tippingmedia\venti\services;

use tippingmedia\venti\Venti;
use tippingmedia\venti\models\Group;
use tippingmedia\venti\models\GroupSiteSettings;
use tippingmedia\venti\records\Group as GroupRecord;
use tippingmedia\venti\records\GroupSiteSettings as GroupSiteSettingsRecord;
use tippingmedia\venti\events\GroupEvent;
use tippingmedia\venti\elements\VentiEvent;

use Craft;
use craft\base\Element;
use craft\db\Query;
use craft\queue\jobs\ResaveElements;

use yii\base\Component;
use yii\base\Exception;
use yii\helpers\VarDumper;

/**
 * Groups Service
 *
 *
 * @author    tippingmedia
 * @package   Venti
 * @since     2.0.0
 */
class Groups extends Component
{
	
	// Constants
    // =========================================================================

    /**
     * @event EventGroup The event that is triggered before a group is saved.
     */
    const EVENT_BEFORE_SAVE_GROUP = 'beforeSaveGroup';

    /**
     * @event EventGroup The event that is triggered after a group is saved.
     */
    const EVENT_AFTER_SAVE_GROUP = 'afterSaveGroup';

	/**
     * @event EventGroup The event that is triggered before a group is deleted.
     */
    const EVENT_BEFORE_DELETE_GROUP = 'beforeDeleteGroup';

    /**
     * @event EventGroup The event that is triggered after a group is deleted.
     */
    const EVENT_AFTER_DELETE_GROUP = 'afterDeleteGroup';


	// Properties
    // =========================================================================
	private $_allGroupIds;
	private $_groupsById;
	private $_fetchedAllGroups = false;
	private $_editableGroupIds;

	/**
	 * Returns all of the group IDs.
	 *
	 * @return array
	 */

	public function getAllGroupIds() 
	{
		if ($this->_allGroupIds !== null) {
			return $this->_allGroupIds;
		}
		$this->_allGroupIds = [];

		foreach($this->getAllGroups() as $group) {
			$this->_allGroupIds[] = $group->id;
		}

		return $this->_allGroupIds;
	}

	/**
     * Returns all of the group IDs that are editable by the current user.
     *
     * @return array All the editable groups’ IDs.
     */
    public function getEditableGroupIds(): array
    {
        if ($this->_editableGroupIds !== null) {
            return $this->_editableGroupIds;
        }

        $this->_editableGroupIds = [];

        foreach ($this->getAllGroupIds() as $groupId) {
            if (Craft::$app->getUser()->checkPermission('editGroupEvents:'.$groupId)) {
                $this->_editableGroupIds[] = $groupId;
            }
        }

        return $this->_editableGroupIds;
    }

	/**
	 * Returns all groups.
	 *
	 * @return Group[] All the groups.
	 */
	public function getAllGroups(): array
	{

		if ($this->_fetchedAllGroups) {
			return array_values($this->_groupsById);
		}

		$results = $this->_createGroupQuery()
			->all();
		
		$this->_groupsById = [];

		foreach ($results as $result) {
            $group = new Group($result);
            $this->_groupsById[$group->id] = $group;
        }

        $this->_fetchedAllGroups = true;

        return array_values($this->_groupsById);

	}


	/**
     * Returns all editable groups.
     *
     * @return Group[] All the editable groups.
     */
	public function getEditableGroups(): array 
	{
		$editableGroupIds = $this->getEditableGroupIds();
		$editableGroups = [];

		foreach ($this->getAllGroups() as $group) {
            if (in_array($group->id, $editableGroupIds, false)) {
                $editableGroups[] = $group;
            }
        }

        return $editableGroups;
	}


	/**
	 * Gets the total number of groups.
	 *
	 * @return int
	 */
	public function getTotalGroups(): int
	{
		return count($this->getAllGroupIds());
	}

	public function getTotalEditableGroups(): int 
	{
		return count($this->getEditableGroupIds());
	}

	/**
	 * Returns a group by its ID.
	 *
	 * @param $calendarId
	 * @return Group|null
	 */
	public function getGroupById(int $groupId) 
	{

		if(!$groupId) {
			return null;
		}

		if($this->_groupsById !== null && array_key_exists($groupId, $this->_groupsById)) {
			return $this->_groupsById[$groupId];
		}

		// If we've already fetched all groups we can save ourselves a trip to
        // the DB for section IDs that don't exist
        if ($this->_fetchedAllGroups) {
            return null;
        }

		$result = $this->_createGroupQuery()
			->where(['groups.id' => $groupId])
			->one();

		if (!$result) {
			return $this->_groupsById[$groupId] = null;
		}

		return $this->_groupsById[$groupId] = new Group($result);

	}

	/**
	 * Gets a group by its handle.
	 *
	 * @param string $groupHandle
	 * @return Group|null
	 */
	public function getGroupByHandle(string $groupHandle) 
	{
		$result = $this->_createGroupQuery()
			->where(['groups.handle' => $groupHandle])
			->one();


		if ($result) {
			$group = new Group($result);
			$this->_groupsById[$group->id] = $group;
			return $group;
		}

		return null;
	}

	/**
	 * Saves a group.
	 *
	 * @param Group $group
	 * @throws \Exception
	 * @return bool
	 */
	public function saveGroup(Group $group, bool $runValidation = true): bool
	{
		$isNewGroup = !$group->id;

		// Fire a 'beforeSaveGroup' event
        $this->trigger(self::EVENT_BEFORE_SAVE_GROUP, new GroupEvent([
            'group' => $group,
            'isNew' => $isNewGroup
        ]));

		if ($runValidation && !$group->validate()) {
            Craft::info('venti','Group not saved due to validation error.', __METHOD__);

            return false;
        }

		if (!$isNewGroup) {
			$groupRecord = GroupRecord::find()
				->where(['id' => $group->id])
				->one();

			if (!$groupRecord) {
				throw new Exception(Craft::t('venti',"No group exists with the ID '{$group->id}'"));
			}

			$oldGroup = new Group($groupRecord->toArray([
				'id',
				'name',
				'handle',
				'color',
				'description',
			]));
		
		} else {
			$groupRecord = new GroupRecord();
		}

		// Main group settings
        /** @var GroupRecord $groupRecord */
		$groupRecord->name = $group->name;
		$groupRecord->handle = $group->handle;
		$groupRecord->color = $group->color;
		$groupRecord->description = $group->description;


		// Make sure that all of the URL formats are set properly
		$allGroupSiteSettings = $group->getGroupSiteSettings();


		if (empty($allGroupSiteSettings)) {
			throw new Exception('Tried to save a Venti event group without any site settings');
		}
	

        $db = Craft::$app->getDb();
        $transaction = $db->beginTransaction();

		try {

			if (!$isNewGroup && $oldGroup->fieldLayoutId) {
				$fieldLayout = $oldGroup->getFieldLayout();
				if ($fieldLayout->type != "Venti_Event_Default") {
					// Drop the old field layout
					Craft::$app->getFields()->deleteLayoutById($oldGroup->fieldLayoutId);
				}
			}

			if (isset($group->fieldLayoutId) && $group->fieldLayoutId == null) {
				$fieldLayout = $group->getFieldLayout();
				// Save the new one
				Craft::$app->getFields()->saveLayout($fieldLayout);
				// Update the group record/model with the new layout ID
				$group->fieldLayoutId = $fieldLayout->id;
				$groupRecord->fieldLayoutId = $fieldLayout->id;

			} else {
				$groupRecord->fieldLayoutId = $group->fieldLayoutId;
			}


			// Save it!
			$groupRecord->save(false);


			// Now that we have a group ID, save it on the model
			if ($isNewGroup) {
				$group->id = $groupRecord->id;
			}

			// Might as well update our cache of the group while we have it.
			$this->_groupsById[$group->id] = $group;


			if (!$isNewGroup) {
				// Get the old group sites
				$allOldGroupSiteSettingsRecords = GroupSiteSettingsRecord::find()
					->where(['groupId' => $group->id])
					->indexBy('siteId')
					->all();
			} else {
				$allOldGroupSiteSettingsRecords = [];
			}

			foreach ($allGroupSiteSettings as $siteId => $groupSiteSettings) {
				// Was this already selected?
				if (!$isNewGroup && isset($allOldGroupSiteSettingsRecords[$siteId])) {
					$groupSiteSettingsRecord = $allOldGroupSiteSettingsRecords[$siteId];
				} else {
					$groupSiteSettingsRecord = new GroupSiteSettingsRecord();
					$groupSiteSettingsRecord->groupId = $group->id;
					$groupSiteSettingsRecord->siteId = $groupSiteSettings->siteId;
					//$newSiteData[] = [$group->id, $siteId, (int)$groupSiteSettings->enabledByDefault, $groupSiteSettings->uriFormat];
				}

				$groupSiteSettingsRecord->enabledByDefault = $groupSiteSettings->enabledByDefault;
				$groupSiteSettingsRecord->hasUrls = $groupSiteSettings->hasUrls;
				$groupSiteSettingsRecord->uriFormat = $groupSiteSettings->uriFormat;
				$groupSiteSettingsRecord->template = $groupSiteSettings->template;


				$groupSiteSettingsRecord->save(false);

				// Set the ID on the model
				$groupSiteSettings->id = $groupSiteSettingsRecord->id;
			}


			if (!$isNewGroup) {
				// Drop any sites that are no longer being used, as well as the associated entry/element site
				// rows

				$siteIds = array_keys($allGroupSiteSettings);

				foreach($allOldGroupSiteSettingsRecords as $siteId => $groupSiteSettingsRecord) {
					if(!in_array($siteId, $siteIds, false)){
						$groupSiteSettingsRecord->delete();
					}
				}
			}

			
			// Finally, deal with the existing events...

			if (!$isNewGroup) {
				
				// Get the most-primary site that this group was already enabled in
				$siteIds = array_values(array_intersect(Craft::$app->getSites()->getAllSiteIds(), array_keys($allOldGroupSiteSettingsRecords)));

				if (!empty($siteIds)) {
					Craft::$app->getQueue()->push(new ResaveElements([
						'description' => Craft::t('venti','Resaving {group} events', ['group' => $group->name]),
						'elementType' => VentiEvent::class,
						'criteria' => [
							'siteId' => $siteIds[0],
							'groupId' => $group->id,
							'status' => null,
							'enabledForSite' => false,
							'limit' => null,
						]
					]));
				}
			}

			$transaction->commit();
		} catch (\Exception $e) {
			$transaction->rollback();

			throw $e;
		}

		// Fire an 'afterSaveGroup' event
        $this->trigger(self::EVENT_AFTER_SAVE_GROUP, new GroupEvent([
            'group' => $group,
            'isNew' => $isNewGroup
        ]));


		return true;
	}

	/**
	 * Deletes a group by its ID.
	 *
	 * @param int $groupId
	 * @throws \Exception
	 * @return bool
	 */
	public function deleteGroupById(int $groupId): bool 
	{
		$group = $this->getGroupById($groupId);

		if(!$group) {
			return false;
		}

		return $this->deleteGroup($group);
	}

 	/**
     * Deletes a group.
     *
     * @param Group $group
     *
     * @return bool Whether the group was deleted successfully
     * @throws \Exception if reasons
     */
    public function deleteGroup(Group $group): bool
    {
		// Fire a 'beforeDeleteGroup' event
        $this->trigger(self::EVENT_BEFORE_DELETE_GROUP, new GroupEvent([
            'group' => $group
        ]));

		$transaction = Craft::$app->getDb()->beginTransaction();
        try {
            
            // Delete the field layout(s)
            $fieldLayoutIds = (new Query())
                ->select(['fieldLayoutId'])
                ->from(['{{%venti_groups}}'])
                ->where(['id' => $group->id])
                ->column();

            if (!empty($fieldLayoutIds)) {
				if(!in_array(null,$fieldLayoutIds)) {
					Craft::$app->getFields()->deleteLayoutById($fieldLayoutIds);
				}
            }

            // Delete the events
            $events = VentiEvent::find()
                ->status(null)
                ->enabledForSite(false)
                ->groupId($group->id)
                ->all();

            foreach ($events as $event) {
                Craft::$app->getElements()->deleteElement($event);
            }


            // Delete the group.
            Craft::$app->getDb()->createCommand()
                ->delete('{{%venti_groups}}', ['id' => $group->id])
                ->execute();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();

            throw $e;
        }

        // Fire an 'afterDeleteGroup' event
        $this->trigger(self::EVENT_AFTER_DELETE_GROUP, new GroupEvent([
            'group' => $group
        ]));

        return true;
	}

	

	/**
	 * Returns group models found by fieldLayoutId.
	 *
	 * @param int         $layoutId
	 *
	 * @return GroupModel[].
	 */

	public function getGroupsByLayoutId($layoutId) 
	{
		
		$query = (new Query())
			->select('*')
			->from('venti_groups venti_groups')
			->where('venti_groups.fieldLayoutId = :fieldLayoutId', [':fieldLayoutId' => $layoutId])
			->all();

		return $query;
	}


	/**
	 * Save groups with new default field layout id.
	 *
	 * @param array         $groups
	 * @param int           $newLayoutId
	 *
	 * @return bool
	 */
	public function updateGroupLayoutIds($groups, $newLayoutId) 
	{
		$success = true;

		if ($groups) {
			foreach ($groups as $group) {
				$group->fieldLayoutId = $newLayoutId;
				if ($this->saveGroup($group)) {
					$success = true;
				}
			}
		}

		return $success;
	}

	/**
	 * Returns a groups’s sites.
	 *
	 * @param int         $groupId
	 * @param string|null $indexBy
	 *
	 * @return Group[] The group’s sites.
	 */
	public function getGroupSiteSettings(int $groupId): array
	{

		$groupSiteSettings = (new Query())
			->select([
				'venti_groups_sites.id',
				'venti_groups_sites.groupId',
				'venti_groups_sites.siteId',
				'venti_groups_sites.enabledByDefault',
				'venti_groups_sites.hasUrls',
				'venti_groups_sites.uriFormat',
				'venti_groups_sites.template'
			])
			->from(['{{%venti_groups_sites}} venti_groups_sites'])
            ->innerJoin('{{%sites}} sites', '[[sites.id]] = [[venti_groups_sites.siteId]]')
            ->where(['venti_groups_sites.groupId' => $groupId])
            ->orderBy(['sites.sortOrder' => SORT_ASC])
            ->all();

        foreach ($groupSiteSettings as $key => $value) {
            $groupSiteSettings[$key] = new GroupSiteSettings($value);
        }

        return $groupSiteSettings;
		
	}



	public function getRoutesFromFormats() 
	{
		$groups = $this->getAllGroups();
		$routes = [];

		foreach ($groups as $group) {
			if ($group && $group->hasUrls) {
				$template = $group->template;
				$groupSiteSettings = $group->getSiteSettings();

				if ($groupSiteSettings) {
					foreach ($groupSiteSettings as $glocale) {
						$routes[$groupSiteSettings[$glocale->siteId]->urlFormat] = $template;
					}
				}
			}
		}

		return $routes;
	}



	/**
     * Returns a Query object prepped for retrieving groups.
     *
     * @return Query
     */
    private function _createGroupQuery(): Query
    {
        return (new Query())
            ->select([
                'groups.id',
                'groups.name',
                'groups.handle',
				'groups.description',
                'groups.color',
            ])
            ->from(['{{%venti_groups}} groups'])
            ->orderBy(['name' => SORT_ASC]);
    }
	
}
