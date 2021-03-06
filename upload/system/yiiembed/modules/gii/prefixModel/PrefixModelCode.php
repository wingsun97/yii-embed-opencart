<?php

/**
 * PrefixModelCode
 *
 * @author Brett O'Donnell <cornernote@gmail.com>
 * @link https://github.com/cornernote/yii-embed-opencart
 * @copyright 2013 Mr PHP <info@mrphp.com.au>
 * @license BSD-3-Clause https://raw.github.com/cornernote/yii-embed-opencart/master/LICENSE
 */
class PrefixModelCode extends CCodeModel
{
    /**
     * @var string
     */
    public $connectionId = 'db';
    /**
     * @var
     */
    public $tablePrefix;
    /**
     * @var
     */
    public $modelPrefix = 'Oc';
    /**
     * @var
     */
    public $tableName = '*';
    /**
     * @var
     */
    public $modelClass;
    /**
     * @var string
     */
    public $modelPath = 'application.models';
    /**
     * @var string
     */
    public $baseClass = 'CActiveRecord';
    /**
     * @var bool
     */
    public $buildRelations = true;

    /**
     * @var array list of candidate relation code. The array are indexed by AR class names and relation names.
     * Each element represents the code of the one relation in one AR class.
     */
    protected $relations;

    /**
     * @return array
     */
    public function rules()
    {
        return array_merge(parent::rules(), array(
            array('tablePrefix, modelPrefix, baseClass, tableName, modelClass, modelPath, connectionId', 'filter', 'filter' => 'trim'),
            array('connectionId, tableName, modelPath, baseClass', 'required'),
            array('tablePrefix, modelPrefix, tableName, modelPath', 'match', 'pattern' => '/^(\w+[\w\.]*|\*?|\w+\.\*)$/', 'message' => '{attribute} should only contain word characters, dots, and an optional ending asterisk.'),
            array('connectionId', 'validateConnectionId', 'skipOnError' => true),
            array('tableName', 'validateTableName', 'skipOnError' => true),
            array('tablePrefix, modelPrefix, modelClass, baseClass', 'match', 'pattern' => '/^[a-zA-Z_]\w*$/', 'message' => '{attribute} should only contain word characters.'),
            array('modelPath', 'validateModelPath', 'skipOnError' => true),
            array('baseClass, modelClass', 'validateReservedWord', 'skipOnError' => true),
            array('baseClass', 'validateBaseClass', 'skipOnError' => true),
            array('connectionId, tablePrefix, modelPrefix, modelPath, baseClass, buildRelations', 'sticky'),
        ));
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), array(
            'tablePrefix' => 'Table Prefix',
            'modelPrefix' => 'Model Prefix',
            'tableName' => 'Table Name',
            'modelPath' => 'Model Path',
            'modelClass' => 'Model Class',
            'baseClass' => 'Base Class',
            'buildRelations' => 'Build Relations',
            'connectionId' => 'Database Connection',
        ));
    }

    /**
     * @return array
     */
    public function requiredTemplates()
    {
        return array(
            'model.php',
        );
    }

    /**
     * @throws CHttpException
     */
    public function init()
    {
        if (Yii::app()->{$this->connectionId} === null)
            throw new CHttpException(500, 'A valid database connection is required to run this generator.');
        $this->tablePrefix = Yii::app()->{$this->connectionId}->tablePrefix;
        parent::init();
    }

    /**
     *
     */
    public function prepare()
    {
        if (($pos = strrpos($this->tableName, '.')) !== false) {
            $schema = substr($this->tableName, 0, $pos);
            $tableName = substr($this->tableName, $pos + 1);
        }
        else {
            $schema = '';
            $tableName = $this->tableName;
        }
        if ($tableName[strlen($tableName) - 1] === '*') {
            $tables = Yii::app()->{$this->connectionId}->schema->getTables($schema);
            if ($this->tablePrefix != '') {
                foreach ($tables as $i => $table) {
                    if (strpos($table->name, $this->tablePrefix) !== 0)
                        unset($tables[$i]);
                }
            }
        }
        else
            $tables = array($this->getTableSchema($this->tableName));

        $this->files = array();
        $templatePath = $this->templatePath;
        $this->relations = $this->generateRelations();

        foreach ($tables as $table) {
            $tableName = $this->removePrefix($table->name);
            $className = $this->generateClassName($table->name);
            $params = array(
                'tableName' => $schema === '' ? $tableName : $schema . '.' . $tableName,
                'modelClass' => $className,
                'columns' => $table->columns,
                'labels' => $this->generateLabels($table),
                'rules' => $this->generateRules($table),
                'relations' => isset($this->relations[$className]) ? $this->relations[$className] : array(),
                'connectionId' => $this->connectionId,
            );
            $this->files[] = new CCodeFile(
                Yii::getPathOfAlias($this->modelPath) . '/' . $className . '.php',
                $this->render($templatePath . '/model.php', $params)
            );
        }
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validateTableName($attribute, $params)
    {
        if ($this->hasErrors())
            return;

        $invalidTables = array();
        $invalidColumns = array();

        if ($this->tableName[strlen($this->tableName) - 1] === '*') {
            if (($pos = strrpos($this->tableName, '.')) !== false)
                $schema = substr($this->tableName, 0, $pos);
            else
                $schema = '';

            $this->modelClass = '';
            $tables = Yii::app()->{$this->connectionId}->schema->getTables($schema);
            foreach ($tables as $table) {
                if ($this->tablePrefix == '' || strpos($table->name, $this->tablePrefix) === 0) {
                    if (in_array(strtolower($this->modelPrefix . $table->name), self::$keywords))
                        $invalidTables[] = $table->name;
                    if (($invalidColumn = $this->checkColumns($table)) !== null)
                        $invalidColumns[] = $invalidColumn;
                }
            }
        }
        else {
            if (($table = $this->getTableSchema($this->tableName)) === null)
                $this->addError('tableName', "Table '{$this->tableName}' does not exist.");
            if ($this->modelClass === '')
                $this->addError('modelClass', 'Model Class cannot be blank.');

            if (!$this->hasErrors($attribute) && ($invalidColumn = $this->checkColumns($table)) !== null)
                $invalidColumns[] = $invalidColumn;
        }

        if ($invalidTables != array())
            $this->addError('tableName', 'Model class cannot take a reserved PHP keyword! Table name: ' . implode(', ', $invalidTables) . ".");
        if ($invalidColumns != array())
            $this->addError('tableName', 'Column names that does not follow PHP variable naming convention: ' . implode(', ', $invalidColumns) . ".");
    }

    /*
     * Check that all database field names conform to PHP variable naming rules
     * For example mysql allows field name like "2011aa", but PHP does not allow variable like "$model->2011aa"
     * @param CDbTableSchema $table the table schema object
     * @return string the invalid table column name. Null if no error.
     */
    /**
     * @param $table
     * @return string
     */
    public function checkColumns($table)
    {
        foreach ($table->columns as $column) {
            if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $column->name))
                return $table->name . '.' . $column->name;
        }
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validateModelPath($attribute, $params)
    {
        if (Yii::getPathOfAlias($this->modelPath) === false)
            $this->addError('modelPath', 'Model Path must be a valid path alias.');
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validateBaseClass($attribute, $params)
    {
        $class = @Yii::import($this->baseClass, true);
        if (!is_string($class) || !$this->classExists($class))
            $this->addError('baseClass', "Class '{$this->baseClass}' does not exist or has syntax error.");
        elseif ($class !== 'CActiveRecord' && !is_subclass_of($class, 'CActiveRecord'))
            $this->addError('baseClass', "'{$this->model}' must extend from CActiveRecord.");
    }

    /**
     * @param $tableName
     * @return mixed
     */
    public function getTableSchema($tableName)
    {
        $connection = Yii::app()->{$this->connectionId};
        return $connection->getSchema()->getTable($tableName, $connection->schemaCachingDuration !== 0);
    }

    /**
     * @param $table
     * @return array
     */
    public function generateLabels($table)
    {
        $labels = array();
        foreach ($table->columns as $column) {
            $label = ucwords(trim(strtolower(str_replace(array('-', '_'), ' ', preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $column->name)))));
            $label = preg_replace('/\s+/', ' ', $label);
            if (strcasecmp(substr($label, -3), ' id') === 0)
                $label = substr($label, 0, -3);
            if ($label === 'Id')
                $label = 'ID';
            $labels[$column->name] = $label;
        }
        return $labels;
    }

    /**
     * @param $table
     * @return array
     */
    public function generateRules($table)
    {
        $rules = array();
        $required = array();
        $integers = array();
        $numerical = array();
        $length = array();
        $safe = array();
        foreach ($table->columns as $column) {
            if ($column->autoIncrement)
                continue;
            $r = !$column->allowNull && $column->defaultValue === null;
            if ($r)
                $required[] = $column->name;
            if ($column->type === 'integer')
                $integers[] = $column->name;
            elseif ($column->type === 'double')
                $numerical[] = $column->name;
            elseif ($column->type === 'string' && $column->size > 0)
                $length[$column->size][] = $column->name;
            elseif (!$column->isPrimaryKey && !$r)
                $safe[] = $column->name;
        }
        if ($required !== array())
            $rules[] = "array('" . implode(', ', $required) . "', 'required')";
        if ($integers !== array())
            $rules[] = "array('" . implode(', ', $integers) . "', 'numerical', 'integerOnly'=>true)";
        if ($numerical !== array())
            $rules[] = "array('" . implode(', ', $numerical) . "', 'numerical')";
        if ($length !== array()) {
            foreach ($length as $len => $cols)
                $rules[] = "array('" . implode(', ', $cols) . "', 'length', 'max'=>$len)";
        }
        if ($safe !== array())
            $rules[] = "array('" . implode(', ', $safe) . "', 'safe')";

        return $rules;
    }

    /**
     * @param $className
     * @return array
     */
    public function getRelations($className)
    {
        return isset($this->relations[$className]) ? $this->relations[$className] : array();
    }

    /**
     * @param $tableName
     * @param bool $addBrackets
     * @return string
     */
    protected function removePrefix($tableName, $addBrackets = true)
    {
        if ($addBrackets && Yii::app()->{$this->connectionId}->tablePrefix == '')
            return $tableName;
        $prefix = $this->tablePrefix != '' ? $this->tablePrefix : Yii::app()->{$this->connectionId}->tablePrefix;
        if ($prefix != '') {
            if ($addBrackets && Yii::app()->{$this->connectionId}->tablePrefix != '') {
                $prefix = Yii::app()->{$this->connectionId}->tablePrefix;
                $lb = '{{';
                $rb = '}}';
            }
            else
                $lb = $rb = '';
            if (($pos = strrpos($tableName, '.')) !== false) {
                $schema = substr($tableName, 0, $pos);
                $name = substr($tableName, $pos + 1);
                if (strpos($name, $prefix) === 0)
                    return $schema . '.' . $lb . substr($name, strlen($prefix)) . $rb;
            }
            elseif (strpos($tableName, $prefix) === 0)
                return $lb . substr($tableName, strlen($prefix)) . $rb;
        }
        return $tableName;
    }

    /**
     * @return array
     */
    protected function generateRelations()
    {
        if (!$this->buildRelations)
            return array();
        $relations = array();
        foreach (Yii::app()->{$this->connectionId}->schema->getTables() as $table) {
            if ($this->tablePrefix != '' && strpos($table->name, $this->tablePrefix) !== 0)
                continue;
            $tableName = $table->name;

            if ($this->isRelationTable($table)) {
                $pks = $table->primaryKey;
                $fks = $this->getForeignKeys($table);

                $table0 = $fks[$pks[0]][0];
                $table1 = $fks[$pks[1]][0];
                $className0 = $this->generateClassName($table0);
                $className1 = $this->generateClassName($table1);

                $unprefixedTableName = $this->removePrefix($tableName);

                $relationName = $this->generateRelationName($table0, $this->removePrefix($table1, false), true);
                $relations[$className0][$relationName] = "array(self::MANY_MANY, '$className1', '$unprefixedTableName($pks[0], $pks[1])')";

                $relationName = $this->generateRelationName($table1, $this->removePrefix($table0, false), true);

                $i = 1;
                $rawName = $relationName;
                while (isset($relations[$className1][$relationName]))
                    $relationName = $rawName . $i++;

                $relations[$className1][$relationName] = "array(self::MANY_MANY, '$className0', '$unprefixedTableName($pks[1], $pks[0])')";
            }
            else {
                $className = $this->generateClassName($tableName);
                foreach ($this->getForeignKeys($table) as $fkName => $fkEntry) {
                    // Put table and key name in variables for easier reading
                    $refTable = $fkEntry[0]; // Table name that current fk references to
                    $refKey = $fkEntry[1]; // Key in that table being referenced
                    $refClassName = $this->generateClassName($refTable);

                    // Add relation for this table
                    $relationName = $this->generateRelationName($tableName, $fkName, false);
                    $relations[$className][$relationName] = "array(self::BELONGS_TO, '$refClassName', '$fkName')";

                    // Add relation for the referenced table
                    $relationType = $table->primaryKey === $fkName ? 'HAS_ONE' : 'HAS_MANY';
                    $relationName = $this->generateRelationName($refTable, $this->removePrefix($tableName, false), $relationType === 'HAS_MANY');
                    $i = 1;
                    $rawName = $relationName;
                    while (isset($relations[$refClassName][$relationName]))
                        $relationName = $rawName . ($i++);
                    $relations[$refClassName][$relationName] = "array(self::$relationType, '$className', '$fkName')";
                }
            }
        }
        return $relations;
    }

    /**
     * Generates foreign keys based on related fields being called [foreign_table]_id
     * @param $table
     * @return array
     */
    public function getForeignKeys($table)
    {
        $foreignKeys = array();
        $schema = $this->getTableSchema($table->name);
        foreach ($schema->columns as $columnName => $column) {
            if (substr($columnName, -3) == '_id' && $column->type == 'integer') {
                $relationTable = $this->tablePrefix . substr($columnName, 0, -3);
                if ($table->name == $relationTable)
                    continue;
                $foreignKeys[$columnName] = array($relationTable, $columnName);
            }
        }
        return $foreignKeys;
    }

    /**
     * Checks if the given table is a "many to many" pivot table.
     * Their PK has 2 fields, and both of those fields are also FK to other separate tables.
     * @param CDbTableSchema table to inspect
     * @return boolean true if table matches description of helpter table.
     */
    protected function isRelationTable($table)
    {
        $pk = $table->primaryKey;
        $fks = $this->getForeignKeys($table);
        return (count($pk) === 2 // we want 2 columns
            && isset($fks[$pk[0]]) // pk column 1 is also a foreign key
            && isset($fks[$pk[1]]) // pk column 2 is also a foriegn key
            && $fks[$pk[0]][0] !== $fks[$pk[1]][0]); // and the foreign keys point different tables
    }

    /**
     * @param $tableName
     * @return string
     */
    protected function generateClassName($tableName)
    {
        if ($this->tableName === $tableName || ($pos = strrpos($this->tableName, '.')) !== false && substr($this->tableName, $pos + 1) === $tableName)
            return $this->modelPrefix . $this->modelClass;

        $tableName = $this->removePrefix($tableName, false);
        $className = '';
        foreach (explode('_', $tableName) as $name) {
            if ($name !== '')
                $className .= ucfirst($name);
        }
        return $this->modelPrefix . $className;
    }

    /**
     * Generate a name for use as a relation name (inside relations() function in a model).
     * @param string the name of the table to hold the relation
     * @param string the foreign key name
     * @param boolean whether the relation would contain multiple objects
     * @return string the relation name
     */
    protected function generateRelationName($tableName, $fkName, $multiple)
    {
        if (strcasecmp(substr($fkName, -2), 'id') === 0 && strcasecmp($fkName, 'id'))
            $relationName = rtrim(substr($fkName, 0, -2), '_');
        else
            $relationName = $fkName;
        $relationName[0] = strtolower($relationName);

        if ($multiple)
            $relationName = $this->pluralize($relationName);

        $names = preg_split('/_+/', $relationName, -1, PREG_SPLIT_NO_EMPTY);
        if (empty($names)) return $relationName; // unlikely
        for ($name = $names[0], $i = 1; $i < count($names); ++$i)
            $name .= ucfirst($names[$i]);

        $rawName = $name;
        $table = Yii::app()->{$this->connectionId}->schema->getTable($tableName);
        $i = 0;
        while (isset($table->columns[$name]))
            $name = $rawName . ($i++);

        return $name;
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function validateConnectionId($attribute, $params)
    {
        if (Yii::app()->hasComponent($this->connectionId) === false || !(Yii::app()->getComponent($this->connectionId) instanceof CDbConnection))
            $this->addError('connectionId', 'A valid database connection is required to run this generator.');
    }
}
