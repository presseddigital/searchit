<?php
namespace fruitstudios\searchit\services;

use fruitstudios\searchit\Searchit;
use fruitstudios\searchit\helpers\ElementHelper;

use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\records\CategoryGroup as CategoryGroupRecord;
use craft\records\UserGroup as UserGroupRecord;
use craft\records\Volume as VolumeRecord;
use craft\records\Section as SectionRecord;

use craft\elements\Category;
use craft\elements\Entry;
use craft\elements\User;
use craft\elements\Asset;

use craft\commerce\elements\Product;

use craft\commerce\records\ProductType as ProductTypeRecord;

class ElementFilters extends Component
{
    // Properties
    // =========================================================================

    private $_optionsByType;

    private $_supportedElementTypes;
    private $_supportedSourcesByElementType = [];

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
            'key' => 'global',
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

    public function getElementFiltersByType(string $elementType, string $source)
    {
        return $this->_createElementFilterQuery()
            ->where([
                'elementType' => $elementType,
                'source' => $source
            ])
            ->all();
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

    public function getElementNameByType(string $type)
    {
        $name = '';
        switch ($type)
        {
            case 'entries':
                $name = Craft::t('app', 'entry');
                break;

            case 'categories':
                $name = Craft::t('app', 'category');
                break;

            case 'users':
                $name = Craft::t('app', 'user');
                break;

            case 'assets':
                $name = Craft::t('app', 'asset');
                break;

            case 'products':
                $name = Craft::t('app', 'product');
                break;
        }
        return $name;
    }


    public function getOptionsByType(string $type)
    {
        $elementName = $this->getElementNameByType($type);

        // TODO: Let's use elementType properly, pass element around to determine type etc etc
        //     : Craft::$app->getElementIndexes()->getSources(Entry::class);
        //     : Craft::dd(Entry::displayName());

        $this->_optionsByType[$type][] = [
            'id' => 'global',
            'key' => 'global',
            'label' => Craft::t('searchit', 'Global Filters'),
            'instructions' => Craft::t('searchit', 'Filters to include on every {element} search.', [
                'element' => ucfirst($elementName),
            ]),
        ];

        switch ($type)
        {
            case 'entries':
                $sectionRecords = SectionRecord::find()
                    ->select([
                        'id',
                        'name'
                    ])
                    ->where([
                        'or',
                        ['type' => 'structure'],
                        ['type' => 'channel'],
                    ])
                    ->orderBy(['name' => SORT_ASC])
                    ->all();

                if($sectionRecords)
                {
                    foreach($sectionRecords as $sectionRecord)
                    {
                        $this->_optionsByType[$type][] = [
                            'id' => 'section'.$sectionRecord['id'],
                            'key' => 'section:'.$sectionRecord['id'],
                            'label' => Craft::t('searchit', '{name} Filters', [
                                'name' => $sectionRecord['name']
                            ]),
                            'instructions' => Craft::t('searchit', 'Filters to include on the {name} Section search.', [
                                'name' => $sectionRecord['name']
                            ]),
                        ];
                    }
                }
                break;

            case 'categories':
                $categoryGroupRecords = CategoryGroupRecord::find()
                    ->select([
                        'id',
                        'name'
                    ])
                    ->orderBy(['name' => SORT_ASC])
                    ->all();

                if($categoryGroupRecords)
                {
                    foreach($categoryGroupRecords as $categoryGroupRecord)
                    {
                        $this->_optionsByType[$type][] = [
                            'id' => 'categorygroup'.$categoryGroupRecord['id'],
                            'key' => 'group:'.$categoryGroupRecord['id'],
                            'label' => Craft::t('searchit', '{name} Filters', [
                                'name' => $categoryGroupRecord['name']
                            ]),
                            'instructions' => Craft::t('searchit', 'Filters to include on the {name} Category Group search.', [
                                'name' => $categoryGroupRecord['name']
                            ]),
                        ];
                    }
                }
                break;


            case 'users':
                $userGroupRecords = UserGroupRecord::find()
                    ->select([
                        'id',
                        'name'
                    ])
                    ->orderBy(['name' => SORT_ASC])
                    ->all();

                if($userGroupRecords)
                {
                    foreach($userGroupRecords as $userGroupRecord)
                    {
                        $this->_optionsByType[$type][] = [
                            'id' => 'usergroup'.$userGroupRecord['id'],
                            'key' => 'group'.$userGroupRecord['id'],
                            'label' => Craft::t('searchit', '{name} Filters', [
                                'name' => $userGroupRecord['name']
                            ]),
                            'instructions' => Craft::t('searchit', 'Filters to include on the {name} User Group search.', [
                                'name' => $userGroupRecord['name']
                            ]),
                        ];
                    }
                }
                break;

            case 'assets':
                $volumeRecords = VolumeRecord::find()
                    ->select([
                        'id',
                        'name'
                    ])
                    ->orderBy(['name' => SORT_ASC])
                    ->all();

                if($volumeRecords)
                {
                    foreach($volumeRecords as $volumeRecord)
                    {
                        $this->_optionsByType[$type][] = [
                            'id' => 'volume'.$volumeRecord['id'],
                            'key' => 'folder'.$volumeRecord['id'],
                            'label' => Craft::t('searchit', '{name} Filters', [
                                'name' => $volumeRecord['name']
                            ]),
                            'instructions' => Craft::t('searchit', 'Filters to include on the {name} Volume search.', [
                                'name' => $volumeRecord['name']
                            ]),
                        ];
                    }
                }
                break;


            case 'products':
                if (Searchit::$commerceInstalled)
                {
                    $productTypes = ProductTypeRecord::find()
                        ->select([
                            'id',
                            'name'
                        ])
                        ->orderBy(['name' => SORT_ASC])
                        ->all();

                    if($productTypes)
                    {
                        foreach($productTypes as $productType)
                        {
                            $this->_optionsByType[$type][] = [
                                'id' => 'producttype'.$productType['id'],
                                'key' => 'productType:'.$productType['id'],
                                'label' => Craft::t('searchit', '{name} Filters', [
                                    'name' => $productType['name']
                                ]),
                                'instructions' => Craft::t('searchit', 'Filters to include on the {name} Product Types search.', [
                                    'name' => $productType['name']
                                ]),
                            ];
                        }
                    }
                }

                break;
        }
        return $this->_optionsByType[$type] ?? [];
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
