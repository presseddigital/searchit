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
    public $filterType = 'manual'; // custom, dynamic
    public $manual;
    public $dynamic;
    public $sortOrder;

    // Public Methods
    // =========================================================================

	public function rules(): array
    {
        $filterOptionsRequiredMessage = Craft::t('searchit', 'Filter options cannot be blank');

        $rules = parent::rules();
        $rules[] = [['type', 'source', 'name', 'filterType'], 'string'];
        $rules[] = [['type', 'source', 'name', 'filterType'], 'required'];
        $rules[] = ['manual', 'required', 'when' => [$this, 'isManualFilterType'], 'message' => $filterOptionsRequiredMessage];
        $rules[] = ['dynamic', 'required', 'when' => [$this, 'isDynamicFilterType'], 'message' => $filterOptionsRequiredMessage];

        return $rules;
    }

    public function isManualFilterType()
    {
        return $this->filterType == 'manual';
    }

    public function isDynamicFilterType()
    {
        return $this->filterType == 'dynamic';
    }

    public function getOptions()
    {
        $options = [
            '' => $this->name
        ];

        $filters = [];
        switch ($this->filterType)
        {
            case 'manual':
                $filters = is_string($this->manual) ? Json::decodeIfJson($this->manual, true) : [];
                foreach ($filters as $k => $v)
                {
                    $filters[$k]['filter'] = Json::decodeIfJson($v['filter'], true);
                }
                break;

            case 'dynamic':
                $view = Craft::$app->getView();
                $currentTemplateMode = $view->getTemplateMode();
                $view->setTemplateMode($view::TEMPLATE_MODE_SITE);
                $filters = is_string($this->dynamic) ? Json::decodeIfJson('[' . Craft::$app->getView()->renderString($this->dynamic) . ']', true) : [];
                $view->setTemplateMode($currentTemplateMode);
                break;
        }

        if(is_array($filters) && !empty($filters))
        {
            foreach ($filters as $filter)
            {
                $options[Json::encode($filter['filter'])] = $filter['label'];
            }
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
