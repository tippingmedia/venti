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
		$variables['groups'] = Venti::getInstance()->groups->getAllGroups();
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

		
		if ($groupId !== null) {
			if ($group === null) {
				$group = Venti::getInstance()->groups->getGroupById($groupId);
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
		//VarDumper::dump($variables, 5, true);exit;
		return $this->renderTemplate('venti/groups/_edit', $variables);
	}

	/**
	 * Saves a group
	 */
	public function actionSaveGroup() 
	{
		$this->requirePostRequest();
		$request = Craft::$app->getRequest();
		$group = new Group();

		// Shared attributes
		$group->id         		= $request->getBodyParam('groupId');
		$group->name       		= $request->getBodyParam('name');
		$group->handle     		= $request->getBodyParam('handle');
		$group->color      		= $request->getBodyParam('color');
		$group->description		= $request->getBodyParam('description');
		$group->propagateEvents = $request->getBodyParam('propagateEvents', true);

	


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
		$allSitesSettings = [];
		// if (Craft::$app->getIsMultiSite()) {
		// 	$sitesParams = Craft::$app->getRequest()->getBodyParam('sites', []);
		// } else {
		// 	$primarySiteId = Craft::$app->getSites()->getPrimarySite()->id;
		// 	$sitesParams = Craft::$app->getRequest()->getBodyParam('sites',[$primarySiteId]);
			
		// }

		foreach (Craft::$app->getSites()->getAllSites() as $site) {	
			$postedSettings = $request->getBodyParam('sites.'.$site->handle);

			if (Craft::$app->getIsMultiSite() && empty($postedSettings['enabled'])) {
                continue;
			}
				//VarDumper::dump($postedSettings, 5, true);exit;

			$groupSiteSettings = new GroupSiteSettings();
			$groupSiteSettings->siteId = $site->id;

			$groupSiteSettings->hasUrls = !empty($postedSettings['uriFormat']);

			if ($groupSiteSettings->hasUrls) {
				$groupSiteSettings->uriFormat = $postedSettings['uriFormat'];
				$groupSiteSettings->template = $postedSettings['template'] != '' ? $postedSettings['template'] : 'event/_entry';
			} else {
				$groupSiteSettings->uriFormat = null;
				$groupSiteSettings->template = null;
			}

            $groupSiteSettings->enabledByDefault = (bool)$postedSettings['enabledByDefault'];

			
			$allSitesSettings[$site->id] = $groupSiteSettings;
			//VarDumper::dump($postedSettings, 5, true);exit;
			// $urlFormat = $postedSettings['uriFormat'];
			// $template = $postedSettings['template'] != '' ? $postedSettings['template'] : 'event/_entry';
			// $hasUrls  = (bool) Craft::$app->getRequest()->getBodyParam('groups.hasUrls', true);
			// $siteId = Craft::$app->getSites()->getSiteByHandle($site);
			// $sites[$site] = new GroupSiteSettings([
			// 	'siteId'           => $siteId->id,
			// 	'enabledByDefault' => (bool) Craft::$app->getRequest()->getBodyParam('defaultSiteStatuses_'.$site),
			// 	'uriFormat'        => $urlFormat,
			// 	'hasUrls'		   => $hasUrls,
			// 	'template'         => $template,
			// ]);
		}

		//VarDumper::dump($sites, 5, true);exit;

		$group->setGroupSiteSettings($allSitesSettings);

		// Save it
		if (!Venti::getInstance()->groups->saveGroup($group)) {
			Craft::$app->getSession()->setError(Craft::t('venti','Couldn’t save group'));
			// Send the group back to the template
			Craft::$app->getUrlManager()->setRouteParams([
				'group' => $group
			]);

			return null;
		}
		
		Craft::$app->getSession()->setNotice(Craft::t('venti','Group saved'));
		
		return $this->redirectToPostedUrl($group);

	}

	/**
	 * Deletes a group.
	 */
	public function actionDeleteGroup() 
	{
		$this->requirePostRequest();
        $this->requireAcceptsJson();

		$groupId = Craft::$app->getRequest()->getRequiredBodyParam('id');

		Venti::getInstance()->groups->deleteGroupById($groupId);

		return $this->asJson(['success' => true]);
	}

}
