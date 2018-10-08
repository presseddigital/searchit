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
    public $type = 'custom'; // custom, dynamic, advanced
    public $settings;
    public $sortOrder;

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
            case 'dynamic':

                if(empty($this->settings))
                {
                    $this->addError('settings', [Craft::t('This is required')]);
                }


                break;
            case 'advanced':
                break;
        }

    }

    public function getSettingsAsOptions()
    {
        switch ($this->type)
        {
            case 'custom':
                return is_string($this->settings) ? Json::decodeIfJson($this->settings) : [];
                break;
            case 'dynamic':

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
