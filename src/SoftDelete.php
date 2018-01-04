<?php

namespace yiithings\softdelete;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\ModelEvent;
use yii\db\ActiveQueryInterface;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;
use yiithings\softdelete\behaviors\SoftDeleteBehavior;

trait SoftDelete
{
    /**
     * @var string ActiveRecord deleted_at attribute name.
     */
    protected static $deletedAtAttribute = 'deleted_at';
    /**
     * @var bool
     */
    private $forceDelete = false;

    /**
     * @return object|ActiveQuery|SoftDelete
     * @throws \yii\base\InvalidConfigException
     */
    public static function find()
    {
        return Yii::createObject(ActiveQuery::className(), [get_called_class()]);
    }

    /**
     * @return object|ActiveQuery|SoftDelete
     * @throws \yii\base\InvalidConfigException
     */
    public static function findWithTrashed()
    {
        /** @var ActiveQuery $query */
        $query =  Yii::createObject(ActiveQuery::className(), [get_called_class()]);

        return $query->withTrashed();
    }

    /**
     * @return object|ActiveQuery|SoftDelete
     * @throws \yii\base\InvalidConfigException
     */
    public static function findOnlyTrashed()
    {
        /** @var ActiveQuery $query */
        $query =  Yii::createObject(ActiveQuery::className(), [get_called_class()]);

        return $query->onlyTrashed();
    }

    /**
     * @param mixed $condition
     * @return object|ActiveQuery|SoftDelete
     * @throws InvalidConfigException
     */
    public static function findOneWithTrashed($condition)
    {
        return static::findByCondition($condition, static::findWithTrashed())->one();
    }

    /**
     * @param mixed $condition
     * @return object[]|ActiveQuery[]|SoftDelete[]
     * @throws InvalidConfigException
     */
    public static function findAllWithTrashed($condition)
    {
        return static::findByCondition($condition, static::findWithTrashed())->all();
    }

    /**
     * @param mixed $condition
     * @return object|ActiveQuery|SoftDelete
     * @throws InvalidConfigException
     */
    public static function findOneOnlyTrashed($condition)
    {
        return static::findByCondition($condition, static::findOnlyTrashed())->one();
    }

    /**
     * @param mixed $condition
     * @return object[]|ActiveQuery[]|SoftDelete[]
     * @throws InvalidConfigException
     */
    public static function findAllOnlyTrashed($condition)
    {
        return static::findByCondition($condition, static::findOnlyTrashed())->all();
    }

    public function softDelete()
    {
        return $this->delete();
    }

    public function forceDelete()
    {
        $this->forceDelete = true;
        $result = $this->delete();
        $this->forceDelete = false;

        return $result;
    }

    public function restore()
    {
        if ( ! $this->beforeRestore()) {
            return false;
        }
        if (empty($this->getOldAttribute(static::$deletedAtAttribute))) {
            $this->afterRestore();
            return true;
        }

        $this->{static::$deletedAtAttribute} = null;
        $result = $this->save();
        $this->afterRestore();

        return $result;
    }

    public function isTrashed()
    {
        return ! empty($this->getOldAttribute(static::$deletedAtAttribute));
    }

    public function beforeSoftDelete()
    {
        $event = new ModelEvent();
        $this->trigger(SoftDeleteBehavior::EVENT_BEFORE_SOFT_DELETE, $event);

        return $event->isValid;
    }

    public function afterSoftDelete()
    {
        $event = new ModelEvent();
        $this->trigger(SoftDeleteBehavior::EVENT_AFTER_SOFT_DELETE, $event);

        return $event->isValid;
    }

    public function beforeForceDelete()
    {
        $event = new ModelEvent();
        $this->trigger(SoftDeleteBehavior::EVENT_BEFORE_FORCE_DELETE, $event);

        return $event->isValid;
    }

    public function afterForceDelete()
    {
        $event = new ModelEvent();
        $this->trigger(SoftDeleteBehavior::EVENT_AFTER_FORCE_DELETE, $event);

        return $event->isValid;
    }

    public function beforeRestore()
    {
        $event = new ModelEvent();
        $this->trigger(SoftDeleteBehavior::EVENT_BEFORE_RESTORE, $event);

        return $event->isValid;
    }

    public function afterRestore()
    {
        $event = new ModelEvent();
        $this->trigger(SoftDeleteBehavior::EVENT_AFTER_RESTORE, $event);

        return $event->isValid;
    }

    /**
     * Finds ActiveRecord instance(s) by the given condition.
     * This method is internally called by [[findOne()]] and [[findAll()]].
     *
     * @param mixed                $condition please refer to [[findOne()]] for the explanation of this parameter
     * @param ActiveQueryInterface $query
     * @return ActiveQueryInterface the newly created [[ActiveQueryInterface|ActiveQuery]] instance.
     * @throws InvalidConfigException if there is no primary key defined
     * @internal
     */
    protected static function findByCondition($condition, $query = null)
    {
        if ($query === null) {
            $query = static::find();
        }

        if ( ! ArrayHelper::isAssociative($condition)) {
            // query by primary key
            $primaryKey = static::primaryKey();
            if (isset($primaryKey[0])) {
                $condition = [$primaryKey[0] => $condition];
            } else {
                throw new InvalidConfigException('"' . get_called_class() . '" must have a primary key.');
            }
        }

        return $query->andWhere($condition);
    }

    protected function deleteInternal()
    {
        if ( ! $this->beforeDelete()) {
            return false;
        }

        if ($this->forceDelete) {
            $this->beforeForceDelete();
            // we do not check the return value of deleteAll() because it's possible
            // the record is already deleted in the database and thus the method will return 0
            $condition = $this->getOldPrimaryKey(true);
            $lock = $this->optimisticLock();
            if ($lock !== null) {
                $condition[$lock] = $this->$lock;
            }
            $result = static::deleteAll($condition);
            if ($lock !== null && ! $result) {
                throw new StaleObjectException('The object being deleted is outdated.');
            }
            $this->setOldAttributes(null);
            $this->afterForceDelete();
        } else {
            $result = $this->softDeleteInternal();
        }
        $this->afterDelete();

        return $result;
    }

    protected function softDeleteInternal()
    {
        if ( ! $this->beforeSoftDelete()) {
            return false;
        }
        $values = $this->getAttributes([static::$deletedAtAttribute]);
        if ($this->isTrashed()) {
            $this->afterSoftDelete();

            return true;
        }
        $condition = $this->getOldPrimaryKey(true);
        $lock = $this->optimisticLock();
        if ($lock !== null) {
            $values[$lock] = $this->$lock + 1;
            $condition[$lock] = $this->$lock;
        }
        // We do not check the return value of updateAll() because it's possible
        // that the UPDATE statement doesn't change anything and thus returns 0.
        $result = static::updateAll($values, $condition);

        if ($lock !== null && ! $result) {
            throw new StaleObjectException('The object being updated is outdated.');
        }

        if (isset($values[$lock])) {
            $this->$lock = $values[$lock];
        }

        $this->afterSoftDelete();

        return $result;
    }
}