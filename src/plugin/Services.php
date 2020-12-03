<?php
namespace presseddigital\searchit\plugin;

use Craft;
use presseddigital\searchit\services\Cp;
use presseddigital\searchit\services\ElementFilters;

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

    // Private Methods
    // =========================================================================

    private function _setPluginComponents()
    {
        $this->setComponents([
            'cp' => Cp::class,
            'elementFilters' => ElementFilters::class,
        ]);
    }
}
