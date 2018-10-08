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
        $elementFilters = Searchit::$plugin->getElementFilters()->getElementFiltersByType($elementType, $sourceHandle);

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

                $elementType = ElementHelper::getElementTypeByHandle($elementTypeHandle);
                $source = Searchit::$plugin->getElementFilters()->getSourceInfo($elementType, $sourceHandle);
                if (!$elementType || !$source)
                {
                    throw new HttpException(404);
                }

                $elementFilter->elementType = $elementType;
                $elementFilter->source = $source['key'];
            }
        }

        $isNewElementFilter = !$elementFilter->id;

        return $this->renderTemplate('searchit/filters/_edit', compact(
            'elementTypeHandle',
            'sourceHandle',
            'isNewElementFilter',
            'elementFilter'
        ));
    }

    public function actionSave()
    {
        $this->requirePostRequest();

        $presetsService = Colorit::$plugin->getPresets();
        $request = Craft::$app->getRequest();
        $type = $request->getRequiredBodyParam('type');

        $preset = $presetsService->createPreset([
            'type' => $type,
            'id' => $request->getBodyParam('presetId'),
            'name' => $request->getBodyParam('name'),
            'settings' => $request->getBodyParam('types.'.$type),
        ]);

        if (!Colorit::$plugin->getPresets()->savePreset($preset)) {
            Craft::$app->getSession()->setError(Craft::t('searchit', 'Couldn’t save preset.'));

            // Send the plugin back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'preset' => $preset
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('searchit', 'Field template saved.'));

        return $this->redirectToPostedUrl();
    }

    public function actionDelete(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $id = Craft::$app->getRequest()->getRequiredBodyParam('id');

        if (Colorit::$plugin->getPresets()->deletePresetById($id))
        {
            return $this->asJson(['success' => true]);
        }
        return $this->asErrorJson(Craft::t('searchit', 'Could not delete preset'));
    }

    // Private Methods
    // =========================================================================

    private function _getPresetModel(string $type, array $attributes = [])
    {
        try {
            $preset = Craft::createObject($type);
            return Craft::configure($preset, $attributes);
        } catch(ErrorException $exception) {
            $error = $exception->getMessage();
            return false;
        }
    }

}
