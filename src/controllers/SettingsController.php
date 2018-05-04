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

        $plugin = Craft::$app->getPlugins()->getPlugin('venti');
       
        $postSettings = Craft::$app->getRequest()->getBodyParams('settings', []);
        $updateLayout = Craft::$app->getRequest()->getBodyParams('update_layout', false);
        $settings = $plugin->getSettings();
        $groups = new Groups();

        foreach ($postSettings['settings'] as $key => $value) {
            $settings[$key] = $value;
        }
        
        // $settings['eventDuration'] = array_key_exists('eventDuration',$postSettings) ? $postSettings['eventDuration'] : $settings['eventDuration'];
        // $settings['timeInterval'] = array_key_exists('timeInterval',$postSettings) ? $postSettings['timeInterval'] : $settings['timeInterval'];
        // $settings['pluginName'] = array_key_exists('pluginName',$postSettings) ? $postSettings['pluginName'] : $settings['pluginName'];
        // $settings['license'] = array_key_exists('license',$postSettings) ? $postSettings['license'] : $settings['license'];
        // $settings['googleMapsApiKey'] = array_key_exists('googleMapsApiKey',$postSettings) ? $postSettings['googleMapsApiKey'] : $settings['googleMapsApiKey'];
        // $settings['country'] = array_key_exists('country',$postSettings) ? $postSettings['country'] : $settings['country'];
        // $settings['hideRegistration'] = array_key_exists('hideRegistration',$postSettings) ? $postSettings['hideRegistration'] : $settings['hideRegistration'];
        // $settings['hideLocation'] = array_key_exists('hideLocation',$postSettings) ? $postSettings['hideLocation'] : $settings['hideLocation'];
        // $settings['multisite'] = array_key_exists('multisite',$postSettings) ? $postSettings['multisite'] : $settings['multisite'];

        if ($updateLayout) {
            $oldLayout = Craft::$app->getFields()->getLayoutByType('Venti_Event_Default');
            $groupsWithOldLayoutId = $groups->getGroupsByLayoutId($oldLayout->id);

            $fieldLayout = $this->saveGroupLayout();

            if ( !$groups->updateGroupLayoutIds($groupsWithOldLayoutId, $fieldLayout['id']) )
            {
                Craft::$app->getSession()->setNotice(Craft::t('venti','Group default layouts not updated.'));
            }
        }

        if(!Craft::$app->getPlugins()->savePluginSettings($plugin, $settings->getAttributes())) {
            Craft::$app->getSession()->setNotice(Craft::t('venti','Settings Not Saved'));
        }

        Craft::$app->getSession()->setNotice(Craft::t('venti','Settings Saved'));

        $this->redirectToPostedUrl();
    }


    /**
     * Saves group layout. If already present remove before saving.
     */
    private function saveGroupLayout()
    {
        $fieldLayout = Craft::$app->getRequest()->getBodyParam('fieldLayout');

        Craft::$app->getFields()->deleteLayoutsByType('Venti_Event_Default');

        if ($fieldLayout) {
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
