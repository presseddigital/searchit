<?php
namespace fruitstudios\colorit\controllers;

use fruitstudios\colorit\Colorit;
use fruitstudios\colorit\models\Preset;

use Craft;
use craft\web\Controller;
use craft\helpers\StringHelper;

use yii\web\Response;

class PresetsController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionIndex(): Response
    {
        $presets = Colorit::$plugin->getPresets()->getAllPresets();

        return $this->renderTemplate('colorit/settings/presets/index', compact('presets'));
    }

    public function actionEdit(int $presetId = null, Preset $preset = null): Response
    {
        if (!$preset)
        {
            if ($presetId)
            {
                $preset = Colorit::$plugin->getPresets()->getPresetById($presetId);
                if (!$preset)
                {
                    throw new HttpException(404);
                }
            }
            else
            {
                $preset = new Preset();
            }
        }

        $isNewPreset = !$preset->id;

        $allPresetsTypes = Colorit::$plugin->getPresets()->getAllPresetTypes();
        $presetTypeOptions = [];
        foreach ($allPresetsTypes as $class) {
            $presetTypeOptions[] = [
                'value' => $class,
                'label' => $class::displayName(),
            ];
        }

        if($isNewPreset && !$preset->type)
        {
            $preset->type = $allPresetsTypes[0];
        }

        return $this->renderTemplate('colorit/settings/presets/_edit', [
            'isNewPreset' => $isNewPreset,
            'preset' => $preset,
            'allPresetsTypes' => $allPresetsTypes,
            'presetTypeOptions' => $presetTypeOptions,
        ]);
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
            Craft::$app->getSession()->setError(Craft::t('colorit', 'Couldn’t save preset.'));

            // Send the plugin back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'preset' => $preset
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('colorit', 'Field template saved.'));

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
        return $this->asErrorJson(Craft::t('colorit', 'Could not delete preset'));
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
