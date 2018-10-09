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
    public $compactMode = true;
    public $maxFilterWidth;

    // Public Methods
    // =========================================================================

	public function rules(): array
    {
        return [
            ['pluginNameOverride', 'string'],
            ['pluginNameOverride', 'default', 'value' => 'Searchit'],
            ['hasCpSectionOverride', 'boolean'],
            ['hasCpSectionOverride', 'default', 'value' => false],
            ['maxFilterWidth', 'number',],
            ['compactMode', 'boolean'],
            ['compactMode', 'default', 'value' => false],
        ];
    }
}
