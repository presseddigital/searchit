<?php
namespace fruitstudios\searchit\plugin;

use Craft;
use fruitstudios\searchit\services\Cp;
use fruitstudios\searchit\services\Fields;
use fruitstudios\searchit\services\ElementFilters;

trait Services
{
    // Public Methods
    // =========================================================================

    public function getCp(): Cp
    {
        return $this->get('cp');
    }

    public function getElementFilters(): ElementFilters
    {
        return $this->get('elementFilters');
    }

    public function getFields(): Fields
    {
        return $this->get('fields');
    }

    // Private Methods
    // =========================================================================

    private function _setPluginComponents()
    {
        $this->setComponents([
            'cp' => Cp::class,
            'fields' => Fields::class,
            'elementFilters' => ElementFilters::class,
        ]);
    }
}
