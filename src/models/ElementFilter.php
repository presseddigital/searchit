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
    public $filterType = 'dynamic';
    public $settings;
    public $sortOrder;

    // Public Methods
    // =========================================================================

	public function rules(): array
    {
        $rules = parent::rules();
        $rules[] = [['type', 'source', 'name', 'filterType'], 'string'];
        $rules[] = [['type', 'source', 'name', 'filterType'], 'required'];
        $rules[] = ['settings', 'validateSettings', 'skipOnEmpty' => false];
        return $rules;
    }

    public function validateSettings()
    {
        switch ($this->filterType)
        {
            case 'dynamic':
                if(!$this->settings)
                {
                    $this->addError('dynamic', Craft::t('searchit', 'Filter options cannot be blank'));
                }
                else
                {
                    try {
                        Craft::$app->getView()->renderString($this->settings);
                    } catch (\Twig_Error_Syntax $e) {
                        $this->addError('dynamic', Craft::t('searchit', 'Looks like you have errors in you twig syntax'));
                    }
                }
                break;

            case 'manual':
                if(!is_array($this->settings) || empty($this->settings))
                {
                    $this->addError('manual', Craft::t('searchit', 'Filter options cannot be blank'));
                }
                else
                {
                    foreach ($this->settings as $i => $row)
                    {
                        $label = $row['label'] ?? '';
                        if(!$label)
                        {
                            $labelError = true;
                            $this->addError('manual', Craft::t('searchit', 'Row {row} {error}', [
                                'row' => ($i + 1),
                                'error' => Craft::t('searchit', 'label cannot be blank'),
                            ]));
                        }

                        $filter = $row['filter'] ?? '';
                        if(!$filter)
                        {
                            $filterError = true;
                            $this->addError('manual', Craft::t('searchit', 'Row {row} {error}', [
                                'row' => ($i + 1),
                                'error' => Craft::t('searchit', 'filter cannot be blank'),
                            ]));
                        }

                        $this->settings[$i] = [
                            'label' => [
                                'value' => $label,
                                'hasErrors' => $labelError ?? '',
                            ],
                            'filter' => [
                                'value' => $filter,
                                'hasErrors' => $filterError ?? '',
                            ]
                        ];

                    }
                }
                break;
        }

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
                $filters = $this->settings;
                foreach ($filters as $k => $v)
                {
                    if($v['filter']['value'] ?? false)
                    {
                         $filters[$k]['filter'] = Json::decodeIfJson($filters[$k]['filter']['value'], true);
                    }
                    else
                    {
                        $filters[$k]['filter'] = Json::decodeIfJson($filters[$k]['filter'], true);
                    }
                }
                break;

            case 'dynamic':
                $view = Craft::$app->getView();
                $currentTemplateMode = $view->getTemplateMode();
                $view->setTemplateMode($view::TEMPLATE_MODE_SITE);
                try {
                    $filters = is_string($this->settings) ? Json::decodeIfJson('[' . Craft::$app->getView()->renderString($this->settings) . ']', true) : [];
                } catch (\Exception $e) {
                    $filters = [];
                }
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
