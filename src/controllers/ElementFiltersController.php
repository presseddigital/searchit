<?php
namespace presseddigital\searchit\controllers;

use presseddigital\searchit\Searchit;
use presseddigital\searchit\helpers\ElementHelper;
use presseddigital\searchit\models\ElementFilter;
use presseddigital\searchit\models\SourceSettings;

use Craft;
use craft\web\Controller;
use craft\helpers\StringHelper;
use craft\helpers\Json;

use yii\web\Response;

class ElementFiltersController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionIndex(string $elementTypeHandle, string $sourceHandle): Response
    {
        $elementType = ElementHelper::getElementTypeByHandle($elementTypeHandle);
        $element = Searchit::$plugin->getElementFilters()->getElementInfo($elementType);
        $source = Searchit::$plugin->getElementFilters()->getSourceInfo($elementType, $sourceHandle);
        $elementFilters = Searchit::$plugin->getElementFilters()->getElementFiltersBySource($elementType, $source['key']);

        return $this->renderTemplate('searchit/filters/index', compact(
            'elementTypeHandle',
            'sourceHandle',
            'elementType',
            'element',
            'source',
            'elementFilters'
        ));
    }

    public function actionGet()
    {
        $this->requireAcceptsJson();

        $request = Craft::$app->getRequest();

        $elementType = $request->getBodyParam('type', false);
        $sourceKeyOrHandle = $request->getBodyParam('source', false);

        $filters = false;
        if($elementType && $sourceKeyOrHandle)
        {
            $filters = Searchit::$plugin->getElementFilters()->getElementFiltersForUse($elementType, $sourceKeyOrHandle);
        }

        return $this->asJson([ 'filters' => $filters ]);
    }

    public function actionEdit(string $elementTypeHandle, string $sourceHandle, int $elementFilterId = null, ElementFilter $elementFilter = null): Response
    {
        $elementType = ElementHelper::getElementTypeByHandle($elementTypeHandle);
        $element = Searchit::$plugin->getElementFilters()->getElementInfo($elementType);
        $source = Searchit::$plugin->getElementFilters()->getSourceInfo($elementType, $sourceHandle);
        if (!$elementType || !$source)
        {
            throw new HttpException(404);
        }

        if (!$elementFilter)
        {
            if ($elementFilterId)
            {
                $elementFilter = Searchit::$plugin->getElementFilters()->getElementFilterById($elementFilterId);
                if (!$elementFilter)
                {
                    throw new HttpException(404);
                }
            }
            else
            {
                $elementFilter = new ElementFilter();
                $elementFilter->type = $elementType;
                $elementFilter->source = $source['key'];
            }
        }

        $isNewElementFilter = !$elementFilter->id;

        return $this->renderTemplate('searchit/filters/_edit', compact(
            'elementTypeHandle',
            'sourceHandle',
            'elementType',
            'element',
            'source',
            'isNewElementFilter',
            'elementFilter'
        ));
    }

    public function actionSave()
    {
        $this->requirePostRequest();

        $elementFiltersService = Searchit::$plugin->getElementFilters();
        $request = Craft::$app->getRequest();

        $type = $request->getRequiredBodyParam('type');
        $source = $request->getRequiredBodyParam('source');
        $filterType = $request->getRequiredBodyParam('filterType');

        $elementFilter = $elementFiltersService->createElementFilter([
            'id' => $request->getBodyParam('elementFilterId'),
            'type' => $type,
            'source' => $source,
            'name' => $request->getBodyParam('name'),
            'filterType' => $filterType,
            'settings' => $request->getBodyParam('settings.'.$filterType),
        ]);

        if (!$elementFiltersService->saveElementFilter($elementFilter)) {
            Craft::$app->getSession()->setError(Craft::t('searchit', 'Couldn’t save element filter.'));

            // Send the plugin back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'elementFilter' => $elementFilter
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('searchit', 'Element filter saved.'));

        return $this->redirectToPostedUrl();
    }

    public function actionDelete(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $id = Craft::$app->getRequest()->getRequiredBodyParam('id');

        if (Searchit::$plugin->getElementFilters()->deleteElementFilterById($id))
        {
            return $this->asJson(['success' => true]);
        }
        return $this->asErrorJson(Craft::t('searchit', 'Could not delete element filter'));
    }

    public function actionReorder(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $elementFilterIds = Json::decode(Craft::$app->getRequest()->getRequiredBodyParam('ids'));
        Searchit::$plugin->getElementFilters()->reorderElementFilters($elementFilterIds);

        return $this->asJson(['success' => true]);
    }

    public function actionSourceSettings(string $elementTypeHandle, string $sourceHandle, SourceSettings $sourceSettings = null): Response
    {
        $elementType = ElementHelper::getElementTypeByHandle($elementTypeHandle);
        $element = Searchit::$plugin->getElementFilters()->getElementInfo($elementType);
        $source = Searchit::$plugin->getElementFilters()->getSourceInfo($elementType, $sourceHandle);
        if (!$elementType || !$source)
        {
            throw new HttpException(404);
        }

        if (!$sourceSettings)
        {
            $sourceSettings = Searchit::$plugin->getElementFilters()->getSourceSettings($elementTypeHandle, $sourceHandle);
        }

        return $this->renderTemplate('searchit/filters/_settings', compact(
            'elementTypeHandle',
            'sourceHandle',
            'elementType',
            'element',
            'source',
            'sourceSettings'
        ));
    }

    public function actionSaveSourceSettings()
    {
        $this->requirePostRequest();

        $elementFiltersService = Searchit::$plugin->getElementFilters();
        $request = Craft::$app->getRequest();

        $type = $request->getRequiredBodyParam('type');
        $source = $request->getRequiredBodyParam('source');

        $sourceSettings = $elementFiltersService->createSourceSettings([
            'type' => $type,
            'source' => $source,
            'hideGlobalFilters' => $request->getBodyParam('hideGlobalFilters'),
        ]);

        if (!$elementFiltersService->saveSourceSettings($sourceSettings)) {
            Craft::$app->getSession()->setError(Craft::t('searchit', 'Couldn’t save source settings.'));

            // Send the plugin back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'sourceSettings' => $sourceSettings
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('searchit', 'Source settings saved.'));

        return $this->redirectToPostedUrl();
    }



}
