<?php
namespace fruitstudios\searchit\records;

use craft\db\ActiveRecord;
use yii\db\ActiveQueryInterface;

class ElementFilter extends ActiveRecord
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['elementType', 'source', 'name', 'type'], 'string'],
            [['elementType', 'source', 'name', 'type'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%searchit_elementfilters}}';
    }

}
