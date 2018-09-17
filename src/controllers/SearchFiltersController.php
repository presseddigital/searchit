<?php
namespace fruitstudios\searchit\controllers;

use fruitstudios\searchit\Searchit;

use Craft;
use craft\web\Controller;

class SearchFiltersController extends Controller
{
    public function actionIndex()
    {
        // /admin/actions/searchit/search-filters
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $type = Craft::$app->getRequest()->getRequiredParam('type');
        $handle = Craft::$app->getRequest()->getParam('handle', 'global');

        $filter = Searchit::$settings->getFilterSelectHtml($type, $handle);
        if($filter)
        {
            return $this->asJson([
                'success' => true,
                'type' => $type,
                'handle' => $handle,
                'filter' => $filter,
            ]);
        }
        return $this->asJson(['success' => false]);
    }
}
