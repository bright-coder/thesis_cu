<?php

namespace App\Library\CustomModel;

use App\Library\CustomModel\DBTargetInterface;
use App\Library\CustomModel\ModelOutput\ModelOutputFactory;
use App\Library\Constraint\PrimaryKey;
use App\Model\ChangeRequestInput;

class SqlServer implements DBTargetInterface
{
    /**
     * @var \PDO
     */

    private $conObj = null;
    private $server;
    private $port;
    private $database;
    private $user;
    private $pass;

    public function __construct(string $server, string $port, string $database, string $user, string $pass)
    {
        $this->server = $server;
        $this->port = $port;
        $this->database = $database;
        $this->user = $user;
        $this->pass = $pass;
    }

    public function connect(): bool
    {
        // "dblib:host={$this->server}:{$this->port};dbname={$this->database};LoginTimeout=1"
        // sqlsrv:server={$this->server} ; Database={$this->database};LoginTimeout=1"

        try {
            $this->conObj = new \PDO(
                "sqlsrv:Server={$this->server}, {$this->port};Database={$this->database};LoginTimeout=1",
                $this->user,
                $this->pass
            );
            $this->conObj->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            return false;
        }
        return true;
    }

    public function getDBType(): string
    {
        return "sqlsrv";
    }

    public function getDBServer(): string
    {
        return $this->server;
    }

    public function getDBName(): string
    {
        return $this->database;
    }

    public function getAllTables(): array
    {
        $stmt = $this->conObj->prepare("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_NAME <> 'sysdiagrams'");
        if ($stmt->execute()) {
            //$tables = \array_flip($stmt->fetchAll(\PDO::FETCH_COLUMN));
            return ModelOutputFactory::createTable($stmt->fetchAll(\PDO::FETCH_COLUMN));
        }
        return [];
    }

    public function getInstanceByTableName(string $tableName, array $columnNameList = [],string $condition = ''): array
    {
        $columnName = '*';
        if($columnNameList) {
            $columnName = implode(",", $columnNameList);
        }
        $strQuery = "SELECT {$columnName} FROM {$tableName}";
        if ($condition != '') {
            $strQuery .= " WHERE {$condition}";
        }
        $stmt = $this->conObj->prepare($strQuery);
        if ($stmt->execute()) {
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
    }

    public function getNumRows(string $tableName): int
    {
        $stmt = $this->conObj->prepare("SELECT count(*) as numRows FROM {$tableName}");
        if ($stmt->execute()) {
            return $stmt->fetchColumn();
        }
    }

    public function getDistinctValues(string $tableName, array $columnName): array
    {
        $strSqlColumnName = implode(",", $columnName);
        $stmt = $this->conObj->prepare("SELECT DISTINCT {$strSqlColumnName} FROM {$tableName}");
        if ($stmt->execute()) {
            //return $stmt->fetch(\PDO::FETCH_OBJ)->numDistinctValue;
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        //return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function getAllColumnsByTableName(string $tableName): array
    {
        $stmt = $this->conObj->prepare("SELECT COLUMN_NAME as name,
        DATA_TYPE as dataType,
        COLUMN_DEFAULT as _default,
        IS_NULLABLE as isNullable,
        CHARACTER_MAXIMUM_LENGTH as length,
        NUMERIC_PRECISION as precision,
        NUMERIC_SCALE as scale
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_NAME = :tableName");
        if ($stmt->execute(array(':tableName' => $tableName))) {
            return ModelOutputFactory::createColumn($stmt->fetchAll(\PDO::FETCH_ASSOC));
        }
    }

    public function getAllConstraintsByTableName(string $tableName): array
    {
        $primaryKey = $this->getPKConstraint($tableName);
        $foreignKeys = $this->getFkConstraints($tableName);
        $checkConstraints = $this->getCheckConstraints($tableName);
        $uniqueConstraints = $this->getUniqueConstraints($tableName);

        return [
            'primaryKey' => $primaryKey,
            'foreignKeys' => $foreignKeys,
            'checkConstraints' => $checkConstraints,
            'uniqueConstraints' => $uniqueConstraints
        ];
    }

    public function getPKConstraint(string $tableName): PrimaryKey
    {
        $stmt = $this->conObj->prepare("SELECT TC.Constraint_Name AS name,
        TC.CONSTRAINT_TYPE as type ,
        SC.definition as definition,
        CC.Column_Name AS columnName
        FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS TC
        INNER JOIN
            INFORMATION_SCHEMA.CONSTRAINT_COLUMN_USAGE CC ON
            TC.Constraint_Name = CC.Constraint_Name
        LEFT JOIN
            sys.check_constraints SC ON
            SC.name = TC.Constraint_Name
        WHERE CC.TABLE_NAME = :tableName AND TC.CONSTRAINT_TYPE = 'PRIMARY KEY' ORDER BY type,name");

        if ($stmt->execute([':tableName' => $tableName])) {
            return ModelOutputFactory::createPrimaryKey($stmt->fetchAll(\PDO::FETCH_ASSOC));
        }
        return [];
    }

    public function getFkConstraints(string $tableName): array
    {
        $stmt = $this->conObj->prepare("exec sp_fkeys :tableName");

        if ($stmt->execute([':tableName' => $tableName])) {
            return ModelOutputFactory::createForeignKey($stmt->fetchAll(\PDO::FETCH_ASSOC));
        }
        return [];
    }

    public function getCheckConstraints(string $tableName): array
    {
        $stmt = $this->conObj->prepare("SELECT TC.Constraint_Name AS name,
        TC.CONSTRAINT_TYPE as type ,
        SC.definition as definition,
        CC.Column_Name AS columnName
        FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS TC
        INNER JOIN
            INFORMATION_SCHEMA.CONSTRAINT_COLUMN_USAGE CC ON
            TC.Constraint_Name = CC.Constraint_Name
        LEFT JOIN
            sys.check_constraints SC ON
            SC.name = TC.Constraint_Name
        WHERE CC.TABLE_NAME = :tableName AND TC.CONSTRAINT_TYPE = 'CHECK' ORDER BY type,name");

        if ($stmt->execute([':tableName' => $tableName])) {
            return ModelOutputFactory::createCheckConstraint($stmt->fetchAll(\PDO::FETCH_ASSOC));
        }
        return [];
    }

    public function getUniqueConstraints(string $tableName): array
    {
        $stmt = $this->conObj->prepare("SELECT TC.Constraint_Name AS name,
        TC.CONSTRAINT_TYPE as type ,
        SC.definition as definition,
        CC.Column_Name AS columnName
        FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS TC
        INNER JOIN
            INFORMATION_SCHEMA.CONSTRAINT_COLUMN_USAGE CC ON
            TC.Constraint_Name = CC.Constraint_Name
        LEFT JOIN
            sys.check_constraints SC ON
            SC.name = TC.Constraint_Name
        WHERE CC.TABLE_NAME = :tableName AND TC.CONSTRAINT_TYPE = 'UNIQUE' ORDER BY type,name");

        if ($stmt->execute([':tableName' => $tableName])) {
            return ModelOutputFactory::createUniqueConstraint($stmt->fetchAll(\PDO::FETCH_ASSOC));
        }
        return [];
    }

    public function dropConstraint(string $tableName, string $constraint) : bool
    {
        $stmt = $this->conObj->prepare("ALTER TABLE $tableName DROP CONSTRAINT $constraint");
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function dropColumn(string $tableName, string $columnName): bool
    {
        $stmt = $this->conObj->prepare("ALTER TABLE $tableName DROP COLUMN $columnName");
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function addColumn(array $columnDetail): bool
    {
        $strSqlDataType = $this->getStrSqlDataType(
            $columnDetail['dataType'],
            [
                'length' =>  array_key_exists('length', $columnDetail) ? $columnDetail['length'] : null,
                'precision' => array_key_exists('precision', $columnDetail) ? $columnDetail['precision'] : null,
                'scale' => array_key_exists('scale', $columnDetail) ? $columnDetail['scale'] : null
            ]
        );

        $strSqlDefault = "";
        if (array_key_exists('default', $columnDetail)) {
            $strSqlDefault = "DEFAULT(".$columnDetail['default'].")";
            if ($columnDetail['default'] == null) {
                $strSqlDefault = '';
            }
        
            if ($columnDetail['default'] == '#NULL' && \strcasecmp($columnDetail['nullable'], 'N') == 0) {
                $strSqlDefault = "0";
            }
        }

        if(is_string($columnDetail['nullable'])) {
            $strSqlNullable = $this->getStrSqlNullable(\strcasecmp($columnDetail['nullable'], 'N') == 0 ? false : true);
        }
        else {
            $strSqlNullable = $this->getStrSqlNullable($columnDetail['nullable']);
        }
        

        $strQuery = "ALTER TABLE ".$columnDetail['tableName']." ADD ".$columnDetail['columnName']." ".$strSqlDataType." NULL ".$strSqlDefault;

        $stmt = $this->conObj->prepare($strQuery);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    private function getStrSqlNullable(bool $isNullable): string
    {
        return !$isNullable ? 'NOT NULL' : 'NULL';
    }

    private function getStrSqlDataType(string $dataType, array $info = ['length' => null , 'precision' => null , 'scale' => null]): string
    {

        $dataType = strtolower($dataType);
        if (\strpos($dataType, 'char') !== false || \strpos($dataType, 'char') === 0) {
            $dataType .= "(".$info['length'].")";
        } elseif (\strpos($dataType, 'decimal') !== false || \strpos($dataType, 'decimal') === 0) {
            $precision = $dataType['precision'] == null ? 38 : $dataType['precision'];
            $scale = $dataType['scale'] == null ? 0 : $dataType['scale'] ;
            $dataType .= "($precision,$scale)";
        }

        return $dataType;
    }
    
    public function updateColumn(array $columnDetail): bool
    {
        $strSqlDataType = $this->getStrSqlDataType(
            $columnDetail['dataType'],
            [
                'length' =>  array_key_exists('length', $columnDetail) ? $columnDetail['length'] : null,
                'precision' => array_key_exists('precision', $columnDetail) ? $columnDetail['precision'] : null,
                'scale' => array_key_exists('scale', $columnDetail) ? $columnDetail['scale'] : null
            ]
        );

        $strSqlDefault = "";
        if (array_key_exists('default', $columnDetail)) {
            $strSqlDefault = "DEFAULT(".$columnDetail['default'].")";
            if ($columnDetail['default'] == null) {
                $strSqlDefault = '';
            }
        
            if ($columnDetail['default'] == '#NULL' && \strcasecmp($columnDetail['nullable'], 'N') == 0) {
                $strSqlDefault = "0";
            }
        }

        if(array_key_exists('nullable', $columnDetail)) {
            if(is_string($columnDetail['nullable'])) {
                $strSqlNullable = $this->getStrSqlNullable(\strcasecmp($columnDetail['nullable'], 'N') == 0 ? false : true);
            }
            else {
                 $strSqlNullable = $this->getStrSqlNullable($columnDetail['nullable']);
                }
        }
        else {
            $strSqlNullable = "NULL";
        }

        

        $strQuery = "ALTER TABLE ".$columnDetail['tableName']." ALTER COLUMN ".$columnDetail['columnName']." ".$strSqlDataType." ".$strSqlNullable." ".$strSqlDefault;
        $stmt = $this->conObj->prepare($strQuery);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function setNullable(string $tableName, string $columnName, array $columnDetail): bool
    {
        $dataType = strtolower($columnDetail['dataType']);
        if ($dataType == "int" || $dataType == "date" || $dataType == "datetime") {
        } elseif (\strpos($dataType, 'char') !== false || \strpos($dataType, 'char') == 0) {
            $dataType .= "(".$columnDetail['length'].")";
        } elseif (\strpos($dataType, 'decimal') != false) {
            $precision = $columnDetail['length'] == null ? 38 : $columnDetail['length'];
            $scale = $columnDetail['scale'] == null ? 0 : $columnDetail['scale'];
        
            $dataType .= "($precision,$scale)";
        }
        $nullable = $columnDetail['nullable'] == false ? 'NOT NULL' : "";
        $strQuery = "ALTER TABLE $tableName ALTER COLUMN $columnName $dataType $nullable";
        $stmt = $this->conObj->prepare($strQuery);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getDuplicateInstance(string $tableName, array $checkColumns, array $pkColumns): array
    {
        $strOn = [];
        foreach ($columnName as $column) {
            $strOn[] = "y.".$column."=dt.".$column;
        }
        $selectColumn = array_unique(array_merge($pkColumns, $checkColumns), SORT_REGULAR);
        foreach($selectColumn as $i => $column) {
            $selectColumn[$i] = 'y.'.$column;
        }

        $strSelectColumn = implode(',', $selectColumn);
    
        $strQuery = "SELECT {$strSelectColumn} FROM {$tableName} y 
            INNER JOIN (SELECT ".implode(",", $columnName)."
                        FROM {$tableName}
                        GROUP BY ".\implode(",", $columnName)."
                        HAVING COUNT(*)>1
        ) dt ON ".\implode(" AND ", $strOn);
        $stmt = $this->conObj->prepare($strQuery);
        if ($stmt->execute()) {
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        return [];
    }

    public function updateInstance(string $tableName, string $newColumnName, array $refValues, array $newData, $default): bool
    {
        $columnTemp = $newColumnName;
        $strQuery = "UPDATE $tableName SET $columnTemp = CASE ";
        // if(\array_last($newValues) == false){
        //     $newValues = \array_keys($newValues);
        // }
        
        foreach ($refValues as $index => $refValue) {
            $whenCondition = [];
            foreach ($refValue as $columnName => $oldValue) {
                $whenCondition[] = "$columnName = '$oldValue'";
            }
            $strWhenCondition = \implode(" AND ", $whenCondition);
            $strQuery .= "WHEN $strWhenCondition THEN '{$newData[$index]}' ";
        }
        if ($default == null) {
            $strQuery .= "ELSE NULL";
        } else {
            $strQuery .= "ELSE $default";
        }
        $strQuery .= ' END';
        
        //dd($strQuery);
        $stmt = $this->conObj->prepare($strQuery);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function updateColumnName(string $tableName, string $oldColumnName, string $newColumnName) : bool
    {
        $param = $tableName.".".$oldColumnName;
        $stmt = $this->conObj->prepare("sp_rename '$param', '$newColumnName', 'COLUMN'");
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function addUniqueConstraint(string $tableName, string $columnName) : bool
    {
        $uniqueName = "{$tableName}_{$columnName}_UNIQUE";
        $strQuery = "ALTER TABLE $tableName ADD CONSTRAINT $uniqueName UNIQUE ($columnName)";
        //dd($strQuery);
        $stmt = $this->conObj->prepare($strQuery);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function addCheckConstraint(string $tableName, string $columnName, $min, $max) : bool
    {
        $min = $min == null ? "" : $columnName." >= ".$min;
        $max = $max == null ? "" : $columnName." <= ".$max;
        $AND = ($min == null) || ($max == null) ? "" : "AND";
        $checkName = "{$tableName}_{$columnName}_CHECK";
        $stmt = $this->conObj->prepare("ALTER TABLE $tableName ADD CONSTRAINT $checkName CHECK ($min $AND $max)");
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function addPrimaryKeyConstraint(string $tableName, array $columnName, string $constraintName) : bool
    {
        $this->conObj->query("ALTER TABLE {$tableName} ADD CONSTRAINT {$constraintName} PRIMARY KEY (".implode(",",$columnName).")");
        return true;
    }

    public function addForeignKeyConstraint(string $tableName, array $links, string $constraintName) : bool
    {
        $fromColumnList = [];
        $toTable = "";
        $toColumnList = [];
        foreach($links as $link) {
            $fromColumnList[] = $link['from']['columnName'];
            $toColumnList[] = $link['to']['columnName'];
            $toTable = $link['to']['tableName'];
        }
        $this->conObj->query("ALTER TABLE {$tableName} ADD CONSTRAINT {$constraintName} FOREIGN KEY (".implode(",",$fromColumnList).") REFERENCES {$toTable} (".implode(",", $toColumnList).")");
        return true;
    }

    public function disableConstraint(string $tableName = '?'): bool
    {
        $stmt = $this->conObj->query("EXEC sp_msforeachtable \"ALTER TABLE {$tableName} NOCHECK CONSTRAINT all\"");
        if ($stmt !== false) {
            return true;
        }
        return false;
    }
    public function enableConstraint(string $tableName = '?'): bool
    {
        $stmt = $this->conObj->query("EXEC sp_msforeachtable \"ALTER TABLE {$tableName} CHECK CONSTRAINT all\"");
        //$stmt = $this->conObj->query("EXEC sp_msforeachtable \"ALTER TABLE {$tableName} WITH NOCHECK CONSTRAINT all\"");
        if ($stmt !== false) {
            return true;
        }
        return false;
    }
}
