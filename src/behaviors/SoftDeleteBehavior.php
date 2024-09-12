<?php

namespace yiithings\softdelete\behaviors;

use yii\behaviors\TimestampBehavior;

/**
 * Class SoftDeleteBehavior
 *
 * ```php
 * use yiithings\softdelete\behaviors\SoftDeleteBehavior;
 *
 * public function behaviors()
 * {
 *     return [
 *         SoftDeleteBehavior::className(),
 *     ];
 * }
 * ```
 *
 * @package yiithings\softdelete\behaviors
 * @property $owner
 */
class SoftDeleteBehavior extends TimestampBehavior
{
    const EVENT_BEFORE_SOFT_DELETE = 'beforeSoftDelete';
    const EVENT_AFTER_SOFT_DELETE = 'afterSoftDelete';
    const EVENT_BEFORE_FORCE_DELETE = 'beforeForceDelete';
    const EVENT_AFTER_FORCE_DELETE = 'afterForceDelete';
    const EVENT_BEFORE_RESTORE = 'beforeRestore';
    const EVENT_AFTER_RESTORE = 'afterRestore';

    public $deletedAtAttribute = 'deleted_at';

    public $withTimestamp = false;

    public function init()
    {
        if ($this->withTimestamp) {
            parent::init();
        }

        $this->attributes = array_merge($this->attributes, [
            static::EVENT_BEFORE_SOFT_DELETE => $this->deletedAtAttribute,
        ]);
    }
}
