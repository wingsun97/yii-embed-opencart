<?php
/**
 * OcTaxClass
 *
 * --- BEGIN ModelDoc ---
 *
 * Table {{tax_class}}
 * @property integer $tax_class_id
 * @property string $title
 * @property string $description
 * @property string $date_added
 * @property string $date_modified
 *
 * Relations
 * @property \OcProduct[] $products
 * @property \OcTaxRule[] $taxRules
 *
 * @see \CActiveRecord
 * @method \OcTaxClass find($condition = '', array $params = array())
 * @method \OcTaxClass findByPk($pk, $condition = '', array $params = array())
 * @method \OcTaxClass findByAttributes(array $attributes, $condition = '', array $params = array())
 * @method \OcTaxClass findBySql($sql, array $params = array())
 * @method \OcTaxClass[] findAll($condition = '', array $params = array())
 * @method \OcTaxClass[] findAllByPk($pk, $condition = '', array $params = array())
 * @method \OcTaxClass[] findAllByAttributes(array $attributes, $condition = '', array $params = array())
 * @method \OcTaxClass[] findAllBySql($sql, array $params = array())
 * @method \OcTaxClass with()
 * @method \OcTaxClass together()
 * @method \OcTaxClass cache($duration, $dependency = null, $queryCount = 1)
 * @method \OcTaxClass resetScope($resetDefault = true)
 * @method \OcTaxClass populateRecord($attributes, $callAfterFind = true)
 * @method \OcTaxClass[] populateRecords($data, $callAfterFind = true, $index = null)
 *
 * --- END ModelDoc ---
 */

class OcTaxClass extends CActiveRecord
{

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return OcTaxClass the static model class
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
        return '{{tax_class}}';
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'products' => array(self::HAS_MANY, 'OcProduct', 'tax_class_id'),
            'taxRules' => array(self::HAS_MANY, 'OcTaxRule', 'tax_class_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'tax_class_id' => Yii::t('app', 'Tax Class'),
            'title' => Yii::t('app', 'Title'),
            'description' => Yii::t('app', 'Description'),
            'date_added' => Yii::t('app', 'Date Added'),
            'date_modified' => Yii::t('app', 'Date Modified'),
        );
    }

}

