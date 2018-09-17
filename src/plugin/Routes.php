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
            $event->rules['searchit/settings/general'] = 'searchit/settings/general';
            $event->rules['searchit/settings/filters/<type:(entries|users|categories|assets|products)>'] = 'searchit/settings/filters';

            // $event->rules['searchit/settings/fieldtemplates'] = 'searchit/field-templates/index';
            // $event->rules['searchit/settings/fieldtemplates/<fieldTemplateId:\d+>'] = 'searchit/field-templates/edit';
            // $event->rules['searchit/settings/fieldtemplates/new'] = 'searchit/field-templates/edit';

        });
    }
}
