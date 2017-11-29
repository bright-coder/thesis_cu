<?php

namespace App\Library\CustomModel;

use App\Library\CustomModel\DBConnector;
use App\Library\CustomModel\ModelOutput\ModelOutputFactory;
use App\Library\CustomModel\ModelOutput\ModelOutputType;
use App\Library\Database\Database;

class SqlServer implements DBConnector
{
    /**
     * @var \PDO
     */
    private $conObj;
    private $server;
    private $database;

    public function __construct(string $server, string $database, string $user, string $pass)
    {
        $this->conObj = new \PDO("sqlsrv:server={$server} ; Database = {$database}", $user, $pass);
        $this->server = $server;
        $this->database = $database;
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
        $stmt = $this->conObj->prepare("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'");
        if ($stmt->execute()) {
            //$tables = \array_flip($stmt->fetchAll(\PDO::FETCH_COLUMN));
            return ModelOutputFactory::createOutput(ModelOutputType::TABLE, $stmt->fetchAll(\PDO::FETCH_COLUMN));
        }
    }

    public function getNumDistinctValues(string $tableName, string $columnName): int
    {
        $stmt = $this->conObj->prepare("SELECT COUNT(DISTINCT {$columnName}) as numDistinctValue FROM {$tableName}");
        if ($stmt->execute()) {
            return $stmt->fetch(\PDO::FETCH_OBJ)->numDistinctValue;
        }
        return 0;
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
            return ModelOutputFactory::createOutput(ModelOutputType::COLUMN, $stmt->fetchAll(\PDO::FETCH_ASSOC));
        }
    }

    public function getAllConstraintsByTableName(string $tableName): array
    {

        $stmt = $this->conObj->prepare("SELECT TC.Constraint_Name AS name,
                        TC.CONSTRAINT_TYPE as type ,
						SC.definition as definition,
						CC.Column_Name AS columnName,
						FK.fromTable AS fromTable,
                        FK.fromColumn AS fromColumn,
                        FK.toTable AS toTable,
                        Fk.toColumn AS toColumn
                    FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS TC
                    INNER JOIN
                        INFORMATION_SCHEMA.CONSTRAINT_COLUMN_USAGE CC ON
                        TC.Constraint_Name = CC.Constraint_Name
					LEFT JOIN
						sys.check_constraints SC ON
						SC.name = TC.Constraint_Name
					LEFT JOIN
                    (SELECT
                        obj.name      AS FK_NAME,
                        tab1.name     AS fromTable,
                        col1.name     AS fromColumn,
                        tab2.name     AS toTable,
                        col2.name     AS toColumn
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
                    ) AS FK
						ON FK.FK_NAME = TC.Constraint_Name
                    WHERE CC.TABLE_NAME = :tableName ORDER BY type,name");

        if ($stmt->execute([':tableName' => $tableName])) {
            return ModelOutputFactory::createOutput(ModelOutputType::CONSTRAINT, $stmt->fetchAll(\PDO::FETCH_ASSOC));
        }

    }

}
