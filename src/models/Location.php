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
 * Location Model
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    tippingmedia
 * @package   Venti
 * @since     1.0.0
 */

class Location extends Model
{


    #-- Properties
	protected $elementType = 'Venti_Location';


    public function getMapUrl($type = 'google')
    {
        $mapurl = "";
        switch ($type) {
            case 'google':
                $mapurl = "http://maps.google.com/?q=" . $this->fullAddress();
                break;
            case 'apple':
                $mapurl = "http://maps.apple.com/?address=" . $this->fullAddress();
                break;
        }
        return $mapurl;
    }

    public function fullAddress()
    {
        return $this->address ." ". $this->city ." ". $this->state ." ". $this->zipCode;
    }

	// Properties
    // =========================================================================


	/**
     * @var string|null address 
     */
    public $address;
	/**
     * @var string|null addressTwo
     */
    public $addressTwo;
	/**
     * @var string|null city
     */
    public $city;
	/**
     * @var string|null state
     */
    public $state;
	/**
     * @var string|null zipCode
     */
    public $zipCode;
	/**
     * @var string|null country
     */
    public $country;
	/**
	   * @var string|null latitude
	*/
	public $latitude;
	/**
	   * @var string|null longitude
	*/
	public $longitude;
	/**
     * @var string|null website
     */
    public $website;
	
	

    /**
	 * Returns whether the current user can edit the element.
	 *
	 * @return bool
	 */
	public function isEditable()
	{
		return true;
	}

    /**
	 * @inheritDoc BaseElementModel::getCpEditUrl()
	 *
	 * @return string|false
	 */
	public function getCpEditUrl()
	{

		// The slug *might* not be set if this is a Draft and they've deleted it for whatever reason
		$url = UrlHelper::getCpUrl('venti/location/'.$this->id.($this->slug ? '-'.$this->slug : ''));

		return $url;

	}

    /**
	 * Returns the reference string to this element.
	 *
	 * @return string|null
	 */
	public function getRef()
	{
		return 'location/'.$this->id."-".$this->slug;
	}
}
