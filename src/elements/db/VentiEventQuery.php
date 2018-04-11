<?php
namespace tippingmedia\venti\elements\db;

use tippingmedia\venti\Venti;
use tippingmedia\venti\models\Group;
use tippingmedia\venti\models\Event;
use tippingmedia\venti\models\Location;
use tippingmedia\venti\services\Groups;
use tippingmedia\venti\elements\VentiEvent;

use Craft;
use craft\db\Query;
use craft\elements\db\ElementQuery;
use craft\db\QueryAbortedException;
use craft\elements\Entry;
use craft\helpers\ArrayHelper;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use DateTime;
use DateTimeZone;
use yii\db\Connection;


class VentiEventQuery extends ElementQuery
{
     // Properties
    // =========================================================================

    // General parameters
    //

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
    public $recurring;
    public $isrecurring;
    public $allDay;
    public $summary;
    public $location;
    public $specificLocation;
    public $registration;
    public $between;
    public $range;
    public $cpindex;
    public $scheduled_date;
    public $event_id;


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
     * @return static self reference
     */
    public function editable(bool $value = true)
    {
        $this->editable = $value;
        return $this;
    }

    /**
     * Sets the [[groupId]] property.
     *
     * @param int|int[]|null $value The property value
     * @return static self reference
     */
    public function groupId($value)
    {
        $this->groupId = $value;
        return $this;
    }

    /**
     * Sets the [[groupId]] property based on a given group(s)’s handle(s).
     *
     * @param string|string[]|Group|null $value The property value
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
                ->where(Db::parseParam('handle', $value))
                ->column();
        } else {
            $this->groupId = null;
        }

        return $this;
    }

    /**
     * Sets the [[startDate]] property.
     *
     * @param DateTime|string $value The property value
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
     * @return static self reference
     */
    public function endDate($value)
    {
        $this->endDate = $value;
        return $this;
    }

    /**
     * Sets the [[diff]] property.
     *
     * @param Time|string $value The property value
     * @return static self reference
     */
    public function diff($value)
    {
        $this->diff = $value;
        return $this;
    }

    /**
     * Sets the [[rRule]] property.
     *
     * @param $string|null $value The property value
     * @return static self reference
     */
    public function rRule($value)
    {
        $this->rRule = $value;
        return $this;
    }

    /**
     * Sets the [[recurring]] property.
     *
     * @param $int|null $value The property value
     * @return static self reference
     */
    public function recurring($value)
    {
        $this->recurring = $value;
        return $this;
    }
    
    /**
     * Sets the [[allDay]] property.
     *
     * @param $int|null $value The property value
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
     * @return static self reference
     */
    public function summary($value)
    {
        $this->summary = $value;
        return $this;
    }

    /**
     * Sets the [[isrecurring]] property.
     *
     * @param $int|null $value The property value
     * @return static self reference
     */
    public function isrecurring($value)
    {
        $this->isrecurring = $value;
        return $this;
    }

    /**
     * Sets the [[location]] property.
     *
     * @param int|int[]|null $value The property value
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
     * @return static self reference
     */
    public function registration($value)
    {
        $this->registration = $value;
        return $this;
    }  

    /**
     * Sets the [[between]] property.
     *
     * @param $array|string $value The property value
     * @return static self reference
     */
    public function between($value)
    {
        $this->between = $value;
        return $this;
    }

    /**
     * Sets the [[range]] property.
     *
     * @param $array|string $value The property value
     * @return static self reference
     */
    public function range($value)
    {
        $this->range = $value;
        return $this;
    }


    /**
     * Sets the [[cpindex]] property.
     *
     * @param $int|null $value The property value
     * @return static self reference
     */
    public function cpindex($value) {
        $this->cpindex = $value;
        return $this;
    }

    /**
     * Sets the [[event_id]] property.
     *
     * @param $int|null $value The property value
     * @return static self reference
     */
    public function event_id($value)
    {
        $this->event_id = $value;
        return $this;
    }  

    /**
     * Sets the [[scheduled_date]] property.
     *
     * @param DateTime|string $value The property value
     * @return static self reference
     */
    public function scheduled_date($value)
    {
        $this->scheduled_date = $value;
        return $this;
    }  



    // Private Methods
    // =========================================================================
    
    /**
     * @inheritdoc
     */
    protected function beforePrepare(): bool
    {

        // See if 'group' is set to invalid handle
        if ($this->groupId === []) {
            return false;
        }

        $this->joinElementTable('venti_events');

        $selects = [
            'venti_events.id',
            'venti_events.groupId',
            'venti_events.allDay',
            'venti_events.rRule',
            'venti_events.recurring',
            'venti_events.summary',
            'venti_events.siteId',
            'venti_events.location',
            'venti_events.specificLocation',
            'venti_events.registration',
            'venti_events.startDate',
            'venti_events.endDate',
            'venti_events.endRepeat',
            'venti_events.diff',
        ];


        if($this->between || $this->range) {
            $params = $this->prepareDateParams($this->between);
            Craft::$app->getDb()->createCommand("CALL {{%venti_recurr_tmp_gen}}('".$params[0]."', '".$params[1]."')")->execute();
            
        } else {

            $params = $this->prepareDateParams([NULL,NULL]);
            Craft::$app->getDb()->createCommand("CALL {{%venti_recurr_tmp_gen}}('".$params[0]."', '".$params[1]."')")->execute();
        }
        
        // If cpindex is true this will prevent grabbing all recurrences for the Venti CP Element Index
        if(!$this->cpindex) {
            $selects[] = 'recur.event_id';
            $selects[] = 'recur.scheduled_date';

            $this->query->rightJoin('{{%venti_recurr_tmp}} recur', '[[recur.event_id]] = [[venti_events.id]]');
        }

        $this->query->select($selects);

        if ($this->groupId) {
            $this->subQuery->andWhere(Db::parseParam('venti_events.groupId', $this->groupId));
        }

        if ($this->startDate) {   
            if (!$this->cpindex) {
                $this->subQuery->andWhere(Db::parseDateParam('recur.scheduled_date', $this->startDate));
            }else {
                $this->subQuery->andWhere(Db::parseDateParam('venti_events.startDate', $this->startDate));
            }
        }

        if($this->scheduled_date) {
            $this->subQuery->andWhere(Db::parseDateParam('recur.scheduled_date', $this->scheduled_date));
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

        if ($this->isrecurring) {
            $this->subQuery->andWhere(Db::parseParam('venti_events.isrecurring', $this->isrecurring));
        }

        if ($this->rRule) {
            $this->subQuery->andWhere(Db::parseParam('venti_events.rRule', $this->rRule));
        }

        if ($this->recurring) {
            $this->subQuery->andWhere(Db::parseParam('venti_events.recurring', $this->recurring));
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

        $this->_applyEditableParam();
        $this->_applyGroupIdParam();
        $this->_applyRefParam();


        if (!$this->orderBy && !$this->cpindex) {
            $this->query->orderBy = ['recur.scheduled_date' => SORT_ASC];
        }
        // if order by is startDate order by recur.scheduled_date
        if($this->orderBy && !$this->cpindex) {
            if(array_key_exists('startDate', $this->orderBy)) {
                if($this->orderBy['startDate'] == 3) {
                    $this->query->orderBy = ['recur.scheduled_date' => SORT_DESC];
                }
                if($this->orderBy['startDate'] == 4) {
                    $this->query->orderBy = ['recur.scheduled_date' => SORT_ASC];
                }
            }
        }
        
        return parent::beforePrepare();
    }

    protected function afterPrepare(): bool
    {
        //\yii\helpers\VarDumper::dump($this, 8, true);exit;
        return parent::afterPrepare();
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
                        'elements_sites.enabled' => '1'
                    ]
                ];
            case VentiEvent::STATUS_EXPIRED:
                return [
                    'and',
                    [
                        'elements.enabled' => '1',
                        'elements_sites.enabled' => '1'
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
                    $condition[] = Db::parseParam('elements_sites.slug', $parts[0]);
                } else {
                    $condition[] = [
                        'and',
                        Db::parseParam('groups.handle', $parts[0]),
                        Db::parseParam('elements_sites.slug', $parts[1])
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

    /**
     * Prepares dates for recur procedure
     * @param $params array of date strings
     * @return array 
     */
    private function prepareDateParams($params)
    {
        $current = new DateTime('now', new DateTimeZone(Craft::$app->getTimeZone()));
        $format = 'Y-m-d g:i:s';
        // TODO: If event detail request use startDate in segments to get specific date.
        //\yii\helpers\VarDumper::dump(Craft::$app->getRequest(), 5, true);exit;

        if(is_array($params) && (isset($params[0]) || (isset($params[1])))) {
            $dates = $params;
            $dateParams = [];
            
            // Is begining span set, if not set to current.
            if(!isset($dates[0]) || $dates[0] == NULL) {
                $altStart = $current;
                $dates[0] = $altStart;
            } else {
                $start = DateTimeHelper::toDateTime(strtotime($dates[0]));
                $dates[0] = $start;
            }

            $dateParams[0] = $dates[0]->format($format);


            // Is ending span set, if not set to start +1 month.
            if(!isset($dates[1]) || $dates[1] == NULL) {
                // Use begin date as starting point.
                $altStart = $dates[0];
                date_modify($altStart, '+1 month');
                $dates[1] = $altStart;
            } else {
                $end = DateTimeHelper::toDateTime(strtotime($dates[1]));
                $dates[1] = $end;
            }
            
            $dateParams[1] = $dates[1]->format($format);

            return $dateParams;

        } else {
            $dateParams = [];
            // Default to current datetime to 1 month in the future
            $dateParams[0] = $current->format($format);
            $span = $current;
            date_modify($span, "+1 month");
            $dateParams[1] = $span->format($format);

            return $dateParams;
        }
    }
}