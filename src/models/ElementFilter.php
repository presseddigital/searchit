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
    public $heading;
    public $type = 'custom'; // custom, json, special
    public $custom;
    public $json;
	public $special;

    // Public Methods
    // =========================================================================

	public function rules(): array
    {
        $rules = parent::rules();
        $rules[] = [['name', 'type'], 'string'];
        $rules[] = [['name', 'type'], 'required'];
        $rules[] = [['settings'], 'validateFieldTypeSettings'];
        return $rules;
    }

    public function validateFieldTypeSettings()
    {
        $fieldType = $this->getFieldTypeTemplate();
        if($fieldType && !$fieldType->validate())
        {
            $this->addError('settings', $fieldType->getErrors());
        }
    }

    public function getOptions()
    {
        return $fieldType ? $fieldType->getSettingsHtml() : '';
    }

    public function getFieldInputPreviewHtml()
    {
        // Get field type for this template and add any errors to it
        $fieldType = $this->getFieldTypeTemplate();
        return $fieldType ? $fieldType->getInputPreviewHtml() : '';
    }


    public function getSettings()
    {
        return $this->normalizeSettings($this->settings);
    }

    public function normalizeSettings($settings)
    {
        if(is_array($settings))
        {
            return $settings;
        }

        return is_string($settings) ? Json::decodeIfJson($settings) : ($settings ?? []);
    }

    public function isInUse()
    {
        return $this->getFieldsUsing() ?? false;
    }

    public function getFieldsUsing()
    {
        if(!is_null($this->_fieldsUsing))
        {
            return $this->_fieldsUsing;
        }

        $fieldsOfType = Colorit::$plugin->getFields()->getFieldsByType($this->type);
        if($fieldsOfType)
        {
            foreach ($fieldsOfType as $fieldOfType)
            {
                if($this->id == $fieldOfType->presetId)
                {
                    $this->_fieldsUsing[] = $fieldOfType;
                }
            }
        }

        return $this->_fieldsUsing;
    }

    public function getFieldsUsingHtml()
    {
        $fields = $this->getFieldsUsing();
        if(!$fields)
        {
            return Craft::t('colorit', 'Not In Use');
        }

        $links = [];
        foreach($fields as $field)
        {
            $isOwnedByMatrix = false;
            if ($field['context'] != 'global')
            {
                $isOwnedByMatrix = true;
                $_field = Colorit::$plugin->getFields()->getMatrixFieldByChildFieldId($field['id']);
            }
            else
            {
                $_field = Colorit::$plugin->getFields()->getFieldById($field['id']);
            }

            if($_field)
            {
                $links[] = '<a href="'.UrlHelper::cpUrl('settings/fields/edit/'.$_field->id).'">'.$_field->name.($isOwnedByMatrix ? ' ('.$field['name'].')' : '').'</a>';
            }
        }
        return '<p>'.implode(', ', $links).'</p>';
    }

    public function getFieldType()
    {
        if(!is_null($this->_fieldType))
        {
            return $this->_fieldType;
        }

        if(!$this->type)
        {
            return false;
        }

        $this->_fieldType = Craft::$app->getFields()->createField([
            'type' => $this->type,
            'settings' => $this->getSettings(),
        ]);
        return $this->_fieldType;
    }

    public function getFieldTypeTemplate()
    {
        if(!is_null($this->_fieldTypeTemplate))
        {
            return $this->_fieldTypeTemplate;
        }

        if(!$this->type)
        {
            return false;
        }

        $this->_fieldTypeTemplate = Craft::$app->getFields()->createField([
            'type' => $this->type,
            'settings' => array_merge($this->getSettings(), [ 'presetMode' => true ]),
        ]);
        return $this->_fieldTypeTemplate;
    }

    // Private Methods
    // =========================================================================



}
