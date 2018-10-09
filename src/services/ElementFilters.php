<?php
namespace fruitstudios\searchit\services;

use fruitstudios\searchit\Searchit;
use fruitstudios\searchit\models\ElementFilter;
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
                            $_filters = array_merge($globalFilters, $this->_elementFiltersAsArrayOfFilters($elementFilters));
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
        return $this->_elementFilters[$id] = new ElementFilter($result);
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
        $record->manual = $model->manual;
        $record->dynamic = $model->dynamic;
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

    public function createElementFilter(array $config = []): ElementFilter
    {
        return new ElementFilter($config);
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
                'manual',
                'dynamic',
                'sortOrder',
            ])
            ->from(['{{%searchit_elementfilters}}']);
    }
}