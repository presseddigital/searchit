<?php
namespace fruitstudios\searchit\plugin;

use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use yii\base\Event;

trait Routes
{
    // Private Methods
    // =========================================================================

    private function _registerCpRoutes()
    {
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {

            $event->rules['searchit'] = ['template' => 'searchit/index'];
            $event->rules['searchit/settings'] = 'searchit/settings/index';
            $event->rules['searchit/filters/<elementTypeHandle:{handle}>/<sourceHandle:{slug}>'] = 'searchit/element-filters/index';
            $event->rules['searchit/filters/<elementTypeHandle:{handle}>/<sourceHandle:{slug}>/<elementFilterId:\d+>'] = 'searchit/element-filters/edit';
            $event->rules['searchit/filters/<elementTypeHandle:{handle}>/<sourceHandle:{slug}>/new'] = 'searchit/element-filters/edit';

        });
    }
}
