<?php
/**
 * OcAffiliateTransaction
 *
 * --- BEGIN ModelDoc ---
 *
 * Table {{affiliate_transaction}}
 * @property integer $affiliate_transaction_id
 * @property integer $affiliate_id
 * @property integer $order_id
 * @property string $description
 * @property number $amount
 * @property string $date_added
 *
 * Relations
 * @property \OcAffiliate $affiliate
 * @property \OcOrder $order
 *
 * @see \CActiveRecord
 * @method \OcAffiliateTransaction find($condition = '', array $params = array())
 * @method \OcAffiliateTransaction findByPk($pk, $condition = '', array $params = array())
 * @method \OcAffiliateTransaction findByAttributes(array $attributes, $condition = '', array $params = array())
 * @method \OcAffiliateTransaction findBySql($sql, array $params = array())
 * @method \OcAffiliateTransaction[] findAll($condition = '', array $params = array())
 * @method \OcAffiliateTransaction[] findAllByPk($pk, $condition = '', array $params = array())
 * @method \OcAffiliateTransaction[] findAllByAttributes(array $attributes, $condition = '', array $params = array())
 * @method \OcAffiliateTransaction[] findAllBySql($sql, array $params = array())
 * @method \OcAffiliateTransaction with()
 * @method \OcAffiliateTransaction together()
 * @method \OcAffiliateTransaction cache($duration, $dependency = null, $queryCount = 1)
 * @method \OcAffiliateTransaction resetScope($resetDefault = true)
 * @method \OcAffiliateTransaction populateRecord($attributes, $callAfterFind = true)
 * @method \OcAffiliateTransaction[] populateRecords($data, $callAfterFind = true, $index = null)
 *
 * --- END ModelDoc ---
 */

class OcAffiliateTransaction extends CActiveRecord
{

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return OcAffiliateTransaction the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{affiliate_transaction}}';
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'affiliate' => array(self::BELONGS_TO, 'OcAffiliate', 'affiliate_id'),
            'order' => array(self::BELONGS_TO, 'OcOrder', 'order_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'affiliate_transaction_id' => Yii::t('app', 'Affiliate Transaction'),
            'affiliate_id' => Yii::t('app', 'Affiliate'),
            'order_id' => Yii::t('app', 'Order'),
            'description' => Yii::t('app', 'Description'),
            'amount' => Yii::t('app', 'Amount'),
            'date_added' => Yii::t('app', 'Date Added'),
        );
    }

}

