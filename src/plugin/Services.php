<?php
namespace fruitstudios\searchit\plugin;

use Craft;
use fruitstudios\searchit\services\Cp;
use fruitstudios\searchit\services\SearchFilters;

trait Services
{
    // Public Methods
    // =========================================================================

    public function getCp(): Cp
    {
        return $this->get('cp');
    }

    public function getSearchFilters(): SearchFilters
    {
        return $this->get('searchFilters');
    }

    // Private Methods
    // =========================================================================

    private function _setPluginComponents()
    {
        $this->setComponents([
            'cp' => Cp::class,
            'searchFilters' => SearchFilters::class,
        ]);
    }
}
