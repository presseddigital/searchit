<?php
namespace fruitstudios\colorit\records;

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
            [['name', 'type'], 'required'],
            [['type'], 'string'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%searchit_element_filters}}';
    }

}
