<?php

namespace App\Library\CustomModel;

use App\Library\CustomModel\DBConnector;

class SqlServer implements DBConnector {
    /**
    * @var \PDO
    */
    private $conObj;
    private $server;
    private $database;

    public function __construct(string $server, string $database, string $user, string $pass){
        $this->conObj = new \PDO("sqlsrv:server={$server} ; Database = {$database}",$user,$pass);
        $this->server = $server;
        $this->database = $database;
    }

    public function getDBType(): string{
        return "sqlsrv";
    }

    public function getDBServer(): string{
        return $this->server;
    }

    public function getDBName(): string{
        return $this->database;
    }

    public function getAllTables(): array{
        $stmt = $this->conObj->prepare("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'");
        if($stmt->execute() ){
            return $stmt->fetchAll(\PDO::FETCH_COLUMN);
        }
    }

    public function getAllColumns(string $tableName): array{
        $stmt = $this->conObj->prepare("SELECT COLUMN_NAME, DATA_TYPE, COLUMN_DEFAULT, IS_NULLABLE, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, NUMERIC_SCALE
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_NAME = :tableName");
        if($stmt->execute(array(':tableName' => $tableName)) ){
            return $stmt->fetchAll(\PDO::FETCH_OBJ);
        }
    }

    public function getPkColumns(string $tableName): array{
        $stmt = $this->conObj->prepare("SELECT Col.Column_Name as columnName from
            INFORMATION_SCHEMA.TABLE_CONSTRAINTS Tab,
            INFORMATION_SCHEMA.CONSTRAINT_COLUMN_USAGE Col
        WHERE
            Col.Constraint_Name = Tab.Constraint_Name
            AND Col.Table_Name = Tab.Table_Name
            AND Constraint_Type = 'PRIMARY KEY'
            AND Col.Table_Name = ':tableName'");
        if($stmt->execute(array(':tableName' => $tableName)) ){
            return $stmt->fetchAll(\PDO::FETCH_COLUMN);
        }
    }

    public function getFkColumns(string $tableName): array{
        $fkColumns = NULL;
        //sch.name    AS [schema_name],
        //tab1.name   AS [tableName]
        $stmt = $this->conObj->prepare("SELECT 
                        obj.name      AS FK_NAME,
                        col1.name     AS [columnName],
                        tab2.name     AS [referencedTable],
                        col2.name     AS [referencedColumn]
                    FROM 
                        sys.foreign_key_columns fkc
                    INNER JOIN sys.objects obj
                        ON obj.object_id = fkc.constraint_object_id
                    INNER JOIN sys.tables tab1
                        ON tab1.object_id = fkc.parent_object_id
                    INNER JOIN sys.columns col1
                        ON col1.column_id = parent_column_id AND col1.object_id = tab1.object_id
                    INNER JOIN sys.tables tab2
                        ON tab2.object_id = fkc.referenced_object_id
                    INNER JOIN sys.columns col2
                        ON col2.column_id = referenced_column_id 
                            AND col2.object_id =  tab2.object_id
                    WHERE tab1.name = :tableName");

        if($stmt->execute(array($tableName)) ){
            $fkColumns = [];
            while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
                $fkColumns[$row['FK_NAME']] = $row;
            }
        }
        return $fkColumns;
    }

}