<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tpconfig".
 *
 * @property integer $id
 * @property string $tp_name
 * @property integer $order_type
 * @property string $token
 * @property string $detail
 */
class Tpconfig extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tpconfig';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tp_name', 'token', 'detail'], 'required'],
            [['order_type'], 'integer'],
            [['tp_name'], 'string', 'max' => 64],
            [['token'], 'string', 'max' => 256],
            [['detail'], 'string', 'max' => 1024],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tp_name' => 'TP名称',
            'order_type' => 'TPID',
            'token' => 'TOKEN',
            'detail' => 'token详情',
        ];
    }
}
