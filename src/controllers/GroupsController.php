<?php
/**
 * Venti plugin
 *
 *
 * @link      http://tippingmedia.com
 * @copyright Copyright (c) 2017 tippingmedia
 */

namespace tippingmedia\venti\controllers;

use tippingmedia\venti\Venti;
use tippingmedia\venti\services\Groups;
use tippingmedia\venti\models\Group;
use tippingmedia\venti\models\GroupSiteSettings;

use Craft;
use craft\web\Controller;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use craft\models\Site;
use DateTime;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

use yii\helpers\VarDumper;

/**
 * Groups Controller
 *
 *
 * @author    tippingmedia
 * @package   Venti
 * @since     2.0.0
 */
class GroupsController extends Controller
{
	/**
	 * Group index
	 */
	public function actionGroupIndex() 
	{
		$groups = new Groups();
		$variables['groups'] = $groups->getAllGroups();

		$this->renderTemplate('venti/groups/index', $variables);
	}

	/**
	 * Edit a group.
	 *
	 * @param array $variables
	 * @throws HttpException
	 * @throws Exception
	 */
	public function actionEditGroup(int $groupId = null, Group $group = null) 
	{
		$variables = [
			'groupId' => $groupId,
			'brandNewGroup' => false
		];

		$groups = new Groups();
		
		if ($groupId !== null) {
			if ($group === null) {
				$group = $groups->getGroupById($groupId);

				if (!$group) {
					throw new NotFoundHttpException('Group not found');
				}
			}

			$variables['title'] = $group->name;
		} else {
			if ($group === null) {
				$group = new Group();
				$variables['brandNewGroup'] = true;
			}

			$variables['title'] = Craft::t('venti','Create a new group');
		}

		$variables['crumbs'] = [
			['label' => Craft::t('venti','Venti'), 'url' => UrlHelper::url('venti')],
			['label' => Craft::t('venti','Groups'), 'url' => UrlHelper::url('venti/groups')],
		];

		$variables['group'] = $group;
		
		return $this->renderTemplate('venti/groups/_edit', $variables);
	}

	/**
	 * Saves a group
	 */
	public function actionSaveGroup() 
	{
		$this->requirePostRequest();
		$request = Craft::$app->getRequest();
		$groups = new Groups();
		$group = new Group();

		// Shared attributes
		$group->id         		= $request->getBodyParam('groupId');
		$group->name       		= $request->getBodyParam('name');
		$group->handle     		= $request->getBodyParam('handle');
		$group->color      		= $request->getBodyParam('color');
		$group->description		= $request->getBodyParam('description');

	


		// Set the field layout
		// Use the default from the groups settings or use group specific
		if (Craft::$app->getRequest()->getBodyParam('defaultFieldLayout') == "1") {

			$fieldLayout = Craft::$app->getFields()->getLayoutByType('Venti_Event_Default');
			$group->fieldLayoutId = $fieldLayout->id;

		} else {
			
			$fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();
			$fieldLayout->type = 'Venti_Event';
			$group->setFieldLayout($fieldLayout);
		}


		// Site-specific attributes
		$sites = [];
		if (Craft::$app->getIsMultiSite()) {
			$sitesParams = Craft::$app->getRequest()->getBodyParam('sites', []);
		} else {
			$primarySiteId = Craft::$app->getSites()->getPrimarySite()->id;
			$sitesParams = [$primarySiteId];
		}

		foreach ($sitesParams as $site => $params) {	
			//VarDumper::dump($site, 5, true);exit;
			$urlFormat = $params['uriFormat'];
			$template = $params['template'] != '' ? $params['template'] : 'event/_entry';
			$hasUrls  = (bool) Craft::$app->getRequest()->getBodyParam('groups.hasUrls', true);
			$siteId = Craft::$app->getSites()->getSiteByHandle($site);
			$sites[$site] = new GroupSiteSettings([
				'siteId'           => $siteId->id,
				'enabledByDefault' => (bool) Craft::$app->getRequest()->getBodyParam('defaultSiteStatuses_'.$site),
				'uriFormat'        => $urlFormat,
				'hasUrls'		   => $hasUrls,
				'template'         => $template,
			]);
		}

		//VarDumper::dump($sites, 5, true);exit;

		$group->setGroupSiteSettings($sites);

		// Save it
		if (!$groups->saveGroup($group)) {
			Craft::$app->getSession()->setError(Craft::t('venti','Couldn’t save group'));
			// Send the group back to the template
			Craft::$app->getUrlManager()->setRouteParams([
				'group' => $group
			]);
		}
		
		Craft::$app->getSession()->setNotice(Craft::t('venti','Group saved'));
		
		$this->redirectToPostedUrl($group);

	}

	/**
	 * Deletes a group.
	 */
	public function actionDeleteGroup() 
	{
		$this->requirePostRequest();
        $this->requireAcceptsJson();

		$groups = new Groups();

		$groupId = Craft::$app->getRequest()->getRequiredBodyParam('id');

		$groups->deleteGroupById($groupId);

		return $this->asJson(['success' => true]);
	}

}
