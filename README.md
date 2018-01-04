Yii2 SoftDelete
===============
[![Build Status](https://travis-ci.org/yiithings/yii2-softdelete.svg)](https://travis-ci.org/yiithings/yii2-softdelete)
[![Latest Stable Version](https://poser.pugx.org/yiithings/yii2-softdelete/v/stable.svg)](https://packagist.org/packages/yiithings/yii2-softdelete) 
[![Total Downloads](https://poser.pugx.org/yiithings/yii2-softdelete/downloads.svg)](https://packagist.org/packages/yiithings/yii2-softdelete) 
[![Latest Unstable Version](https://poser.pugx.org/yiithings/yii2-softdelete/v/unstable.svg)](https://packagist.org/packages/yiithings/yii2-softdelete)
[![License](https://poser.pugx.org/yiithings/yii2-softdelete/license.svg)](https://packagist.org/packages/yiithings/yii2-softdelete)

Soft delete extension for Yii2 framework.

This extension ensures that soft-deleted has delete native consistent performance and is IDE-friendly. 

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yiithings/yii2-softdelete "*"
```

or add

```
"yiithings/yii2-softdelete": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

Edit model class:
```php
use yiithings\softdelete\behaviors\SoftDeleteBehavior;
use yiithings\softdelete\SoftDelete;

class Model extends \yii\db\ActiveRecord
{
    use SoftDelete;

    public function behaviors()
    {
        return [
            'class' => SoftDeleteBehavior::className(),
        ];
    }
}
```

Change database table structures, add `deleted_at (int 11)` field and attached to UNIQUE index. 

API
---

### ActiveRecord class (SoftDelete Trait):

find系列方法会返回 `yiithings\softdelete\ActiveQuery` 对象。

+ softDelete() 使用软删除模式删除数据
+ forceDelete() 使用物理删除模式强制删除数据
+ restore() 恢复被软删除的模型数据
+ isTrashed() 是否被软删除

以下命令分别是 `find()` / `findOne()` / `findAll()` 在不同模式下的对应版本：

所有模型（包括被软删除的）：

+ findWithTrashed()
+ findOneWithTrashed($condition)
+ findAllWithTrashed($condition)

只查找被软删除的模型：

+ findOnlyTrashed()
+ findOneOnlyTrashed($condition)
+ findAllOnlyTrashed($condition)

以下的命令均被重写成软删除版本：

+ find() 
+ findOne()
+ findAll()
+ delete()

### yiithings\softdelete\ActiveQuery

增加了 `withTrashed()`, `withoutTrashed()` 和 `onlyTrashed()` 三个方法，
设置相应的查找模式。