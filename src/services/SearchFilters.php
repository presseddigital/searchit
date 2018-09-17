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

use craft\commerce\records\ProductType as ProductTypeRecord;

class SearchFilters extends Component
{
    // Properties
    // =========================================================================

    private $_optionsByType;

    // Public Methods
    // =========================================================================

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

        $this->_optionsByType[$type][] = [
            'handle' => 'global',
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
                        'type' => 'structure',
                    ])
                    ->orderBy(['name' => SORT_ASC])
                    ->all();

                if($sectionRecords)
                {
                    foreach($sectionRecords as $sectionRecord)
                    {
                        $this->_optionsByType[$type][] = [
                            'id' => $sectionRecord['id'],
                            'handle' => 'section'.$sectionRecord['id'],
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
                            'id' => $categoryGroupRecord['id'],
                            'handle' => 'categorygroup'.$categoryGroupRecord['id'],
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
                            'id' => $userGroupRecord['id'],
                            'handle' => 'usergroup'.$userGroupRecord['id'],
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
                            'id' => $volumeRecord['id'],
                            'handle' => 'volume'.$volumeRecord['id'],
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
                                'id' => $productType['id'],
                                'handle' => 'producttype'.$productType['id'],
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
