<?php
namespace tippingmedia\venti\elements\db;

use tippingmedia\venti\Venti;
use tippingmedia\venti\models\Group;
use tippingmedia\venti\models\Event;
use tippingmedia\venti\models\Location;
use tippingmedia\venti\services\Groups;

use craft\db\Query;
use craft\elements\db\ElementQuery;
use craft\db\QueryAbortedException;
use craft\elements\Entry;
use craft\helpers\ArrayHelper;
use craft\helpers\Db;
use DateTime;
use yii\db\Connection;


class VentiEventQuery extends ElementQuery
{
     /**
     * @var bool Whether to only return entries that the user has permission to edit.
     */
    public $editable = false;
    public $siteId;
    public $groupId;
    public $startDate;
    public $endDate;
    public $endRepeat;
    public $rRule;
    public $diff;
    public $repeat;
    public $isrepeat;
    public $allDay;
    public $summary;
    public $location;
    public $specificLocation;
    public $registration;
    public $between;


    public function __construct($elementType, array $config = [])
    {
        // Default status
        if (!isset($config['status'])) {
            $config['status'] = 'live';
        }

        parent::__construct($elementType, $config);
    }

    /**
     * @inheritdoc
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'group':
                $this->group($value);
                break;
            default:
                parent::__set($name, $value);
        }
    }

    /**
     * Sets the [[editable]] property.
     *
     * @param bool $value The property value (defaults to true)
     *
     * @return static self reference
     */
    public function editable(bool $value = true)
    {
        $this->editable = $value;

        return $this;
    }


    /**
     * Sets the [[groupId]] property based on a given group(s)’s handle(s).
     *
     * @param string|string[]|group|null $value The property value
     *
     * @return static self reference
     */
    public function group($value)
    {
        if ($value instanceof Group) {
            $this->groupId = $value->id;
        } else if ($value !== null) {
            $this->groupId = (new Query())
                ->select(['id'])
                ->from(['{{%venti_groups}} groups'])
                ->where(Db::parseParam('groups.handle', $value))
                ->column();
        } else {
            $this->groupId = null;
        }

        return $this;
    }


    /**
     * Sets the [[groupId]] property.
     *
     * @param int|int[]|null $value The property value
     *
     * @return static self reference
     */
    public function groupId($value)
    {
        $this->groupId = $value;

        return $this;
    }


    /**
     * Sets the [[eid]] property.
     *
     * @param int|int[]|null $value The property value
     *
     * @return static self reference
     */
    public function eid($value)
    {
        $this->eid = $value;

        return $this;
    }


    /**
     * Sets the [[cid]] property.
     *
     * @param int|int[]|null $value The property value
     *
     * @return static self reference
     */
    public function cid($value)
    {
        $this->cid = $value;

        return $this;
    }

    /**
     * Sets the [[startDate]] property.
     *
     * @param DateTime|string $value The property value
     *
     * @return static self reference
     */
    public function startDate($value)
    {
        $this->startDate = $value;

        return $this;
    }

    /**
     * Sets the [[endDate]] property.
     *
     * @param DateTime|string $value The property value
     *
     * @return static self reference
     */
    public function endDate($value)
    {
        $this->endDate = $value;

        return $this;
    }

    /**
     * Sets the [[rRule]] property.
     *
     * @param $string|null $value The property value
     *
     * @return static self reference
     */
    public function rRule($value)
    {
        $this->rRule = $value;

        return $this;
    }

    /**
     * Sets the [[repeat]] property.
     *
     * @param $int|null $value The property value
     *
     * @return static self reference
     */
    public function repeat($value)
    {
        $this->repeat = $value;

        return $this;
    }
    
    /**
     * Sets the [[allDay]] property.
     *
     * @param $int|null $value The property value
     *
     * @return static self reference
     */
    public function allDay($value)
    {
        $this->allDay = $value;

        return $this;
    }

    /**
     * Sets the [[summary]] property.
     *
     * @param $string|null $value The property value
     *
     * @return static self reference
     */
    public function summary($value)
    {
        $this->summary = $value;

        return $this;
    }

    /**
     * Sets the [[isrepeat]] property.
     *
     * @param $int|null $value The property value
     *
     * @return static self reference
     */
    public function isrepeat($value)
    {
        $this->isrepeat = $value;

        return $this;
    }

    /**
     * Sets the [[location]] property.
     *
     * @param int|int[]|null $value The property value
     *
     * @return static self reference
     */
    public function location($value)
    {
        $this->location = $value;

        return $this;
    }

    /**
     * Sets the [[specificLocation]] property.
     *
     * @param $string|null $value The property value
     *
     * @return static self reference
     */
    public function specificLocation($value)
    {
        $this->specificLocation = $value;

        return $this;
    }

    /**
     * Sets the [[registration]] property.
     *
     * @param $mixed|null $value The property value
     *
     * @return static self reference
     */
    public function registration($value)
    {
        $this->registration = $value;

        return $this;
    }  



    /**
     * @inheritdoc
     */
    protected function beforePrepare(): bool
    {

        //Craft::$app->getDb()->createCommand('CREATE TEMPORARY TABLE temp_venti_events (elementId,startDate,endDate)')->execute();
        // See if 'group' is set to invalid handle
        if ($this->groupId === []) {
            return false;
        }

        $this->joinElementTable('venti_events');
        //$this->query->rightJoin('{{%venti_recurr recurr}}','[[recurr.cid]] = [[venti_events.id]]');

        $this->query->select([
            'venti_events.id',
            'venti_events.groupId',
            'venti_events.allDay',
            'venti_events.rRule',
            'venti_events.repeat',
            'venti_events.summary',
            'venti_events.siteId',
            'venti_events.location',
            'venti_events.specificLocation',
            'venti_events.registration',
            'venti_events.startDate',
            'venti_events.endDate',
            'venti_events.endRepeat',
            'venti_events.diff'
        ]);

        //$this->query->group('recurr.eid');

        if ($this->groupId) {
            $this->subQuery->andWhere(Db::parseParam('venti_events.groupId', $this->groupId));
        }

        if ($this->startDate) {
            $this->subQuery->andWhere(Db::parseDateParam('venti_events.startDate', $this->startDate));
        }

        if ($this->endDate) {
            $this->subQuery->andWhere(Db::parseDateParam('venti_events.endDate', $this->endDate));
        }

        if ($this->endRepeat) {
            $this->subQuery->andWhere(Db::parseDateParam('venti_events.endRepeat', $this->endRepeat));
        }

        if ($this->summary) {
            $this->subQuery->andWhere(Db::parseParam('venti_events.summary', $this->summary));
        }

        if ($this->diff) {
            $this->subQuery->andWhere(Db::parseParam('venti_events.diff', $this->diff));
        }

        if ($this->isrepeat) {
            $this->subQuery->andWhere(Db::parseParam('venti_events.isrepeat', $this->isrepeat));
        }

        if ($this->rRule) {
            $this->subQuery->andWhere(Db::parseParam('venti_events.rRule', $this->rRule));
        }

        if ($this->repeat) {
            $this->subQuery->andWhere(Db::parseParam('venti_events.repeat', $this->repeat));
        }

        if ($this->allDay) {
            $this->subQuery->andWhere(Db::parseParam('venti_events.allDay', $this->allDay));
        }

        if ($this->location) {
            $this->subQuery->andWhere(Db::parseParam('venti_events.location', $this->location));
        }

        if ($this->specificLocation) {
            $this->subQuery->andWhere(Db::parseParam('venti_events.specificLocation', $this->specificLocation));
        }

        if ($this->registration) {
            $this->subQuery->andWhere(Db::parseParam('venti_events.registration', $this->registration));
        }

        if ($this->between) {
            $dates = array();
        	$interval = array();

        	if(!is_array($this->between))
        	{
        		$this->between = ArrayHelper::stringToArray($this->between);
        	}

        	if (count($this->between) == 2)
        	{
        		foreach ($this->between as $ref)
        		{
        			if (!$ref instanceof DateTime)
					{
						$dates[] = DateTime::createFromString($ref, Craft::$app->getTimeZone());
					}
					else
					{
						$dates[] = $ref;
					}
        		}

        		if ($dates[0] > $dates[1])
        		{
        			$interval[0] = $dates[1];
        			$interval[1] = $dates[0];
        		}
        		else
        		{
        			$interval = $dates;
        		}

        		$this->query->andWhere('(venti_events.startDate BETWEEN :betweenStartDate AND :betweenEndDate) OR (:betweenStartDate BETWEEN venti_events.startDate AND venti_events.endRepeat)',
        			array(
        				':betweenStartDate'   => Db::prepareDateForDb($interval[0]->getTimestamp()),
        				':betweenEndDate'     => Db::prepareDateForDb($interval[1]->getTimestamp()),
        			)
        		);
        	}
        }

        $this->_applyEditableParam();
        $this->_applyGroupIdParam();
        $this->_applyRefParam();

        if (!$this->orderBy) {
            $this->orderBy = 'venti_events.startDate desc';
        }

        return parent::beforePrepare();
    }

    /**
     * @inheritdoc
     */
    protected function statusCondition(string $status)
    {
        $currentTimeDb = Db::prepareDateForDb(new \DateTime());
        switch ($status) {
            case VentiEvent::STATUS_LIVE:
                return [
                    'and',
                    [
                        'elements.enabled' => '1',
                        'elements_i18n.enabled' => '1'
                    ]
                ];
            case VentiEvent::STATUS_EXPIRED:
                return [
                    'and',
                    [
                        'elements.enabled' => '1',
                        'elements_i18n.enabled' => '1'
                    ],
                    ['not', ['venti_events.endDate' => null]],
                    ['<=', 'venti_events.endDate', $currentTimeDb]
                ];
            default:
                return parent::statusCondition($status);
        }
    }

/**
     * Applies the 'editable' param to the query being prepared.
     *
     * @return void
     * @throws QueryAbortedException
     */
    private function _applyEditableParam()
    {
        if (!$this->editable) {
            return;
        }

        $user = Craft::$app->getUser()->getIdentity();

        if (!$user) {
            throw new QueryAbortedException();
        }

        // Limit the query to only the sections the user has permission to edit
        $this->subQuery->andWhere([
            'venti_events.groupId' => getEditableGroupIds()
        ]);

    }

/**
     * Applies the 'groupId' param to the query being prepared.
     */
    private function _applyGroupIdParam() {
        if ($this->groupId) {
            // $this->subQuery->join('{{%venti_groups}} groups','[[groups.id]] = [[venti_events.groupId]]');
            // if(is_numeric($this->group)) {
            //     $this->subQuery->addWhere(Db::parseParam('groups.id', $this->group));
            // } else {
            //     $this->subQuery->addWhere(Db::parseParam('groups.handle', $this->group));
            // }
            $this->subQuery->andWhere(Db::parseParam('venti_events.groupId', $this->groupId));
        }
    }

/**
     * Applies the 'ref' param to the query being prepared.
     *
     * @return void
     */
    private function _applyRefParam()
    {
        if (!$this->ref) {
            return;
        }

        $refs = ArrayHelper::toArray($this->ref);
        $joinGroups = false;
        $condition = ['or'];

        foreach ($refs as $ref) {
            $parts = array_filter(explode('/', $ref));

            if (!empty($parts)) {
                if (count($parts) == 1) {
                    $condition[] = Db::parseParam('elements_i18n.slug', $parts[0]);
                } else {
                    $condition[] = [
                        'and',
                        Db::parseParam('groups.handle', $parts[0]),
                        Db::parseParam('elements_i18n.slug', $parts[1])
                    ];
                    $joinGroups = true;
                }
            }
        }

        $this->subQuery->andWhere($condition);

        if ($joinGroups) {
            $this->subQuery->innerJoin('{{%venti_groups}} groups', '[[groups.id]] = [[venti_events.groupId]]');
        }
    }
}