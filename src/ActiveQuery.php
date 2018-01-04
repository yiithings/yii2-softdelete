<?php

namespace yiithings\softdelete;

use yii\db\QueryBuilder;

class ActiveQuery extends \yii\db\ActiveQuery
{
    const WITH_TRASHED = 0;
    const WITHOUT_TRASHED = 1;
    const ONLY_TRASHED = 2;

    /**
     * @var string
     */
    public $deletedAtAttribute = 'deleted_at';
    /**
     * @var int
     */
    private $_trashed;

    /**
     * @param QueryBuilder $builder
     * @return $this|\yii\db\Query
     */
    public function prepare($builder)
    {
        $query = parent::prepare($builder);
        switch ($this->getTrashed()) {
            case static::WITHOUT_TRASHED:
                $query->andWhere(['is', $this->deletedAtAttribute, null]);
                break;
            case static::ONLY_TRASHED:
                $query->andWhere(['!=', $this->deletedAtAttribute, '']);
                break;
            case static::WITH_TRASHED: // No break;
            default:
                break;
        }

        return $query;
    }

    public function withTrashed()
    {
        $this->_trashed = static::WITH_TRASHED;

        return $this;
    }

    public function withoutTrashed()
    {
        $this->_trashed = static::WITHOUT_TRASHED;

        return $this;
    }

    public function onlyTrashed()
    {
        $this->_trashed = static::ONLY_TRASHED;

        return $this;
    }

    public function getTrashed()
    {
        if ($this->_trashed === null) {
            $this->_trashed = static::WITHOUT_TRASHED;
        }

        return $this->_trashed;
    }
}