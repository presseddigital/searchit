<?php
namespace fruitstudios\searchit\services;

use fruitstudios\searchit\Searchit;

use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\records\CategoryGroup as CategoryGroupRecord;
use craft\records\UserGroup as UserGroupRecord;
use craft\records\Volume as VolumeRecord;
use craft\records\Section as SectionRecord;

use craft\elements\Category;
use craft\elements\Entry;


use craft\commerce\records\ProductType as ProductTypeRecord;

class SearchFilters extends Component
{
    // Properties
    // =========================================================================

    private $_optionsByType;

    // Public Methods
    // =========================================================================

    public function getActiveSearchFiltersArray(string $type = null)
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
                if (Searchit::$isCommerceInstalled)
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

    // private function _createSearchFilterQuery(): Query
    // {
    //     return (new Query())
    //         ->select([
    //             'id',
    //             'name',
    //             'type',
    //             'settings',
    //         ])
    //         ->from(['{{%searchit_fieldtemplates}}']);
    // }
}
