<?php

namespace vardump\secretassets\volumes;

use Craft;

class SecretVolume extends \craft\volumes\Local
{
    public static function displayName(): string
    {
        return Craft::t('secretassets', 'volume-name');
    }

    // public urls are checked for permissions before download is granted
    public $hasUrls = true;

    public function init()
    {
        parent::init();
    }

    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('secretassets/volumeSettings', [
            'volume' => $this
        ]);
    }

    // prefix the root url with "secret" to make sure that the secret assets
    // plugin is used to check for permissions
    public function getRootUrl()
    {
        $userDefinedRootUrl = parent::getRootUrl();

        // Note: make url adaption more robust by parsing the url elements
        // and rebuilding it again. Should also correctly handle aliases such as
        // @web.
        // $url = parse_url($userDefinedRootUrl);

        // strip any prefix
        $userDefinedRootUrl = ltrim($userDefinedRootUrl, '/');
        $userDefinedRootUrl = ltrim($userDefinedRootUrl, '//');

        return "/secret/" . $userDefinedRootUrl;
    }

}
