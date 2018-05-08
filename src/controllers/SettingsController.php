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
use tippingmedia\venti\services\License;
use tippingmedia\venti\services\Groups;
use tippingmedia\venti\services\Settings as SettingsService;
use tippingmedia\venti\models\Settings;
use tippingmedia\venti\models\Group;

use Craft;
use craft\web\Controller;
use craft\helpers\UrlHelper;
use yii\helpers\VarDumper;

/**
 * Settings Controller
 *
 *
 * @author    tippingmedia
 * @package   Venti
 * @since     2.0.0
 */
 
class SettingsController extends Controller
{
    /**
     * Logged in member requried to access controller.
     */
    public function init()
    {
        $this->requireLogin();
    }


    public function actionSaveSettings()
    {
        
        $this->requirePostRequest();

        $settings = Craft::$app->request->post('settings', []);
        $fieldLayout = Craft::$app->getRequest()->getBodyParam('fieldLayout');
        //$updateLayout = Craft::$app->request->post('update_layout', false);

        if ($fieldLayout !== null) {
            $oldLayout = Craft::$app->getFields()->getLayoutByType('Venti_Event_Default');
            $groupsWithOldLayoutId = Venti::getInstance()->groups->getGroupsByLayoutId($oldLayout->id);

            $fieldLayout = $this->saveGroupLayout();

            if ( !Venti::getInstance()->groups->updateGroupLayoutIds($groupsWithOldLayoutId, $fieldLayout['id']) )
            {
                Craft::$app->getSession()->setNotice(Craft::t('venti','Group default layouts not updated.'));
            }

        } else {
            $oldLayout = Craft::$app->getFields()->getLayoutByType('Venti_Event_Default');
            
            if($oldLayout != null) {
                $groupsWithOldLayoutId = Venti::getInstance()->groups->getGroupsByLayoutId($oldLayout->id);
                if ( !Venti::getInstance()->groups->updateGroupLayoutIds($groupsWithOldLayoutId, null) ) {
                    Craft::$app->getSession()->setNotice(Craft::t('venti','Group default layouts not updated.'));
                }
                Craft::$app->getFields()->deleteLayoutsByType('Venti_Event_Default');
            }
        }

        $plugin = Venti::getInstance();
        $plugin->setSettings($settings);
        Craft::$app->getPlugins()->savePluginSettings($plugin, $settings);
        Craft::$app->getSession()->setNotice(Craft::t('venti','Settings Saved'));

        return $this->redirectToPostedUrl();
    }


    /**
     * Saves group layout. If already present remove before saving.
     */
    private function saveGroupLayout()
    {
        $fieldLayout = Craft::$app->getRequest()->getBodyParam('fieldLayout');
        //Craft::$app->getFields()->deleteLayoutsByType('Venti_Event_Default');

        if ($fieldLayout !== null) {
            $fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();
            $fieldLayout->type = 'Venti_Event_Default';
            Craft::$app->getFields()->saveLayout($fieldLayout);
            return $fieldLayout;
        }
    }

    public function actionIndex()
    {
        $plugin = Craft::$app->getPlugins()->getPlugin('venti');

        return $this->renderTemplate('venti/settings/index', [
            'error' => (isset($error) ? $error : null),
            'settings' => $plugin->getSettings()
        ]);
    }

    public function actionGeneral()
    {
        $plugin = Craft::$app->getPlugins()->getPlugin('venti');
        return $this->renderTemplate('venti/settings/_general', [
            'error' => (isset($error) ? $error : null),
            'settings' => $plugin->getSettings()
        ]);
    }

    public function actionGroups()
    {
        $plugin = Craft::$app->getPlugins()->getPlugin('venti');

        return $this->renderTemplate('venti/settings/_groups', [
            'error' => (isset($error) ? $error : null),
            'settings' => $plugin->getSettings()
        ]);
    }

    public function actionEvents()
    {
        $plugin = Craft::$app->getPlugins()->getPlugin('venti');

        return $this->renderTemplate('venti/settings/_events', [
            'error' => (isset($error) ? $error : null),
            'settings' => $plugin->getSettings()
        ]);
    }

}
