<?php

namespace App\Library\CustomModel;

use App\Library\CustomModel\DBTargetInterface;
use App\Library\CustomModel\ModelOutput\ModelOutputFactory;
use App\Library\Constraint\PrimaryKey;
use App\Model\ChangeRequestInput;
use App\Library\Database\Database;

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

    private function dropConstraintSQL(string $tableName, string $constraint) : string
    {
        // $stmt = $this->conObj->prepare("ALTER TABLE $tableName DROP CONSTRAINT $constraint");
        // if ($stmt->execute()) {
        //     return true;
        // }
        // return false;
        return "ALTER TABLE $tableName DROP CONSTRAINT $constraint";
    }

    private function dropColumnSQL(string $tableName, string $columnName): string
    {

        return "ALTER TABLE $tableName DROP COLUMN $columnName";
    }

    private function addColumnSQL(array $columnDetail): string
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
        return $strQuery;
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
    
    private function updateColumnSQL(array $columnDetail): string
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
        
        return $strQuery;
        // $stmt = $this->conObj->prepare($strQuery);
        // if ($stmt->execute()) {
        //     return true;
        // }
        // return false;
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

    private function updateColumnNameSQL(string $tableName, string $oldColumnName, string $newColumnName) : string
    {
        $param = $tableName.".".$oldColumnName;
        $stmt = $this->conObj->prepare("sp_rename '$param', '$newColumnName', 'COLUMN'");
        return "sp_rename '$param', '$newColumnName', 'COLUMN'";
    }

    private function addUniqueConstraintSQL(string $tableName, array $columnName, string $constraintName) : string
    {
        if($constraintName == "") {
            $strQuery = "ALTER TABLE {$tableName} ADD UNIQUE (".implode(",",$columnName).")";
        }
        else {
            $strQuery = "ALTER TABLE {$tableName} ADD CONSTRAINT {$constraintName} UNIQUE (".implode(",",$columnName).")";
        }
        return $strQuery;
    }

    private function addCheckConstraintSQL(string $tableName, string $columnName, $min, $max) : string
    {
        $min = $min == null ? "" : $columnName." >= ".$min;
        $max = $max == null ? "" : $columnName." <= ".$max;
        $AND = ($min == null) || ($max == null) ? "" : "AND";
        $checkName = "{$tableName}_{$columnName}_CHECK";

        return "ALTER TABLE $tableName ADD CONSTRAINT $checkName CHECK ($min $AND $max)";
    }

    private function addPrimaryKeyConstraintSQL(string $tableName, array $columnName, string $constraintName) : string
    {
        return "ALTER TABLE {$tableName} ADD CONSTRAINT {$constraintName} PRIMARY KEY (".implode(",",$columnName).")";
    }

    private function addForeignKeyConstraintSQL(string $tableName, array $links, string $constraintName) : string
    {
        $fromColumnList = [];
        $toTable = "";
        $toColumnList = [];
        foreach($links as $link) {
            $fromColumnList[] = $link['from']['columnName'];
            $toColumnList[] = $link['to']['columnName'];
            $toTable = $link['to']['tableName'];
        }
        //$this->conObj->query("ALTER TABLE {$tableName} ADD CONSTRAINT {$constraintName} FOREIGN KEY (".implode(",",$fromColumnList).") REFERENCES {$toTable} (".implode(",", $toColumnList).")");
        return "ALTER TABLE {$tableName} ADD CONSTRAINT {$constraintName} FOREIGN KEY (".implode(",",$fromColumnList).") REFERENCES {$toTable} (".implode(",", $toColumnList).")";
    }

    private function updateInstanceSQL(string $tableName, array $pkColumns, array $newInsColumns): string {
        $pkColumnsStr = [];
        foreach($pkColumns as $columnName => $value) {
            $pkColumnsStr[] = $columnName." = '{$value}'";
        }
        $pkColumnsStr = implode(", ", $pkColumnsStr);
        $newInsColumnsStr = [];
        foreach($newInsColumns as $columnName => $value) {
            $newInsColumnsStr[] = $columnName." = '{$value}'";
        }
        $newInsColumnsStr = implode(", ", $newInsColumnsStr);
        return "UPDATE {$tableName} SET {$newInsColumnsStr} WHERE {$pkColumnsStr}";
    }

    public function updateDatabase(array $scImpacts, array $insImpacts, array $keyImpacts, Database $dbTarget): bool {
        
        $isSuccess = true;
        $this->conObj->query("EXEC sp_msforeachtable \"ALTER TABLE ? NOCHECK CONSTRAINT all\"");
        try {
            $this->conObj->beginTransaction();
            $pkTrace = [];
            $fkTrace = [];
            $checkTrace = [];
            $uniqueTrace = [];
            $notNullTrace = [];
            $columnEdit = [];
            foreach ($scImpacts as $tableName => $columnList) {
                foreach ($columnList as $columnName => $info) {
                    switch ($info['changeType']) {
                        case 'add':
                        $this->conObj->query($this->addColumnSQL($info['new']));
                           if ($info['new']['nullable'] == 'N') {
                               if (!isset($notNullTrace[$tableName])) {
                                   $notNullTrace[$tableName] = [];
                               }
                               $notNullTrace[$tableName][$columnName] = $info['new'];
                           }
                            if ($info['new']['unique'] == 'Y') {
                                if (!isset($uniqueTrace[$tableName])) {
                                    $uniqueTrace[$tableName] = [];
                                }
                                $uniqueTrace[$tableName]["UNIQUE#new_".$columnName] = true;
                            }
                             if (isset($info['new']) || isset($info['max'])) {
                                 if (!isset($checkTrace[$tableName])) {
                                     $checkTrace[$tableName] = [];
                                 }
                                 $checkTrace[$tableName][$columnName] = [
                                    'max' => isset($info['max']) ? $info['max'] : null,
                                    'min' => isset($info['min']) ? $info['min'] : null,
                                ];
                             }
                            break;
                        case 'delete':
                            if (isset($keyImpacts[$tableName])) {
                                foreach ($keyImpacts[$tableName] as $consName => $conInfo) {
                                    $this->conObj->query($this->dropConstraintSQL($tableName, $consName));
                                }
                            }
                            $checkConstraints = $dbTarget->getTableByName($tableName)->getAllCheckConstraint();
                            $arrayCheckRelated = [];
                            foreach ($checkConstraints as $checkConstraint) {
                                foreach ($checkConstraint->getColumns() as $column) {
                                    if ($column == $columnName) {
                                        $arrayCheckRelated[] = $checkConstraint;
                                        break;
                                    }
                                }
                            }
                            foreach ($arrayCheckRelated as $check) {
                                $this->conObj->query($this->dropConstraintSQL($tableName, $check->getName()));
                            }
                            $this->conObj->query($this->dropColumnSQL($tableName, $columnName));
                            break;
                        
                        default: // EDIT
                        if(!isset($columnEdit[$tableName])) {
                            $columnEdit[$tableName] = [];
                        }

                        $isCompat = true;
                        if(isset($info['new']['dataType'])){
                            switch (strtolower($info['new']['dataType'])) {
                                case 'char':
                                case 'varchar':
                                    $catNew = 1;
                                    break;
                                case 'nchar':
                                case 'nvarchar':
                                    $catNew = 2;
                                    break;
                                case 'int' :
                                    $catNew = 3;
                                    break;
                                case 'float':
                                    $catNew = 4;
                                    break;
                                case 'decimal':
                                    $catNew = 5;
                                    break;
                                case 'date':
                                    $catNew = 6;
                                    break;
                                case 'datetime' :
                                    $catNew = 7;
                                    break;
                            }

                            switch (strtolower($info['old']['dataType'])) {
                                case 'char':
                                case 'varchar':
                                    $cat = 1;
                                    break;
                                case 'nchar':
                                case 'nvarchar':
                                    $cat = 2;
                                    break;
                                case 'int' :
                                    $cat = 3;
                                    break;
                                case 'float':
                                    $cat = 4;
                                    break;
                                case 'decimal':
                                    $cat = 5;
                                    break;
                                case 'date':
                                    $cat = 6;
                                    break;
                                case 'datetime' :
                                    $cat = 7;
                                    break;
                            }
                            $isCompat = $cat == $catNew;

                        }

                        $columnEdit[$tableName][] = ['isCompat' => $isCompat, 'columnName' => $columnName];
                        $columnDetail = [
                            'length' => isset($info['new']['length']) ? $info['new']['length'] : $info['old']['length'],
                            'precision' => isset($info['new']['precision']) ? $info['new']['precision'] : $info['old']['precision'],
                            'scale' => isset($info['new']['scale']) ? $info['new']['scale'] : $info['old']['scale'],
                            'dataType' => isset($info['new']['dataType']) ? $info['new']['dataType'] : $info['old']['dataType'],
                            'default' => isset($info['new']['default']) ? $info['new']['default'] : $info['old']['default'],
                            'nullable' => isset($info['new']['nullable']) ? $info['new']['nullable'] : $info['old']['nullable'],
                            'unique' => isset($info['new']['unique']) ? $info['new']['unique'] : $info['old']['unique'],
                            'min' => isset($info['new']['min']) ? $info['new']['min'] : $info['old']['min'],
                            'max' => isset($info['new']['max']) ? $info['new']['max'] : $info['old']['max'],
                            'tableName' => $info['old']['tableName'],
                            'columnName' => $info['old']['columnName']."#temp",

                        ];
                        $this->conObj->query($this->addColumnSQL($columnDetail));

                        
    
                        if ($dbTarget->getTableByName($tableName)->isFK($columnName)) {
                            foreach ($dbTarget->getTableByName($tableName)->getAllFK() as $fk) {
                                foreach ($fk->getColumns() as $link) {
                                    if ($link['from']['tableName'] == $tableName && $link['from']['columnName']) {
                                        $isTrace = true;
                                        if ($keyImpacts) {
                                            if (isset($keyImpacts[$tableName])) {
                                                if (isset($keyImpacts[$tableName][$fk->getName()])) {
                                                    $isTrace = false;
                                                }
                                            }
                                        }
                                        if ($isTrace) {
                                            if (!isset($fkTrace[$tableName])) {
                                                $fkTrace[$tableName] = [];
                                            }
                                            $fkTrace[$tableName][$fk->getName()] = $fk;
                                        }
                                    }
                                }
                                $this->conObj->query($this->dropConstraintSQL($tableName, $fk->getName()));
                            }
                        }
                        
                        if ($info['isPK']) {
                            foreach ($dbTarget->findFKRelated($tableName, $columnName) as $fk) {
                                $fkTableName = $fk->getColumns()[0]['from']['tableName'];
                                $isTrace = true;
                                if ($keyImpacts) {
                                    if (isset($keyImpacts[$fkTableName])) {
                                        if (isset($keyImpacts[$fkTableName][$fk->getName()])) {
                                            $isTrace = false;
                                        }
                                    }
                                }
                                if ($isTrace) {
                                    if (!isset($fkTrace[$fkTableName])) {
                                        $fkTrace[$fkTableName] = [];
                                    }
                                    $fkTrace[$fkTableName][$fk->getName()] = $fk;
                                }
                                $this->conObj->query($this->dropConstraintSQL($fkTableName, $fk->getName()));
                            }
                            if (!isset($pkTrace[$tableName])) {
                                $pkTrace[$tableName] = $dbTarget->getTableByName($tableName)->getPK();
                            }
                            $this->conObj->query($this->dropConstraintSQL($tableName, $dbTarget->getTableByName($tableName)->getPK()->getName()));
                        }
                        
                        if ($columnDetail['unique'] == 'Y' || $columnDetail['unique'] == true) {
                            foreach ($dbTarget->findUniqueConstraintRelated($tableName, $columnName) as $unique) {
                                $isTrace = true;
                                if ($keyImpacts) {
                                    if (isset($keyImpacts[$tableName])) {
                                        if (isset($keyImpacts[$tableName][$unique->getName()])) {
                                            $isTrace = false;
                                        }
                                    }
                                }
                                if ($isTrace) {
                                    if (!isset($uniqueTrace[$tableName])) {
                                        $uniqueTrace[$tableName] = [];
                                    }
                                    $uniqueTrace[$tableName][$unique->getName()] = $unique;
                                }
                            }
                            if($columnDetail['unique'] == 'Y') {
                                $uniqueTrace[$tableName]['#new'.$columnDetail['columnName']] = $columnDetail['columnName'];
                            }
                        }
    
                        foreach ($dbTarget->findUniqueConstraintRelated($tableName, $columnName) as $unique) {
                            $this->conObj->query($this->dropConstraintSQL($tableName, $unique->getName()));
                        }
    
                        if ($columnDetail['nullable'] == 'N' || $columnDetail['nullable'] == false) {
                            if (!isset($notNullTrace[$tableName])) {
                                $notNullTrace[$tableName] = [];
                            }
                            $notNullTrace[$tableName][$columnName] = $columnDetail;
                            $notNullTrace[$tableName][$columnName]['columnName'] = $info['old']['columnName'];
                        }
                        
    
                        switch ($columnDetail['dataType']) {
                            case 'int':
                            case 'float':
                            case 'decimal':
                            $min = $info['old']['min'];
                            if (array_key_exists('min', $info['new'])) {
                                $min = $info['new']['min'] == '#NULL' ? null : $info['new']['min'];
                            }
            
                            $max = $info['old']['max'];
                            if (array_key_exists('max', $info['new'])) {
                                $max = $info['new']['max'] == '#NULL' ? null : $info['new']['max'];
                            }
                            if ($min != null && $max != null) {
                                if(!isset($checkTrace[$tableName])) {
                                    $checkTrace[$tableName] = [];
                                }
                                $checkTrace[$tableName][$columnName] = ['min' => $min, 'max' => $max]; 
                            }
                                break;
                        }
                        foreach ($dbTarget->findCheckConstraintRelated($tableName, $columnName) as $check) {
                            $this->conObj->query($this->dropConstraintSQL($tableName, $check->getName()));
                        }
                            break;
                    }
                }
            }
            
            foreach($insImpacts as $tableName => $recordList) {
                foreach($recordList as $row) {
                    $newInsColumns = [];
                    foreach($row['columnList'] as $columnName => $info) {
                        if($info['changeType'] == 'add') {
                            $newInsColumns[$columnName] = $info['newValue'];
                        }
                        elseif($info['changeType'] == 'edit') {
                            $newInsColumns[$columnName.'#temp'] = $info['newValue'];
                        }
                    }
                    //dd($row['pkRecord']);
                    $this->conObj->query($this->updateInstanceSQL($tableName, $row['pkRecord'], $newInsColumns));
                }
            
            }
            
            foreach($columnEdit as $tableName => $columnList) {
                //dd($columnList);
                foreach($columnList as $info) {
                    $tempCol = $info['columnName'].'#temp';
                    if($info['isCompat']) {
                        $this->conObj->query("UPDATE {$tableName} SET {$tempCol} = {$info['columnName']}  WHERE {$info['columnName']} IS NULL");
                    }
                    
                    $this->conObj->query($this->dropColumnSQL($tableName, $info['columnName']));
                    $this->conObj->query($this->updateColumnNameSQL($tableName, $info['columnName'].'#temp', $info['columnName']));
                }
            }

            foreach($notNullTrace as $tableName => $columnList) {
                foreach($columnList as $columnName => $info) {
                    //dd($insImpacts);
                    $this->conObj->query($this->updateColumnSQL($info));
                    
                }
            }
            
            foreach($pkTrace as $tableName => $pk) {
                $this->conObj->query($this->addPrimaryKeyConstraintSQL($tableName, $pk->getColumns(), $pk->getName()));
            }

            foreach($uniqueTrace as $tableName => $uniqueList) {
                foreach($uniqueList as $unique) {
                    if(is_string($unique)) {
                        $this->conObj->query($this->addUniqueConstraintSQL($tableName, [$unique], ""));
                    } else {
                        $this->conObj->query($this->addUniqueConstraintSQL($tableName, $unique->getColumns(), $unique->getName()));
                    }
                    
                }
            }

            foreach($fkTrace as $tableName => $fkList) {
                foreach($fkList as $fkName => $info) {
                    $this->conObj->query($this->addForeignKeyConstraintSQL($fkName, $info->getColumns(), $info->getName()));
                }
            }

            foreach($checkTrace as $tableName => $checkList) {
                foreach($checkList as $columnName => $info){
                    $this->conObj->query($this->addCheckConstraintSQL($tableName, $columnName, $info['min'], $info['max']));
                }
            }

            $this->conObj->commit();

        } catch(PDOException $e) {
            $this->conObj->rollBack();
            $isSuccess = false;
            dd($e);
        }

        $this->conObj->query("EXEC sp_msforeachtable \"ALTER TABLE ? CHECK CONSTRAINT all\"");
        return $isSuccess;
    }
}
