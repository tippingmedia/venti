<?php
namespace tippingmedia\venti\elements\db;

use tippingmedia\venti\Venti;
use tippingmedia\venti\models\Location;
use tippingmedia\venti\services\Locations;

use craft\db\Query;
use craft\elements\db\ElementQuery;
use craft\db\QueryAbortedException;
use craft\elements\Entry;
use craft\helpers\ArrayHelper;
use craft\helpers\Db;
use DateTime;
use yii\db\Connection;


class VentiLocationQuery extends ElementQuery
{
     /**
     * @var bool Whether to only return locations that the user has permission to edit.
     */
    public $editable = false;
    public $address;
    public $addressTwo;
    public $city;
    public $state;
    public $zipCode;
    public $country;
    public $longitude;
    public $latitude;
    public $website;
    // public $town;
    // public $region;
    // public $province;
    // public $postalCode;


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
        parent::__set($name, $value);
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
     * Sets the [[address]] property.
     *
     * @param string|string[]|null $value The property value
     *
     * @return static self reference
     */
    public function address($value)
    {
        $this->address = $value;

        return $this;
    }


    /**
     * Sets the [[addressTwo]] property.
     *
     * @param string|string[]|null $value The property value
     *
     * @return static self reference
     */
    public function addressTwo($value)
    {
        $this->addressTwo = $value;

        return $this;
    }

    /**
     * Sets the [[city]] property.
     *
     * @param $string|null $value The property value
     *
     * @return static self reference
     */
    public function city($value)
    {
        $this->city = $value;

        return $this;
    }


    /**
     * Sets the [[state]] property.
     *
     * @param $string|null $value The property value
     *
     * @return static self reference
     */
    public function state($value)
    {
        $this->state = $value;

        return $this;
    }

    /**
     * Sets the [[zipCode]] property.
     *
     * @param $string|null $value The property value
     *
     * @return static self reference
     */
    public function zipCode($value)
    {
        $this->zipCode = $value;

        return $this;
    }
    
    /**
     * Sets the [[country]] property.
     *
     * @param $string|null $value The property value
     *
     * @return static self reference
     */
    public function country($value)
    {
        $this->country = $value;

        return $this;
    }

    /**
     * Sets the [[longitude]] property.
     *
     * @param $string|null $value The property value
     *
     * @return static self reference
     */
    public function longitude($value)
    {
        $this->longitude = $value;

        return $this;
    }

    /**
     * Sets the [[latitude]] property.
     *
     * @param $string|null $value The property value
     *
     * @return static self reference
     */
    public function latitude($value)
    {
        $this->latitude = $value;

        return $this;
    }

    /**
     * Sets the [[website]] property.
     *
     * @param $string|null $value The property value
     *
     * @return static self reference
     */
    public function website($value)
    {
        $this->website = $value;

        return $this;
    }

    /**
     * Sets the [[town]] property.
     *
     * @param $string|null $value The property value
     *
     * @return static self reference
     */
    // public function town($value)
    // {
    //     $this->town = $value;

    //     return $this;
    // }

    /**
     * Sets the [[region]] property.
     *
     * @param $string|null $value The property value
     *
     * @return static self reference
     */
    // public function region($value)
    // {
    //     $this->region = $value;

    //     return $this;
    // }

        /**
     * Sets the [[province]] property.
     *
     * @param $string|null $value The property value
     *
     * @return static self reference
     */
    // public function province($value)
    // {
    //     $this->province = $value;

    //     return $this;
    // }



    /**
     * @inheritdoc
     */
    protected function beforePrepare(): bool
    {


        $this->joinElementTable('venti_locations loc');

        $this->query->select([
            'loc.address',
            'loc.addressTwo',
            'loc.city',
            'loc.state',
            'loc.zipCode',
            'loc.country',
            'loc.longitude',
            'loc.latitude',
            'loc.website'
        ]);


        if ($this->address) {
            $this->subQuery->andWhere(Db::parseParam('loc.address', $this->address));
        }

        if ($this->addressTwo) {
            $this->subQuery->andWhere(Db::parseDateParam('loc.addressTwo', $this->addressTwo));
        }

        if ($this->city) {
            $this->subQuery->andWhere(Db::parseDateParam('loc.city', $this->city));
        }

        if ($this->state) {
            $this->subQuery->andWhere(Db::parseParam('loc.state', $this->state));
        }

        if ($this->zipCode) {
            $this->subQuery->andWhere(Db::parseParam('loc.zipCode', $this->zipCode));
        }

        if ($this->country) {
            $this->subQuery->andWhere(Db::parseParam('loc.country', $this->country));
        }

        if ($this->longitude) {
            $this->subQuery->andWhere(Db::parseParam('loc.longitude', $this->longitude));
        }

        if ($this->latitude) {
            $this->subQuery->andWhere(Db::parseParam('loc.latitude', $this->latitude));
        }

        if ($this->website) {
            $this->subQuery->andWhere(Db::parseParam('loc.website', $this->website));
        }

        // if ($this->town) {
        //     $this->subQuery->andWhere(Db::parseParam('loc.town', $this->town));
        // }

        // if ($this->region) {
        //     $this->subQuery->andWhere(Db::parseParam('loc.region', $this->region));
        // }

        // if ($this->province) {
        //     $this->subQuery->andWhere(Db::parseParam('loc.province', $this->province));
        // }


        //$this->_applyRefParam();


        return parent::beforePrepare();
    }
