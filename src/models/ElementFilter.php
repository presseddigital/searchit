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
    public $type = 'custom'; // custom, json, special
    public $custom;
    public $json;
	public $special;

    // Public Methods
    // =========================================================================

	public function rules(): array
    {
        $rules = parent::rules();
        $rules[] = [['elementType', 'source', 'name', 'type'], 'string'];
        $rules[] = [['elementType', 'source', 'name', 'type'], 'required'];
        $rules[] = ['custom', 'validateCustom'];
        $rules[] = ['json', 'validateJson'];
        $rules[] = ['special', 'validateSpecial'];
        return $rules;
    }

    public function validateCustom()
    {
        if($this->type == 'custom')
        {

        }
    }

    public function validateJson()
    {
        if($this->type == 'json')
        {

        }
    }

    public function validateSpecial()
    {
        if($this->type == 'special')
        {

        }
    }

}
