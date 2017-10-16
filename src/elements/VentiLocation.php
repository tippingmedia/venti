<?php
/**
 * Venti plugin
 *
 *
 * @link      http://tippingmedia.com
 * @copyright Copyright (c) 2017 tippingmedia
 */

namespace tippingmedia\venti\elements;

use tippingmedia\venti\Venti;
use tippingmedia\venti\elements\db\VentiLocationQuery;
use tippingmedia\venti\services\Locations;
use tippingmedia\venti\models\Location;

use tippingmedia\venti\elements\actions\Edit;
use tippingmedia\venti\elements\actions\Delete;
use tippingmedia\venti\elements\actions\View;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;
use ns\prefix\elements\db\ProductQuery;

/**
 * VentiLocation Element
 *
 * Element is the base class for classes representing elements in terms of objects.
 *
 * @property FieldLayout|null      $fieldLayout           The field layout used by this element
 * @property array                 $htmlAttributes        Any attributes that should be included in the element’s DOM representation in the Control Panel
 * @property int[]                 $supportedSiteIds      The site IDs this element is available in
 * @property string|null           $uriFormat             The URI format used to generate this element’s URL
 * @property string|null           $url                   The element’s full URL
 * @property \Twig_Markup|null     $link                  An anchor pre-filled with this element’s URL and title
 * @property string|null           $ref                   The reference string to this element
 * @property string                $indexHtml             The element index HTML
 * @property bool                  $isEditable            Whether the current user can edit the element
 * @property string|null           $cpEditUrl             The element’s CP edit URL
 * @property string|null           $thumbUrl              The URL to the element’s thumbnail, if there is one
 * @property string|null           $iconUrl               The URL to the element’s icon image, if there is one
 * @property string|null           $status                The element’s status
 * @property Element               $next                  The next element relative to this one, from a given set of criteria
 * @property Element               $prev                  The previous element relative to this one, from a given set of criteria
 * @property Element               $parent                The element’s parent
 * @property mixed                 $route                 The route that should be used when the element’s URI is requested
 * @property int|null              $structureId           The ID of the structure that the element is associated with, if any
 * @property ElementQueryInterface $ancestors             The element’s ancestors
 * @property ElementQueryInterface $descendants           The element’s descendants
 * @property ElementQueryInterface $children              The element’s children
 * @property ElementQueryInterface $siblings              All of the element’s siblings
 * @property Element               $prevSibling           The element’s previous sibling
 * @property Element               $nextSibling           The element’s next sibling
 * @property bool                  $hasDescendants        Whether the element has descendants
 * @property int                   $totalDescendants      The total number of descendants that the element has
 * @property string                $title                 The element’s title
 * @property string|null           $serializedFieldValues Array of the element’s serialized custom field values, indexed by their handles
 * @property array                 $fieldParamNamespace   The namespace used by custom field params on the request
 * @property string                $contentTable          The name of the table this element’s content is stored in
 * @property string                $fieldColumnPrefix     The field column prefix this element’s content uses
 * @property string                $fieldContext          The field context this element’s content uses
 *
 * http://pixelandtonic.com/blog/craft-element-types
 *
 * @author    tippingmedia
 * @package   Venti
 * @since     2.0.0
 */
class VentiLocation extends Element
{


	public static function find(): ElementQueryInterface
	{
		return new VentiLocationQuery(get_called_class());
	}

	/**
	 * Returns the element type name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return Craft::t('Venti','Location');
	}

	/**
     * @inheritdoc
     */
	public static function refHandle()
	{
		return 'venti_location';
	}

	/**
	 * Returns whether this element type has content.
	 *
	 * @return bool
	 */
	public static function hasContent(): bool
	{
		return true;
	}

	/**
	 * Returns whether this element type has titles.
	 *
	 * @return bool
	 */
	public static function hasTitles(): bool
	{
		return true;
	}



	public static function isLocalized(): bool
	{
	     return false;
	}


	/**
	* @inheritDoc IElementType::hasStatuses()
	*
	* @return bool
	*/
	public static function hasStatuses(): bool
	{
		return false;
	}



	/**
     * @inheritdoc
     */
	protected static function defineSources(string $context = null): array 
	{
		$sources = [
			[
				'key' => '*',
				'label'    => Craft::t('venti','All Locations'),
				'hasThumbs' => false,
			]
		];
		return $sources;
	}


	


	/**
     * @inheritdoc
     */
	protected static function defineTableAttributes(): array
	{
		$attributes = [
			'title'     => ['label' => Craft::t('venti','Title')],
			'address' 	=> ['label' => Craft::t('venti','Address')],
			'state'   	=> ['label' => Craft::t('venti','State')],
			'zipCode'	=> ['label' => Craft::t('venti','Zip Code')],
			'country'   => ['label' => Craft::t('venti','Country')],
		];

		return $attributes;
	}

	/**
	 * Returns the table view HTML for a given attribute.
	 *
	 * @param BaseElementModel $element
	 * @param string $attribute
	 * @return string
	 */
	protected function tableAttributeHtml(string $attribute): string
	{
		return parent::getTableAttributeHtml($element, $attribute);
	}

 	/**
     * @inheritdoc
     */
	public static function defineActions(string $source = null): array
	{

		// for now these are always on
		$userSessionService = Craft::$app->getUser();
		$canEdit = false;
		$canDelete = false;

		// Now figure out what we can do with these
		$actions = [];

		$canPublishLocations = $userSessionService->checkPermission('publishLocations');
		$canDeleteLocations = $userSessionService->checkPermission('deleteLocations');

		if ($canPublishLocations) {
			$canEdit = true;
		}

		if ($canDeleteLocations) {
			$canDelete = true;
		}


		if ($canEdit) {
			$actions[] = Craft::$app->getElements()->createAction([
				'type' => Edit::class,
				'label' => Craft::t('Venti', 'Edit location'),
			]);
		}


		if ($canDelete) {
			$actions[] = Craft::$app->getElements()->createAction([
				'type' => Delete::class,
				'confirmationMessage' => Craft::t('Venti', 'Are you sure you want to delete the selected locations?'),
				'successMessage' => Craft::t('Venti', 'Locations deleted.'),
			]);
		}

		return $actions;
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
	   * @var string|null longitude
	*/
	public $longitude;
	
	/**
	   * @var string|null latitude
	*/
	public $latitude;

	/**
	   * @var string|null website
	*/
	public $website;

	/**
	   * @var string|null town
	*/
	public $town;

	/**
	   * @var string|null region
	*/
	//public $region;

	/**
	   * @var string|null provice
	*/
	//public $provice;

	/**
	   * @var string|null postalCode
	*/
	//public $postalCode;
	
	




	/**
	 * Modifies an element query targeting elements of this type.
	 *
	 * @param DbCommand $query
	 * @param ElementCriteriaModel $criteria
	 * @return mixed
	 */
	// public function modifyElementsQuery(DbCommand $query, ElementCriteriaModel $criteria)
	// {
	// 	$query
	// 		->addSelect('loc.id, loc.address, loc.addressTwo, loc.city, loc.state, loc.zipCode, loc.country, loc.longitude, loc.latitude, loc.website')
	// 		->join('venti_locations loc', 'loc.id = elements.id');


	// 	if ($criteria->address)
	// 	{
	// 		$query->andWhere(DbHelper::parseDateParam('loc.address', $criteria->address, $query->params));
	// 	}

	// 	if ($criteria->addressTwo)
	// 	{
	// 		$query->andWhere(DbHelper::parseDateParam('loc.addressTwo', $criteria->addressTwo, $query->params));
	// 	}

	// 	if($criteria->city)
    //     {
    //         $query->andWhere(DbHelper::parseParam('loc.city', $criteria->city, $query->params));
    //     }

	// 	if($criteria->town)
    //     {
    //         $query->andWhere(DbHelper::parseParam('loc.city', $criteria->town, $query->params));
    //     }

	// 	if($criteria->state)
    //     {
    //         $query->andWhere(DbHelper::parseParam('loc.state', $criteria->state, $query->params));
    //     }

	// 	if($criteria->province)
    //     {
    //         $query->andWhere(DbHelper::parseParam('loc.state', $criteria->province, $query->params));
    //     }

	// 	if($criteria->region)
    //     {
    //         $query->andWhere(DbHelper::parseParam('loc.state', $criteria->region, $query->params));
    //     }

	// 	if($criteria->zipCode)
	// 	{
	// 		$query->andWhere(DbHelper::parseParam('loc.zipCode', $criteria->zipCode, $query->params));
	// 	}

	// 	if($criteria->postalCode)
	// 	{
	// 		$query->andWhere(DbHelper::parseParam('loc.zipCode', $criteria->postalCode, $query->params));
	// 	}

	// 	if($criteria->latitude)
    //     {
    //         $query->andWhere(DbHelper::parseParam('loc.latitude', $criteria->latitude, $query->params));
    //     }

	// 	if($criteria->longitude)
    //     {
    //         $query->andWhere(DbHelper::parseParam('loc.longitude', $criteria->longitude, $query->params));
    //     }

	// 	if($criteria->country)
    //     {
    //         $query->andWhere(DbHelper::parseParam('loc.country', $criteria->country, $query->params));
    //     }

	// 	if($criteria->website)
    //     {
    //         $query->andWhere(DbHelper::parseParam('loc.website', $criteria->website, $query->params));
    //     }

	// }


	// Public Methods
	// =========================================================================


  	/**
     * @inheritdoc
     */
    public function getIsEditable(): bool
    {
        return Craft::$app->getUser()->checkPermission('publishLocations:'.$this->id);
    }


    /**
     * @inheritdoc
     */
	public function getEditorHtml(): string
	{
		$html = '';
        $view = Craft::$app->getView();
		
    	// Figure out what that ID is going to look like once it has been namespaced
    	$namespacedId = $view->getNamespace();

		$html = Craft::$app->getView()->renderTemplate('venti/locations/_editor', [
			'location' => $element,
			'countries' => LocationHelper::countryOptions(),
			'defaultCountry' => LocationHelper::country(),
			'namespacedId' => $namespacedId,
		]);

		$html .= parent::getEditorHtml();

		return $html;
	}

}
