<?php

namespace App\Library\CustomModel;

use App\Library\CustomModel\DBTargetInterface;
use App\Library\CustomModel\ModelOutput\ModelOutputFactory;

class SqlServer implements DBTargetInterface
{
    /**
     * @var \PDO
     */

    private $conObj = null;
    private $server;
    private $database;
    private $user;
    private $pass;

    public function __construct(string $server, string $database, string $user, string $pass)
    {

        $this->server = $server;
        $this->database = $database;
        $this->user = $user;
        $this->pass = $pass;
    }

    public function connect(): bool
    {
        try {
            $this->conObj = new \PDO(
                "sqlsrv:server={$this->server} ; Database = {$this->database}",
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
        $stmt = $this->conObj->prepare("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'");
        if ($stmt->execute()) {
            //$tables = \array_flip($stmt->fetchAll(\PDO::FETCH_COLUMN));
            return ModelOutputFactory::createOutput(ModelOutputType::TABLE, $stmt->fetchAll(\PDO::FETCH_COLUMN));
        }
    }

    public function getDistinctValues(string $tableName, string $columnName): array
    {
        $stmt = $this->conObj->prepare("SELECT DISTINCT {$columnName} as distinctValues FROM {$tableName}");
        if ($stmt->execute()) {
            //return $stmt->fetch(\PDO::FETCH_OBJ)->numDistinctValue;
            return $stmt->fetchAll(\PDO::FETCH_COLUMN);
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
            return ModelOutputFactory::createOutput(ModelOutputType::COLUMN, $stmt->fetchAll(\PDO::FETCH_ASSOC));
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
        $stmt = $this->conObj->prepare("exec sp_fkeys ':tableName'");

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

    public function updateDatabase()
    {

    }

}
