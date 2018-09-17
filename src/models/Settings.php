<?php
namespace fruitstudios\searchit\models;

use fruitstudios\searchit\Searchit;

use Craft;
use craft\base\Model;

class Settings extends Model
{
    // Public Properties
    // =========================================================================

    public $pluginNameOverride = 'Searchit';
	public $hasCpSectionOverride = false;

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
                    'id' => $filterOption['handle'],
                    'name' => $filterOption['handle'],
                    'label' => $filterOption['label'],
                    'instructions' => $filterOption['instructions'],
                    'rows' => $this->$type[$filterOption['handle']] ?? null,
                    'errors' => [],
                ]);
            }
        }
        return $html;
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
