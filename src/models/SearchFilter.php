<?php
namespace fruitstudios\searchit\models;

use Craft;
use craft\base\Model;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use craft\db\Query;

class SearchFilter extends Model
{
    private $_searchFilters;

    // Public Properties
    // =========================================================================

    public $id;
    public $elementId;
	public $filters;

    // Public Methods
    // =========================================================================

	public function rules(): array
    {
        $rules = parent::rules();
        $rules[] = [['name', 'type'], 'string'];
        $rules[] = [['name', 'type'], 'required'];
        $rules[] = ['filters', 'validateFilters'];
        return $rules;
    }

    public function validateFilters()
    {
        $fieldType = $this->getFieldTypeTemplate();
        if($fieldType && !$fieldType->validate())
        {
            $this->addError('settings', $fieldType->getErrors());
        }
    }

    public function getFiltersHtml()
    {
        return '<p>Filters</p>';
    }

    public function getFieldInputPreviewHtml()
    {
        // Get field type for this template and add any errors to it
        $fieldType = $this->getFieldTypeTemplate();
        return $fieldType ? $fieldType->getInputPreviewHtml() : '';
    }


    public function getFilters()
    {
        // return is_string($settings) ? Json::decodeIfJson($settings) : ($settings ?? []);
    }

    // Private Methods
    // =========================================================================



}
