<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Env]].
 *
 * @see Env
 */
class EnvQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return Env[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Env|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
