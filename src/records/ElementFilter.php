<?php
namespace presseddigital\searchit\records;

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
            [['type', 'source', 'name', 'filterType'], 'string'],
            [['type', 'source', 'name', 'filterType'], 'required'],
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
