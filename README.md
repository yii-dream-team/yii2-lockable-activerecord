# yii2-lockable-active-record
Pessimistic locking behavior for Yii2 ActiveRecord

## Installation ##

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

    php composer.phar require --prefer-dist yii-dream-team/yii2-jstree "*"

or add

    "yii-dream-team/yii2-jstree": "*"

to the `require` section of your composer.json.

## Usage ##
Attach the behavior to your controller class.

    public function behaviors()
    {
        return [
            '\yiidreamteam\behaviors\LockableActiveRecord',
        ];
    }
    
Add @mixin phpdoc to you class definition.

    /**
     * Class Sample
     * @package common\models
     *
     * @mixin \yiidreamteam\behaviors\LockableActiveRecord
     */
    class Wallet extends ActiveRecord { ... }
    
Use model locks in transaction.

    $dbTransaction = $model->getDb()->beginTransaction(\yii\db\Transaction::SERIALIZABLE);
    try {
        $model->lockForUpdate();
        $model->doSomeThingWhileLocked();
        $dbTransaction->commit();
    } catch(\Exception $e) {
        $dbTransaction->rollBack();
        throw $e;
    }
    
## Licence ##

MIT
    
## Links ##

* http://yiidreamteam.com/yii2/lockable-active-record
* https://dev.mysql.com/doc/refman/5.6/en/innodb-locking-reads.html
