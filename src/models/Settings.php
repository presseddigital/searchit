<?php
namespace fruitstudios\searchit\models;

use fruitstudios\searchit\Searchit;

use Craft;
use craft\base\Model;

class Settings extends Model
{
    // Private Properties
    // =========================================================================

    private $_filterSelects;

    // Public Properties
    // =========================================================================

    public $pluginNameOverride = 'Searchit';
	public $hasCpSectionOverride = false;

    public $compactMode = true;

    public $entries;
    public $users;
    public $categories;
    public $assets;
    public $products;

    // Public Methods
    // =========================================================================

	public function rules(): array
    {
        return [
            ['pluginNameOverride', 'string'],
            ['pluginNameOverride', 'default', 'value' => 'Searchit'],
            ['compactMode', 'boolean'],
            ['compactMode', 'default', 'value' => false],
            ['hasCpSectionOverride', 'boolean'],
            ['hasCpSectionOverride', 'default', 'value' => false],
            [
                [
                    'entries',
                    'users',
                    'categories',
                    'assets',
                    'products'
                ],
                'validateFilters'
            ]
        ];
    }

    public function validateFilters()
    {
        return true;
    }

    public function getFilterSettingsHtml(string $type)
    {
        $html = '';
        $filterOptions = Searchit::$plugin->getSearchFilters()->getOptionsByType($type);
        if($filterOptions)
        {
            foreach ($filterOptions as $filterOption)
            {
                $html .= $this->_getFilterSettingsHtml([
                    'id' => $filterOption['id'],
                    'name' => $filterOption['key'],
                    'label' => $filterOption['label'],
                    'instructions' => $filterOption['instructions'],
                    'rows' => $this->$type[$filterOption['key']] ?? null,
                    'errors' => [],
                ]);

                $html .= $this->getFilterSelectHtml($type, $filterOption['key']);
            }
        }
        return $html;
    }

    public function getFilterSelectHtml(string $type, string $key = 'global')
    {
        if($this->_filterSelects[$type][$key] ?? false)
        {
            return $this->_filterSelects[$type][$key];
        }

        $elementName = Searchit::$plugin->getSearchFilters()->getElementNameByType($type);
        $settings = $this->$type ?? false;
        if(!($settings['enabled'] ?? false))
        {
            return false;
        }

        $filters = $settings['global'] ?? [];
        if ($key != 'global')
        {
            $filters = array_merge($filters, $settings[$key] ?? []);
        }

        if(!$filters)
        {
            return false;
        }

        $options = [
            [
                'value' => '',
                'label' => ucfirst($elementName).' Filters',
            ]
        ];

        foreach ($filters as $filter)
        {
            $options[] = [
                'value' => $filter['filter'],
                'label' => $filter['name'],
            ];
        }

        $this->_filterSelects[$type][$key] = Craft::$app->getView()->renderTemplateMacro('_includes/forms', 'select', [
            [
                'id' => '',
                'name' => 'searchFilters',
                'options' => $options,
            ]
        ]);

        return $this->_filterSelects[$type][$key];
    }

    // Private Methods
    // =========================================================================

    private function _getFilterSettingsHtml(array $settings)
    {
        $defaults = [
            'textual' => false,
            'addRowLabel' => Craft::t('searchit', 'Add search filter'),
            'cols' => [
                'name' => [
                    'heading' => Craft::t('searchit', 'Name'),
                    'type' => 'singleline',
                    'width' => '30%',
                ],
                'filter' => [
                    'heading' => Craft::t('searchit', 'Filter'),
                    'type' => 'singleline',
                ]
            ],
            'errors' => []
        ];
        return Craft::$app->getView()->renderTemplateMacro('_includes/forms', 'editableTableField', [array_merge($defaults, $settings)]);
    }

}
