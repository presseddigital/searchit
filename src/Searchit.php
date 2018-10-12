<?php
namespace fruitstudios\searchit;

use fruitstudios\searchit\models\Settings;
use fruitstudios\searchit\plugin\Routes as SearchitRoutes;
use fruitstudios\searchit\plugin\Services as SearchitServices;
use fruitstudios\searchit\web\twig\CraftVariableBehavior;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\helpers\UrlHelper;
use craft\events\RegisterComponentTypesEvent;
use craft\events\PluginEvent;
use craft\web\twig\variables\CraftVariable;
use craft\web\View;

use craft\commerce\Plugin as Commerce;

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

    public $schemaVersion = '1.0.0';

    // Traits
    // =========================================================================

    use SearchitServices;
    use SearchitRoutes;

    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();

        self::$plugin = $this;
        self::$settings = Searchit::$plugin->getSettings();
        self::$devMode = Craft::$app->getConfig()->getGeneral()->devMode;
        self::$view = Craft::$app->getView();
        self::$commerceInstalled = class_exists(Commerce::class);

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

        Craft::info(Craft::t('searchit', '{name} plugin loaded', ['name' => $this->name]), __METHOD__);
    }

    public function beforeInstall(): bool
    {
        return true;
    }

    public function getSettingsResponse()
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


    public function afterInstallPlugin(PluginEvent $event)
    {
        $isCpRequest = Craft::$app->getRequest()->isCpRequest;
        if($event->plugin === $this && $isCpRequest)
        {
            Craft::$app->controller->redirect(UrlHelper::cpUrl('searchit/about'))->send();
        }
    }

    public function afterLoadPlugins(Event $event)
    {
        $isCpRequest = Craft::$app->getRequest()->isCpRequest;
        if($isCpRequest)
        {
            Searchit::$plugin->getElementFilters()->initElementFilters();
        }
    }

    // Protected Methods
    // =========================================================================

    protected function createSettingsModel()
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
        Event::on(Plugins::class, Plugins::EVENT_AFTER_LOAD_PLUGINS, [$this, 'afterLoadPlugins']);
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
