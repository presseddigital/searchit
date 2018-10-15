<?php
namespace fruitstudios\searchit\services;

use Craft;
use craft\base\Component;
use craft\db\Query;

class Fields extends Component
{
    private $_fieldsById;
    private $_fieldsByType;
    private $_mapFields;
    private $_mapFieldsToMatrix;

    // Public Methods
    // =========================================================================

    public function getFieldById($id)
    {
        if(isset($this->_fieldsById[$id]))
        {
            return $this->_fieldsById[$id];
        }

        if(!$field = Craft::$app->getFields()->getFieldById())
        {
            return false;
        }

        $this->_fieldsById[$field->id] = $field;
        return $this->_fieldsById[$field->id];
    }

    public function getFieldIdByHandle(string $handle)
    {
        $field = $this->getFieldByHandle();
        return $field ? $field->id : false;
    }

    public function getFieldByHandle(string $handle)
    {
        $this->_buildFieldMaps();
        return $this->_mapFields[$handle] ?? false;
    }

    public function getFieldsByType(string $type)
    {
        if(isset($this->_fieldsByType[$type]))
        {
            return $this->_fieldsByType[$type];
        }

        $fields = $this->_createFieldQuery()
            ->where(['type' => $type])
            ->all();

        if(!$fields)
        {
            return false;
        }

        foreach ($fields as $field)
        {
            $field = Craft::$app->getFields()->createField($field);
            $this->_fieldsById[$field->id] = $field;
            $this->_fieldsByType[get_class($field)][$field->id] = $field;
        }

        return $this->_fieldsByType[$type];
    }


    public function getMatrixFieldByChildFieldId($id)
    {
        $this->_buildFieldMaps();
        return $this->_mapFieldsToMatrix[$id] ?? false;
    }

    public function getMatrixFieldIdByChildFieldId($id)
    {
        $this->_buildFieldMaps();
        if(!$this->_mapFieldsToMatrix[$id] ?? false)
        {
            return false;
        }
        return $this->getFieldById($this->_mapFieldsToMatrix[$id]);
    }


    public function getFieldsMap()
    {
        $this->_buildFieldMaps();
        return $this->_mapFields;
    }

    public function getMatrixFieldsMap()
    {
        $this->_buildFieldMaps();
        return $this->_matrixFieldsMap;
    }

    // Private Methods
    // =========================================================================

    private function _buildFieldMaps()
    {
        if (is_null($this->_mapFields))
        {
            // Get all fields of any context and store a map to them
            $allFields = $this->_createFieldQuery()->all();
            if(!$allFields)
            {
                $this->_mapFields = [];
                return;
            }

            foreach ($allFields as $field)
            {
                $field = Craft::$app->getFields()->createField($field);
                $this->_fieldsById[$field->id] = $field;
                $this->_fieldsByType[get_class($field)][$field->id] = $field;
            }

            // Get any matrix blocks and store a refenrece to them by block handle
            $matrixFieldInfoByContext = [];
            $matrixBlockTypes = $this->_createMatrixBlockTypeQuery()->all();
            if($matrixBlockTypes)
            {
                foreach ($matrixBlockTypes as $matrixBlockType)
                {
                    $matrixFieldInfoByContext['matrixBlockType:'.$matrixBlockType['id']] = [
                        'field' => $this->getFieldById($matrixBlockType['fieldId']),
                        'handle' => $matrixBlockType['fieldHandle'].':'.$matrixBlockType['handle'].':'
                    ];
                }
            }

            // Build and store the field maps
            foreach ($this->_fieldsById as $field)
            {
                $ownedByMatrix = $matrixFieldInfoByContext[$field['context']] ?? false;
                if($ownedByMatrix)
                {
                    $this->_mapFields[$ownedByMatrix['handle'].$field['handle']] = $field;
                    $this->_mapFieldsToMatrix[$field['id']] = $ownedByMatrix['field'];
                }
                else
                {
                    $this->_mapFields[$field['handle']] = $field;
                }
            }
        }
    }

    private function _createFieldQuery(): Query
    {
        return (new Query())
            ->select([
                'fields.id',
                'fields.dateCreated',
                'fields.dateUpdated',
                'fields.groupId',
                'fields.name',
                'fields.handle',
                'fields.context',
                'fields.instructions',
                'fields.translationMethod',
                'fields.translationKeyFormat',
                'fields.type',
                'fields.settings'
            ])
            ->from(['{{%fields}} fields'])
            ->orderBy(['fields.name' => SORT_ASC, 'fields.handle' => SORT_ASC]);
    }

    private function _createMatrixBlockTypeQuery(): Query
    {
        return (new Query())
            ->select([
                'matrixblocktypes.id',
                'matrixblocktypes.handle',
                'matrixblocktypes.fieldId',
                'fields.handle as fieldHandle'
            ])
            ->from(['{{%matrixblocktypes}} matrixblocktypes'])
            ->orderBy('matrixblocktypes.id')
            ->innerJoin('{{%fields}} fields', '[[fields.id]] = [[matrixblocktypes.fieldId]]');
    }
}
