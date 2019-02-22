<?php
/**
 * secretassets plugin for Craft CMS 3.x
 *
 * A simple plugin to restrict access to assets for permitted users only.
 *
 * @link      https://webworker.me/
 * @copyright Copyright (c) 2019 Andi Grether
 */

namespace vardump\secretassets\controllers;

use vardump\secretassets\Secretassets;

use Craft;
use craft\web\Controller;
use craft\helpers\FileHelper;
use yii\web\HttpException;

/**
 * DefaultController Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    Andi Grether
 * @package   Secretassets
 * @since     1.0.0
 */
class DefaultController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['download'];

    public function actionDownload(string $path)
    {
        // find the volume by the given path
        $volumes = Craft::$app->getVolumes();
        $publicVolumes = $volumes->getPublicVolumes();

        $targetVolume = null;
        $volumeUrl = null;
        foreach ($publicVolumes as $volume) {

            // get the public url of the volume
            $volumeUrl = $volume['url'];

            // strip any prefixing slashes
            $volumeUrl = ltrim($volumeUrl, '/');
            $volumeUrl = ltrim($volumeUrl, '//');
            $volumeUrl = rtrim($volumeUrl, '/');

            if (strpos($path, $volumeUrl . '/') === 0) {
                $targetVolume = $volume;
                break;
            }

        }

        if ($targetVolume === null) {
            throw new HttpException(404, "Sorry. File not found.");
            return;
        }

        // get the current user session
        $currentUser = Craft::$app->getUser();

        // check if the user is allowed to access the volume
        $access = $currentUser->checkPermission('viewvolume:' . $volume['uid']);

        if ($access === false) {
            throw new HttpException(404, "Sorry. Permission denied.");
            return;
        }

        // generate the filesystem path to the file
        $filepath = ltrim($path, $volumeUrl);
        $filepath = $volume['path'] . $filepath;
        $filepath = FileHelper::normalizePath($filepath);

        // get only the filename
        $filename = basename($filepath);

        // get the files mime type
        $mimeType = FileHelper::getMimeTypeByExtension($filename);

        // output asset
        header('Content-type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        readfile($filepath);
    }
}
