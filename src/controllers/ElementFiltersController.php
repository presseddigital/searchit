<?php
namespace fruitstudios\searchit\controllers;

use fruitstudios\searchit\Searchit;
use fruitstudios\searchit\helpers\ElementHelper;
use fruitstudios\searchit\models\ElementFilter;

use Craft;
use craft\web\Controller;
use craft\helpers\StringHelper;

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
            'custom' => $request->getBodyParam('custom'),
            'dynamic' => $request->getBodyParam('dynamic'),
            'advanced' => $request->getBodyParam('advanced'),
            'sortOrder' => $request->getBodyParam('sortOrder', 1),
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

}
