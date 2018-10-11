<?php
namespace fruitstudios\searchit\services;

use fruitstudios\searchit\Searchit;
use fruitstudios\searchit\models\ElementFilter;
use fruitstudios\searchit\models\SourceSettings;
use fruitstudios\searchit\records\ElementFilter as ElementFilterRecord;
use fruitstudios\searchit\helpers\ElementHelper;

use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\helpers\Json;

use craft\elements\Category;
use craft\elements\Entry;
use craft\elements\User;
use craft\elements\Asset;

use craft\commerce\elements\Product;

use craft\commerce\Plugin as CommercePlugin;

class ElementFilters extends Component
{
    // Const
    // =========================================================================

    const GLOBAL_SOURCE_HANDLE = 'global';
    const GLOBAL_SOURCE_KEY = '*';

    // Properties
    // =========================================================================

    private $_supportedElementTypes;
    private $_supportedSources = [];

    private $_fetchedAllElementFilters;
    private $_elementFilters = [];
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

        // Craft::dd(\craft\commerce\Plugin::getInstance());

        // $plugin = Craft::$app->getPlugins()->getPlugin('commerce');
        // if(Searchit::$plugin->isCommerceEnabled())
        // {
        //     $types[Product::class] = [
        //         'class' => Product::class,
        //         'handle' => 'products',
        //         'label' => Craft::t('searchit', 'Products'),
        //         'displayName' => Craft::t('searchit', 'Product'),
        //         'sources' => $this->getSupportedSources(Product::class),
        //     ];
        // }

        $this->_supportedElementTypes = $types;
        return $this->_supportedElementTypes;
    }

    public function getSupportedSources(string $elementType)
    {
        if(isset($this->_supportedSources[$elementType]))
        {
            return $this->_supportedSources[$elementType];
        }

        $sources[self::GLOBAL_SOURCE_HANDLE] = [
            'label' => Craft::t('searchit', 'Global'),
            'key' => '*',
            'handle' => self::GLOBAL_SOURCE_HANDLE,
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

        $this->_supportedSources[$elementType] = $sources;
        return $this->_supportedSources[$elementType];
    }

    public function getElementFiltersByType(string $elementType)
    {
        if(!$this->_fetchedAllElementFilters)
        {
            $this->getAllElementFilters();
        }

        return $this->_elementFiltersByType[$elementType] ?? false;
    }

    public function getElementFiltersBySource(string $elementType, string $sourceKey)
    {
        if(!$this->_fetchedAllElementFilters)
        {
            $this->getAllElementFilters();
        }

        return $this->_elementFiltersBySource[$elementType][$sourceKey] ?? false;
    }

    public function getAllElementFilters()
    {
        if($this->_fetchedAllElementFilters)
        {
            return $this->_elementFilters;
        }

        $results = $this->_createElementFilterQuery()
            ->all();

        if($results)
        {
            foreach($results as $result)
            {
                $elementFilter = $this->createElementFilter($result);

                $this->_elementFilters[$elementFilter->id] = $elementFilter;
                $this->_elementFiltersByType[$elementFilter->type][$elementFilter->id] = $elementFilter;
                $this->_elementFiltersBySource[$elementFilter->type][$elementFilter->source][$elementFilter->id] = $elementFilter;
            }
        }

        $this->_fetchedAllElementFilters = true;
        return $this->_elementFilters;
    }

    public function getActiveElementFiltersArray(string $type = null)
    {
        $filters = [];
        try{
            $this->getAllElementFilters();

            $supportedElementTypes = $this->getSupportedElementTypes();
            if($supportedElementTypes)
            {
                foreach ($supportedElementTypes as $supportedElementType)
                {
                    // Global Filters
                    $globalElementFilters = $this->_elementFiltersBySource[$supportedElementType['class']][self::GLOBAL_SOURCE_KEY] ?? false;
                    $globalFilters = $globalElementFilters ? $this->_elementFiltersAsArrayOfFilters($globalElementFilters) : [];

                    // Sources
                    $supportedSources = $supportedElementType['sources'] ?? [];
                    foreach ($supportedSources as $supportedSource)
                    {
                        $_sourceSettings = $this->getSourceSettings($supportedElementType['handle'], $supportedSource['handle']);
                        $_filters = [];
                        switch ($supportedSource['key'])
                        {
                            case self::GLOBAL_SOURCE_KEY:
                                if($globalFilters)
                                {
                                    $_filters = $globalFilters;
                                }
                                break;
                            default:
                                $elementFilters = $this->_elementFiltersBySource[$supportedElementType['class']][$supportedSource['key']] ?? [];
                                if (!$_sourceSettings->hideGlobalFilters)
                                {
                                    $_filters = $globalFilters;
                                }
                                $_filters = array_merge($_filters, $this->_elementFiltersAsArrayOfFilters($elementFilters));
                                break;
                        }

                        if(!empty($_filters))
                        {
                            $filters[] = [
                                'elementType' => $supportedElementType['class'],
                                'source' => $supportedSource['key'],
                                'filters' => $_filters
                            ];
                        }
                    }
                }
            }
        } catch(\Exception $e) {
            Craft::error('An error occurred when generating searchit filters: ' . $e->getMessage(), __METHOD__);
            return $filters;
        }



        return $filters;
    }

    public function getElementFilterById($id)
    {
        if($this->_fetchedAllElementFilters || isset($this->_elementFilters[$id]))
        {
            return $this->_elementFilters[$id] ?? null;
        }

        $result = $this->_createElementFilterQuery()
            ->where(['id' => $id])
            ->one();

        if (!$result)
        {
            return null;
        }
        return $this->_elementFilters[$id] = $this->createElementFilter($result);
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

        $record->type = $model->type;
        $record->source = $model->source;
        $record->name = $model->name;
        $record->filterType = $model->filterType;
        $record->settings = $model->settings;

        $maxSortOrder = (new Query())
            ->from(['{{%searchit_elementfilters}}'])
            ->where([
                'type' => $model->type,
                'source' => $model->source,
            ])
            ->max('[[sortOrder]]');

        $record->sortOrder = $maxSortOrder ? $maxSortOrder + 1 : 1;

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

    public function reorderElementFilters(array $elementFilterIds): bool
    {
        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            foreach ($elementFilterIds as $sortOrder => $elementFilterId)
            {
                $elementFilterRecord = ElementFilterRecord::findOne($elementFilterId);
                $elementFilterRecord->sortOrder = $sortOrder + 1;
                $elementFilterRecord->save();
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

    public function createElementFilter(array $config = []): ElementFilter
    {
        switch ($config['filterType'] ?? false)
        {
            case 'manual':
                $config['settings'] = is_string($config['settings']) ? Json::decodeIfJson($config['settings'], true) : ($config['settings'] ?? []);
                break;
        }
        return new ElementFilter($config);
    }

    public function saveSourceSettings(SourceSettings $sourceSettings, bool $runValidation = true): bool
    {
        if ($runValidation && !$sourceSettings->validate())
        {
            Craft::info('Element filter not saved due to validation error.', __METHOD__);
            return false;
        }

        $settings = Searchit::$settings;

        $sources = $settings->sources;
        $sources[$sourceSettings->type][$sourceSettings->source] = $sourceSettings->getAttributes();

        return Craft::$app->getPlugins()->savePluginSettings(Searchit::$plugin, [
            'sources' => $sources
        ]);
    }

    public function getSourceSettings(string $elementTypeHandle, string $sourceHandle): SourceSettings
    {
        return new SourceSettings(Searchit::$settings->sources[$elementTypeHandle][$sourceHandle] ?? []);
    }

    public function createSourceSettings(array $config = []): SourceSettings
    {
        return new SourceSettings($config);
    }

    // Private Methods
    // =========================================================================
    private function _elementFiltersAsArrayOfFilters(array $elementFilters = [])
    {
        $filters = [];
        foreach ($elementFilters as $elementFilter)
        {
            $filters[] = $elementFilter->getOptions();
        }
        return $filters;
    }
    private function _createElementFilterQuery(): Query
    {
        return (new Query())
            ->select([
                'id',
                'type',
                'source',
                'name',
                'filterType',
                'settings',
                'sortOrder',
            ])
            ->from(['{{%searchit_elementfilters}}'])
            ->orderBy('sortOrder');
    }
}
