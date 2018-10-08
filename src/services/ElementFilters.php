<?php
namespace fruitstudios\searchit\services;

use fruitstudios\searchit\Searchit;
use fruitstudios\searchit\models\ElementFilter;
use fruitstudios\searchit\records\ElementFilter as ElementFilterRecord;
use fruitstudios\searchit\helpers\ElementHelper;

use Craft;
use craft\base\Component;
use craft\db\Query;

use craft\elements\Category;
use craft\elements\Entry;
use craft\elements\User;
use craft\elements\Asset;
use craft\commerce\elements\Product;

class ElementFilters extends Component
{
    // Properties
    // =========================================================================

    private $_supportedElementTypes;
    private $_supportedSourcesByElementType = [];

    private $_fetchedAllElementFilters;
    private $_elementFiltersById = [];
    private $_elementFiltersByType = [];
    private $_elementFiltersBySource = [];

    // Public Methods
    // =========================================================================

    public function getSourceInfo(string $elementType, string $sourceKeyOrHandle)
    {
        $supportedSources = $this->getSupportedSources($elementType);
        $handle = ElementHelper::sourceKeyAsHandle($sourceKeyOrHandle);
        return $supportedSources[$sourceKeyOrHandle] ?? false;
    }

    public function getElementInfo(string $elementType)
    {
        $supportedElementTypes = $this->getSupportedElementTypes();
        return $supportedElementTypes[$elementType] ?? false;
    }

    public function getSupportedElementTypes()
    {
        if(!is_null($this->_supportedElementTypes))
        {
            return $this->_supportedElementTypes;
        }

        $types = [];

        $types[User::class] = [
            'class' => User::class,
            'handle' => 'users',
            'label' => Craft::t('searchit', 'Users'),
            'displayName' => Craft::t('searchit', 'User'),
            'sources' => $this->getSupportedSources(User::class),
        ];

        $types[Entry::class] = [
            'class' => Entry::class,
            'handle' => 'entries',
            'label' => Craft::t('searchit', 'Entries'),
            'displayName' => Craft::t('searchit', 'Entry'),
            'sources' => $this->getSupportedSources(Entry::class),
        ];

        $types[Category::class] = [
            'class' => Category::class,
            'handle' => 'categories',
            'label' => Craft::t('searchit', 'Categories'),
            'displayName' => Craft::t('searchit', 'Category'),
            'sources' => $this->getSupportedSources(Category::class),
        ];

        $types[Asset::class] = [
            'class' => Asset::class,
            'handle' => 'assets',
            'label' => Craft::t('searchit', 'Assets'),
            'displayName' => Craft::t('searchit', 'Asset'),
            'sources' => $this->getSupportedSources(Asset::class),
        ];

        if(Searchit::$commerceInstalled)
        {
            $types[Product::class] = [
                'class' => Product::class,
                'handle' => 'products',
                'label' => Craft::t('searchit', 'Products'),
                'displayName' => Craft::t('searchit', 'Product'),
                'sources' => $this->getSupportedSources(Product::class),
            ];
        }

        $this->_supportedElementTypes = $types;
        return $this->_supportedElementTypes;
    }

    public function getSupportedSources(string $elementType)
    {
        if(isset($this->_supportedSourcesByElementType[$elementType]))
        {
            return $this->_supportedSourcesByElementType[$elementType];
        }

        $sources['global'] = [
            'label' => Craft::t('searchit', 'Global'),
            'key' => '*',
            'handle' => 'global',
        ];
        $allSources = Craft::$app->getElementIndexes()->getSources($elementType);
        if($allSources)
        {
            foreach ($allSources as $source)
            {
                if($source['key'] ?? false)
                {
                    $handle = ElementHelper::sourceKeyAsHandle($source['key']);
                    $skip = false;
                    switch($elementType)
                    {
                        case(Entry::class):
                            $skip = strpos($source['key'], 'section:') === false;
                            break;
                        case(User::class):
                            $skip = $source['key'] == '*';
                            break;
                    }

                    if($skip)
                    {
                        continue;
                    }

                    $sources[$handle] = [
                        'label' => $source['label'],
                        'key' => $source['key'],
                        'handle' => $handle,
                    ];
                }
            }
        }

        $this->_supportedSourcesByElementType[$elementType] = $sources;
        return $this->_supportedSourcesByElementType[$elementType];
    }

    public function getElementFilters(string $elementType = null, string $source = null)
    {
        if($this->_fetchedAllElementFilters)
        {
            return $this->_elementFiltersBySource[$elementType][$source] ?? $this->_elementFiltersByType[$elementType] ?? $this->_elementFiltersById;
        }

        $results = $this->_createElementFilterQuery()
            ->where(array_filter([
                'elementType' => $elementType,
                'source' => $source
            ]))
            ->all();

        if($results)
        {
            foreach($results as $result)
            {
                $elementFilter = new ElementFilter($result);
                $this->_elementFiltersById[$result['id']] = $elementFilter;
                $this->_elementFiltersByType[$result['elementType']][$result['id']] = $elementFilter;
                $this->_elementFiltersBySource[$result['elementType']][$result['source']][$result['id']] = $elementFilter;
            }
        }

         return $this->_elementFiltersBySource[$elementType][$source] ?? $this->_elementFiltersByType[$elementType] ?? $this->_elementFiltersById;
    }

    public function getAllElementFilters()
    {
        if($this->_fetchedAllElementFilters)
        {
            return $this->_elementFiltersById;
        }

        $this->getElementFilters();
        $this->_fetchedAllElementFilters = true;
        return $this->_elementFiltersById;
    }

    public function getActiveElementFiltersArray(string $type = null)
    {
        $settings = Searchit::$plugin->getSettings();
        $filters = [];

        $filters = [
            [
                'elementType' => Category::class,
                'source' => '*',
                'filters' => [
                    [
                        '' => 'Global',
                        'fg:1' => 'Filter 1',
                        'fg:2' => 'Filter 2',
                        'fg:3' => 'Filter 3',
                    ]
                ]
            ],
            [
                'elementType' => Category::class,
                'source' => 'group:1',
                'filters' => [
                    [
                        '' => 'Filter A',
                        'fa:1' => 'Filter 1',
                        'fa:2' => 'Filter 2',
                        'fa:3' => 'Filter 3',
                    ],
                    [
                        '' => 'Filter B',
                        'fb:1' => 'Filter 1',
                        'fb:2' => 'Filter 2',
                        'fb:3' => 'Filter 3',
                    ]
                ]
            ],
            [
                'elementType' => Category::class,
                'source' => 'group:2',
                'filters' => [
                    [
                        '' => 'Filter A',
                        'fa:1' => 'Filter 1',
                        'fa:2' => 'Filter 2',
                        'fa:3' => 'Filter 3',
                    ]
                ]
            ],
            [
                'elementType' => Entry::class,
                'source' => '*',
                'filters' => [
                    [
                        '' => 'Global',
                        'fg:1' => 'Filter 1',
                        'fg:2' => 'Filter 2',
                        'fg:3' => 'Filter 3',
                    ]
                ]
            ],
            [
                'elementType' => Entry::class,
                'source' => 'section:7',
                'filters' => [
                    [
                        '' => 'Filter A',
                        'fa:1' => 'Filter 1',
                        'fa:2' => 'Filter 2',
                        'fa:3' => 'Filter 3',
                    ],
                    [
                        '' => 'Filter B',
                        'fb:1' => 'Filter 1',
                        'fb:2' => 'Filter 2',
                        'fb:3' => 'Filter 3',
                    ]
                ]
            ],
            [
                'elementType' => Entry::class,
                'source' => 'section:2',
                'filters' => [
                    [
                        '' => 'Filter A',
                        'fa:1' => 'Filter 1',
                        'fa:2' => 'Filter 2',
                        'fa:3' => 'Filter 3',
                    ]
                ]
            ]
        ];

        // foreach ($variable as $key => $value) {
        //     # code...
        // }
        // Craft::dd($settings);

        return $filters;
    }

    public function getElementFilterById($id)
    {
        if($this->_fetchedAllElementFilters || isset($this->_elementFiltersById[$id]))
        {
            return $this->_elementFiltersById[$id] ?? null;
        }

        $result = $this->_createElementFilterQuery()
            ->where(['id' => $id])
            ->one();

        if (!$result)
        {
            return null;
        }
        return $this->_elementFiltersById[$id] = new ElementFilter($result);
    }

    public function saveElementFilter(ElementFilter $model, bool $runValidation = true): bool
    {
        if ($model->id)
        {
            $record = ElementFilterRecord::findOne($model->id);
            if (!$record)
            {
                throw new Exception(Craft::t('searchit', 'No element filter exists with the ID “{id}”', ['id' => $model->id]));
            }
        }
        else
        {
            $record = new ElementFilterRecord();
        }

        if ($runValidation && !$model->validate())
        {
            Craft::info('Element filter not saved due to validation error.', __METHOD__);
            return false;
        }

        $record->elementType = $model->elementType;
        $record->source = $model->source;
        $record->name = $model->name;
        $record->type = $model->type;
        $record->settings = $model->settings;
        $record->sortOrder = $model->sortOrder;

        // Save it!
        $record->save(false);

        // Now that we have a record ID, save it on the model
        $model->id = $record->id;

        return true;
    }

    public function deleteElementFilterById($id): bool
    {
        $record = ElementFilterRecord::findOne($id);
        if ($record)
        {
            return (bool)$record->delete();
        }
        return false;
    }

    // Private Methods
    // =========================================================================

    private function _createElementFilterQuery(): Query
    {
        return (new Query())
            ->select([
                'id',
                'elementType',
                'source',
                'name',
                'type',
                'settings',
                'sortOrder',
            ])
            ->from(['{{%searchit_elementfilters}}']);
    }
}
