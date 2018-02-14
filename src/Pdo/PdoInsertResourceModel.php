<?php

namespace RebelCode\Storage\Resource\Pdo;

use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeContainerCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Storage\Resource\InsertCapableInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use PDO;
use RebelCode\Storage\Resource\Pdo\Query\BuildInsertSqlCapableTrait;
use RebelCode\Storage\Resource\Pdo\Query\EscapeSqlReferenceCapableTrait;
use RebelCode\Storage\Resource\Pdo\Query\GetPdoValueHashStringCapableTrait;

/**
 * Concrete implementation of an INSERT resource model for use with a PDO database connection.
 *
 * This generic implementation can be instantiated to INSERT into any table. An optional field-to-column map may be
 * provided which is used to translate consumer-friendly field names in insertion data sets to  their actual column
 * counterpart names.
 *
 * @since [*next-version*]
 */
class PdoInsertResourceModel extends AbstractPdoResourceModel implements InsertCapableInterface
{
    /*
     * Provides PDO SQL INSERT functionality.
     *
     * @since [*next-version*]
     */
    use PdoInsertCapableTrait;

    /*
     * Provides functionality for building INSERT SQL queries.
     *
     * @since [*next-version*]
     */
    use BuildInsertSqlCapableTrait;

    /*
     * Provides SQL reference escaping functionality.
     *
     * @since [*next-version*]
     */
    use EscapeSqlReferenceCapableTrait;

    /*
     * Provides PDO value hash generation functionality.
     *
     * @since [*next-version*]
     */
    use GetPdoValueHashStringCapableTrait;

    /*
     * Provides SQL table list storage functionality.
     *
     * @since [*next-version*]
     */
    use SqlTablesAwareTrait;

    /*
     * Provides SQL field-to-column map storage functionality.
     *
     * @since [*next-version*]
     */
    use SqlFieldColumnMapAwareTrait;

    /*
     * Provides SQL column name list storage functionality.
     *
     * @since [*next-version*]
     */
    use SqlColumnNamesAwareTrait;

    /*
     * Provides functionality for reading from any kind of container.
     *
     * @since [*next-version*]
     */
    use ContainerGetCapableTrait;

    /**
     * Provides functionality for checking for key existence in any kind of container.
     *
     * @since [*next-version*]
     */
    use ContainerHasCapableTrait;

    /*
     * Provides functionality for normalizing containers.
     *
     * @since [*next-version*]
     */
    use NormalizeContainerCapableTrait;

    /**
     * Provides string normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeStringCapableTrait;

    /**
     * Provides array normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeArrayCapableTrait;

    /**
     * Provides functionality for creating invalid argument exceptions.
     *
     * @since [*next-version*]
     */
    use CreateInvalidArgumentExceptionCapableTrait;

    /*
     * Provides functionality for creating container exceptions.
     *
     * @since [*next-version*]
     */
    use CreateContainerExceptionCapableTrait;

    /*
     * Provides functionality for creating not-found container exceptions.
     *
     * @since [*next-version*]
     */
    use CreateNotFoundExceptionCapableTrait;

    /**
     * Provides string translating functionality.
     *
     * @since [*next-version*]
     */
    use StringTranslatingTrait;

    public function __construct(PDO $pdo, $table, $fieldColumnMap)
    {
        $this->_setPdo($pdo);
        $this->_setSqlTables([$table]);
        $this->_setSqlFieldColumnMap($fieldColumnMap);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function insert($records)
    {
        return $this->_insert($records);
    }

    /**
     * Retrieves the SQL database table name for use in SQL INSERT queries.
     *
     * @since [*next-version*]
     *
     * @return string|Stringable The table.
     */
    protected function _getSqlInsertTable()
    {
        return $this->_getSqlTables()[0];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getSqlInsertColumnNames()
    {
        return $this->_getSqlColumnNames();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getSqlInsertFieldColumnMap()
    {
        return $this->_getSqlFieldColumnMap();
    }
}
