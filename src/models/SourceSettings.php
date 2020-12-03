<?php
namespace presseddigital\searchit\models;

use presseddigital\searchit\Searchit;

use Craft;
use craft\base\Model;

class SourceSettings extends Model
{
    // Public Properties
    // =========================================================================

    public $type;
    public $source;

    public $hideGlobalFilters = false;

    // Public Methods
    // =========================================================================

	public function rules(): array
    {
        return [
            [['type', 'source'], 'string'],
            [['type', 'source'], 'required'],
            ['hideGlobalFilters', 'boolean'],
            ['hideGlobalFilters', 'default', 'value' => false],
        ];
    }
}
