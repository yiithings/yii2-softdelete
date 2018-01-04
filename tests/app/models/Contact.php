<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yiithings\softdelete\behaviors\SoftDeleteBehavior;
use yiithings\softdelete\SoftDelete;

/**
 * This is the model class for table "contact".
 *
 * @property int $id
 * @property string $name
 * @property string $mobile
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 */
class Contact extends \yii\db\ActiveRecord
{
    use SoftDelete;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'contact';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
            ],
            [
                'class' => SoftDeleteBehavior::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'deleted_at'], 'integer'],
            [['name'], 'string', 'max' => 64],
            [['mobile'], 'string', 'max' => 20],
            [['name', 'deleted_at'], 'unique', 'targetAttribute' => ['name', 'deleted_at']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'mobile' => 'Mobile',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }
}
