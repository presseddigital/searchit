<?php
namespace fruitstudios\searchit\models;

use fruitstudios\searchit\Searchit;

use Craft;
use craft\base\Model;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use craft\db\Query;

class ElementFilter extends Model
{
    // Public Properties
    // =========================================================================

    public $id;
    public $elementType;
    public $source = '*';
    public $name;
    public $type = 'custom'; // custom, json, advanced
    public $settings;

    // Public Methods
    // =========================================================================

	public function rules(): array
    {
        $rules = parent::rules();
        $rules[] = [['elementType', 'source', 'name', 'type'], 'string'];
        $rules[] = [['elementType', 'source', 'name', 'type'], 'required'];
        $rules[] = ['settings', 'validateSettings'];

        return $rules;
    }

    public function validateSettings()
    {
        switch ($this->type)
        {
            case 'custom':
                break;
            case 'json':
                break;
            case 'advanced':
                break;
        }

    }

    public function getPreview()
    {
        return '<p>Select</p>';
    }

}
