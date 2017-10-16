<?php
/**
 * Venti plugin
 *
 *
 * @link      http://tippingmedia.com
 * @copyright Copyright (c) 2017 tippingmedia
 * If you tinker with this code know that you cause us to feel ☹️ 😶 😖 😞 😬 😒.
 */

namespace tippingmedia\venti\services;

use tippingmedia\venti\Venti;

use Craft;
use craft\base\Component;

/**
 * License Service
 *
 * @author    tippingmedia
 * @package   Venti
 * @since     2.0.0
 */


class License extends Component
{
    const GumroadVerifyUrl      = "https://api.gumroad.com/v2/licenses/verify";
    const CheckForUpdates       = 'https://elliott.craftcms.com/actions/elliott/app/checkForUpdates';
    const ProductPermalink      = "venti";


    public function getQueryUrl()
    {
        $settings = craft()->plugins->getPlugin('venti')->getSettings();
        if($settings->license === "")
        {

            return false;
        }

        $params = array(
            "product_permalink" => static::ProductPermalink,
            "license_key" => $settings->license,
            "increment_uses_count" => false
        );

        $url = static::GumroadVerifyUrl . "?" . http_build_query(array_filter($params));
        return $url;
    }


    public function getGumroadResponse()
    {

        //If we don't have a license in settings return false.
        if ($this->getQueryUrl() == false)
        {
            return false;
        }

        try
        {

            $client = new \Guzzle\Http\Client();
            // get the url with attributes
            $request = $client->post($this->getQueryUrl());

            $request->getCurlOptions()->set(CURLOPT_SSL_VERIFYHOST, false);
            $request->getCurlOptions()->set(CURLOPT_SSL_VERIFYPEER, false);
            $request->getCurlOptions()->set(CURLOPT_RETURNTRANSFER, true);
            $request->getCurlOptions()->set(CURLOPT_FOLLOWLOCATION, true);
            //$request->getCurlOptions()->set(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            //$request->getCurlOptions()->set(CURLOPT_USERPWD, "X");

            $response = $request->send();

            if (!$response->isSuccessful())
            {
              return;
            }

            $gumroadResponse = $response->json();

            // Cache the response
            craft()->cache->set('ventiLicense'.craft()->request->getHostName(), $gumroadResponse);

            return $gumroadResponse;

        }
        catch(\Exception $e)
        {
            VentiPlugin::log($e->getResponse(), LogLevel::Error);
            VentiPlugin::log($e->getRequest(), LogLevel::Info);
            return;
        }
    }

    /**
     * @return array
     * If craft is not on a test domain check Gumroad to see if license is valid.
     */
    public function validateGumroadLicense()
    {
        $clearTheWay = true;
        $craftTestDomain = 1;
        $craftLicenseDomain = craft()->cache->get('licensedDomain') ? craft()->cache->get('licensedDomain') : null;

        $settings = craft()->plugins->getPlugin('venti')->getSettings();
        if($settings->license === "")
        {
            $clearTheWay = false;
        }

        // Use Craft's internal Phone Home class to get populated ETModel
        if (!$craftTestDomain = craft()->cache->get('editionTestableDomain@'.craft()->request->getHostName()))
        {
            $et = new Et(static::CheckForUpdates);
    		$etModel = $et->phoneHome();
            if (is_object($etModel))
            {
                $craftTestDomain = $etModel->editionTestableDomain;
                $licensedDomain = $etModel->licensedDomain;
            }

    		// \CVarDumper::dump($etModel->licensedDomain, 5, true);
            // \CVarDumper::dump($etModel->editionTestableDomain, 5, true);
        }

        if($craftTestDomain != 1)
        {
            //Check if we have cached Gumroad Response if not phone Gumroad.
            if($response = craft()->cache->get('ventiLicense'.craft()->request->getHostName()))
            {

                if($response['success'] == true && $response['purchase']['refunded'] == false && $response['purchase']['chargebacked'] == false)
                {
                    $clearTheWay = true;
                }
                else
                {
                    $clearTheWay = false;
                }

            }
            else
            {
                //Phone Gumroad
                $response = craft()->venti_license->getGumroadResponse();

                if (is_array($response))
                {
                    if($response['success'] == true && $response['purchase']['refunded'] == false && $response['purchase']['chargebacked'] == false)
                    {
                        $clearTheWay = true;
                    }
                    else
                    {
                        $clearTheWay = false;
                    }
                }
                else
                {
                    $clearTheWay = false;
                }
            }
        }

        return array("valid" => $clearTheWay, "testDomain" => $craftTestDomain);

    }

}
