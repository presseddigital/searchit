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
    public $type;
    public $source = '*';
    public $name;
    public $filterType = 'custom'; // custom, dynamic, advanced
    public $custom;
    public $dynamic;
    public $advanced;
    public $sortOrder;

    // Public Methods
    // =========================================================================

	public function rules(): array
    {
        $filterOptionsRequiredMessage = Craft::t('searchit', 'Filter options cannot be blank');

        $rules = parent::rules();
        $rules[] = [['type', 'source', 'name', 'filterType'], 'string'];
        $rules[] = [['type', 'source', 'name', 'filterType'], 'required'];
        $rules[] = ['custom', 'required', 'when' => [$this, 'isCustomFilterType'], 'message' => $filterOptionsRequiredMessage];
        $rules[] = ['dynamic', 'required', 'when' => [$this, 'isDynamicFilterType'], 'message' => $filterOptionsRequiredMessage];
        $rules[] = ['advanced', 'required', 'when' => [$this, 'isAdvancedFilterType'], 'message' => $filterOptionsRequiredMessage];

        return $rules;
    }

    public function isCustomFilterType()
    {
        return $this->filterType == 'custom';
    }

    public function isAdvancedFilterType()
    {
        return $this->filterType == 'advanced';
    }

    public function isDynamicFilterType()
    {
        return $this->filterType == 'dynamic';
    }

    public function validateOptions()
    {
        switch ($this->filterType)
        {
            case 'custom':
                // $this->addError('dynamic', Craft::t('searchit', 'This is required'));
                break;
            case 'dynamic':
                // $this->addError('dynamic', Craft::t('searchit', 'This is required'));
                break;
            case 'advanced':

                break;
        }

    }

    public function getOptions()
    {
        $options = [
            '' => $this->name
        ];

        switch ($this->filterType)
        {
            case 'custom':
                $filters = is_array($this->custom) ? $this->custom : [];
                break;
            case 'dynamic':

                $view = Craft::$app->getView();
                $currentTemplateMode = $view->getTemplateMode();
                $view->setTemplateMode($view::TEMPLATE_MODE_SITE);

                $filters = Json::decodeIfJson('[' . Craft::$app->getView()->renderString($this->dynamic) . ']', true);
                $filters = is_array($filters) ? $filters : [];

                $view->setTemplateMode($currentTemplateMode);
                break;

            case 'advanced':
                $filters = [];
                break;
        }

        foreach ($filters as $filter)
        {
            $options[$filter['filter']] = $filter['label'];

        }

        return $options;
    }

    public function getPreview()
    {
        return Craft::$app->getView()->renderTemplateMacro('_includes/forms', 'select', [
            [
                'options' => $this->getOptions(),
            ]
        ]);
    }


}
