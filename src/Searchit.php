<?php
/**
 * Searchit plugin for Craft CMS 4.x
 *
 * A super simple field type which allows you toggle existing field types.
 *
 * @link      https://fruitstudios.co.uk
 * @copyright Copyright (c) 2018 Fruit Studios
 */

namespace fruitstudios\searchit;

use fruitstudios\searchit\models\Settings;
use fruitstudios\searchit\plugin\Routes as SearchitRoutes;
use fruitstudios\searchit\plugin\Services as SearchitServices;
use fruitstudios\searchit\web\twig\CraftVariableBehavior;
use fruitstudios\searchit\web\assets\searchit\SearchitAssetBundle;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\services\Fields;
use craft\helpers\StringHelper;
use craft\helpers\UrlHelper;
use craft\helpers\Json;
use craft\events\RegisterComponentTypesEvent;
use craft\events\PluginEvent;
use craft\events\TemplateEvent;
use craft\web\twig\variables\CraftVariable;
use craft\web\View;

use craft\commerce\Plugin as CommercePlugin;

use yii\base\Event;

/**
 * Class Searchit
 *
 * @author    Fruit Studios
 * @package   Searchit
 * @since     1.0.0
 *
 */
class Searchit extends Plugin
{
    // Static Properties
    // =========================================================================

    public static $plugin;
    public static $settings;
    public static $devMode;
    public static $view;
    public static $commerceInstalled;

    // Public Properties
    // =========================================================================

    public string $schemaVersion = '1.0.4';

    // Traits
    // =========================================================================

    use SearchitServices;
    use SearchitRoutes;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        self::$plugin = $this;
        self::$settings = Searchit::$plugin->getSettings();
        self::$devMode = Craft::$app->getConfig()->getGeneral()->devMode;
        self::$view = Craft::$app->getView();
        self::$commerceInstalled = class_exists(CommercePlugin::class);

        $this->name = Searchit::$settings->pluginNameOverride;
        $this->hasCpSection = Searchit::$settings->hasCpSectionOverride;

        $this->_setPluginComponents(); // See Trait
        $this->_registerCpRoutes(); // See Trait
        $this->_addTwigExtensions();
        $this->_registerFieldTypes();
        $this->_registerPermissions();
        $this->_registerEventListeners();
        $this->_registerWidgets();
        $this->_registerVariables();
        $this->_registerElementTypes();

        if($this->isInstalled)
        {
            $this->initElementFilters();
        }

        // Craft::dd(\craft\commerce\Plugin::getInstance());

        Craft::info(Craft::t('searchit', '{name} plugin loaded', ['name' => $this->name]), __METHOD__);
    }

    public function afterInstallPlugin(PluginEvent $event)
    {
        $isCpRequest = Craft::$app->getRequest()->isCpRequest;
        if ($event->plugin === $this && $isCpRequest)
        {
            Craft::$app->controller->redirect(UrlHelper::cpUrl('searchit/about'))->send();
        }
    }

    public function getSettingsResponse(): mixed
    {
        return Craft::$app->controller->redirect(UrlHelper::cpUrl('searchit/settings'));
    }

    public function getGitHubUrl(string $append = '')
    {
        return 'https://github.com/fruitstudios/craft-'.$this->handle.$append;
    }

    public function isCommerceEnabled()
    {
        return self::$commerceInstalled && Craft::$app->getPlugins()->isPluginEnabled('commerce');
    }

    public function initElementFilters()
    {
        if (!Craft::$app->getRequest()->getIsCpRequest())
        {
            return false;
        }

        Event::on(View::class, View::EVENT_BEFORE_RENDER_TEMPLATE, function (TemplateEvent $event) {

            $request = Craft::$app->getRequest();
            $general = Craft::$app->getConfig()->getGeneral();
            $js = [
                'id' => StringHelper::UUID(),
                'compactMode' => (bool) self::$settings->compactMode,
                'debug' => $general->devMode,
                'csrfTokenName' => $general->csrfTokenName,
                'csrfTokenValue' => $request->getCsrfToken(),
            ];

            $view = Craft::$app->getView();
            $view->registerAssetBundle(SearchitAssetBundle::class);
            $view->registerJs('new ElementFilters('.Json::encode($js).');');

            if(self::$settings->compactMode)
            {
                $view->registerCss('
                    .elementindex:not(.searchit--compactMode) .toolbar .statusmenubtn { font-size: 0; }
                    .elementindex:not(.searchit--compactMode) .toolbar .statusmenubtn::after { font-size: 14px; }
                    .elementindex:not(.searchit--compactMode) .toolbar .statusmenubtn .status { vertical-align: middle; margin-right: 0; }
                    .elementindex:not(.searchit--compactMode) .toolbar .sortmenubtn { font-size: 0; }
                    .elementindex:not(.searchit--compactMode) .toolbar .sortmenubtn::before,
                    .elementindex:not(.searchit--compactMode) .toolbar .sortmenubtn::after { font-size: 14px; }
                    .elementindex:not(.searchit--compactMode) .toolbar .spinner { position: absolute; right: 76px; top: 0px; }
                    body.ltr .elementindex:not(.searchit--compactMode) .sortmenubtn[data-icon]:not(:empty):before { margin-right: 0; }
                ');
            }

            $view->registerCss('
                .elementindex.searchit--compactMode-on .toolbar .statusmenubtn { font-size: 0; }
                .elementindex.searchit--compactMode-on .toolbar .statusmenubtn::after { font-size: 14px; }
                .elementindex.searchit--compactMode-on .toolbar .statusmenubtn .status { vertical-align: middle; margin-right: 0; }
                .elementindex.searchit--compactMode-on .toolbar .sortmenubtn { font-size: 0; }
                .elementindex.searchit--compactMode-on .toolbar .sortmenubtn::before,
                .elementindex.searchit--compactMode-on .toolbar .sortmenubtn::after { font-size: 14px; }
                .elementindex.searchit--compactMode-on .toolbar .spinner { position: absolute; right: 76px; top: 0px; }
                body.ltr .elementindex.searchit--compactMode-on .sortmenubtn[data-icon]:not(:empty):before { margin-right: 0; }
            ');

            if(self::$settings->maxFilterWidth)
            {
                $view->registerCss('.toolbar .searchit--filters select { max-width: '.self::$settings->maxFilterWidth.'px; }');
            }

        });
    }

    // Protected Methods
    // =========================================================================

    protected function createSettingsModel(): ?craft\base\Model
    {
        return new Settings();
    }

    // Private Methods
    // =========================================================================

    private function _addTwigExtensions()
    {
        // Craft::$app->view->registerTwigExtension(new Extension);
    }

    private function _registerPermissions()
    {
        // Event::on(UserPermissions::class, UserPermissions::EVENT_REGISTER_PERMISSIONS, function(RegisterUserPermissionsEvent $event) {
        //     $productTypes = Plugin::getInstance()->getProductTypes()->getAllProductTypes();
        //     $productTypePermissions = [];
        //     foreach ($productTypes as $id => $productType) {
        //         $suffix = ':' . $id;
        //         $productTypePermissions['commerce-manageProductType' . $suffix] = ['label' => Craft::t('commerce', 'Manage “{type}” products', ['type' => $productType->name])];
        //     }
        //     $event->permissions[Craft::t('commerce', 'Craft Commerce')] = [
        //         'commerce-manageProducts' => ['label' => Craft::t('commerce', 'Manage products'), 'nested' => $productTypePermissions],
        //         'commerce-manageOrders' => ['label' => Craft::t('commerce', 'Manage orders')],
        //         'commerce-managePromotions' => ['label' => Craft::t('commerce', 'Manage promotions')],
        //         'commerce-manageSubscriptions' => ['label' => Craft::t('commerce', 'Manage subscriptions')],
        //         'commerce-manageShipping' => ['label' => Craft::t('commerce', 'Manage shipping')],
        //         'commerce-manageTaxes' => ['label' => Craft::t('commerce', 'Manage taxes')],
        //     ];
        // });
    }

    private function _registerEventListeners()
    {
        Event::on(Plugins::class, Plugins::EVENT_AFTER_INSTALL_PLUGIN, [$this, 'afterInstallPlugin']);

        // Event::on(Sites::class, Sites::EVENT_AFTER_SAVE_SITE, [$this->getServiceName(), 'functionToCall']);

        // if (!Craft::$app->getRequest()->getIsConsoleRequest()) {
        //     Event::on(UserElement::class, UserElement::EVENT_AFTER_SAVE, [$this->getFunction(), 'functionToCall']);
        //     Event::on(User::class, User::EVENT_AFTER_LOGIN, [$this->getCustomers(), 'loginHandler']);
        //     Event::on(User::class, User::EVENT_AFTER_LOGOUT, [$this->getCustomers(), 'logoutHandler']);
        // }
    }

    private function _registerFieldTypes()
    {
        // Event::on(Fields::className(), Fields::EVENT_REGISTER_FIELD_TYPES, function(RegisterComponentTypesEvent $event) {
        //     $event->types[] = SearchitField::class;
        // });
    }

    private function _registerWidgets()
    {
        // Event::on(Dashboard::class, Dashboard::EVENT_REGISTER_WIDGET_TYPES, function(RegisterComponentTypesEvent $event) {
        //     $event->types[] = Example::class;
        // });
    }

    private function _registerVariables()
    {
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event) {
            /** @var CraftVariable $variable */
            $variable = $event->sender;
            $variable->attachBehavior('searchit', CraftVariableBehavior::class);
        });
    }

    private function _registerElementTypes()
    {
        // Event::on(Elements::class, Elements::EVENT_REGISTER_ELEMENT_TYPES, function(RegisterComponentTypesEvent $e) {
        //     $e->types[] = Example::class;
        // });
    }
}
