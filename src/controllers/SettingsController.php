<?php
namespace fruitstudios\searchit\controllers;

use fruitstudios\searchit\Searchit;

use Craft;
use craft\web\Controller;
use craft\helpers\StringHelper;

use yii\web\Response;

class SettingsController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionGeneral()
    {
        return $this->renderTemplate('searchit/settings/general', [
            'settings' => Searchit::$settings,
        ]);
    }

    public function actionFilters($type)
    {
        return $this->renderTemplate('searchit/settings/filters', [
        	'type' => $type,
        	'settings' => Searchit::$settings,
        ]);
    }
}
