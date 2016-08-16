<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "env".
 *
 * @property integer $id
 * @property string $env_name
 * @property string $branch_name
 * @property string $hostname
 * @property string $path
 * @property integer $port
 * @property string $agent_url
 * @property string $discription
 */
class Env extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'env';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['port'], 'integer'],
            [['env_name', 'branch_name', 'hostname', 'discription'], 'string', 'max' => 100],
            [['path', 'agent_url'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'env_name' => '环境名称',
            'branch_name' => '分支名称',
            'hostname' => '主机名称',
            'path' => '部署路径',
            'port' => '服务端口',
            'agent_url' => '沙盒agent地址',
            'discription' => '描述',
            'status' => '分支状态',
        ];
    }

    /**
     * @inheritdoc
     * @return EnvQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new EnvQuery(get_called_class());
    }
}
