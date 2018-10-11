<?php
namespace fruitstudios\searchit\services;

use fruitstudios\searchit\Searchit;

use Craft;
use craft\base\Component;

class Cp extends Component
{
    // Properties
    // =========================================================================

    // Public Methods
    // =========================================================================

    public function getNav()
    {
        $items = [];

        foreach (Searchit::$plugin->getElementFilters()->getSupportedElementTypes() as $elementType)
        {
            $items[$elementType['handle']] = [ 'heading' => $elementType['label'] ];
            $items['filters/'.$elementType['handle'].'/global'] = [ 'title' => Craft::t('searchit', 'Global')  ];
            if($elementType['sources'])
            {
                foreach ($elementType['sources'] as $source)
                {
                    $items['filters/'.$elementType['handle'].'/'.$source['handle']] = [ 'title' => $source['label'] ];
                }
            }
        }

        $items['general'] = [ 'heading' => Craft::t('searchit', 'General') ];
        $items['settings'] = [ 'title' => Craft::t('searchit', 'Settings') ];
        $items['about'] = [ 'title' => Craft::t('searchit', 'About') ];

        return $items;
    }

    // Private Methods
    // =========================================================================

}
