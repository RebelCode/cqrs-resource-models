<?php

namespace RebelCode\Storage\Resource\Pdo;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Expression\ExpressionInterface;
use Dhii\Expression\LogicalExpressionInterface;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Output\TemplateInterface;
use Dhii\Storage\Resource\SelectCapableInterface;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use PDO;
use RebelCode\Storage\Resource\Pdo\Query\BuildSelectSqlCapableTrait;
use RebelCode\Storage\Resource\Pdo\Query\BuildSqlJoinsCapableTrait;
use RebelCode\Storage\Resource\Pdo\Query\BuildSqlWhereClauseCapableTrait;
use RebelCode\Storage\Resource\Pdo\Query\EscapeSqlReferenceCapableTrait;
use RebelCode\Storage\Resource\Pdo\Query\ExecutePdoQueryCapableTrait;
use RebelCode\Storage\Resource\Pdo\Query\GetPdoExpressionHashMapCapableTrait;
use RebelCode\Storage\Resource\Pdo\Query\GetPdoValueHashStringCapableTrait;
use RebelCode\Storage\Resource\Pdo\Query\RenderSqlConditionCapableTrait;

/**
 * Concrete implementation of a SELECT resource model for use with a PDO database connection.
 *
 * This generic implementation can be instantiated to SELECT from any number of tables and with any number of JOIN
 * conditions. An optional field-to-column map may be provided which is used to translate consumer-friendly field names
 * to their actual column counterpart names.
 *
 * @since [*next-version*]
 */
class PdoSelectResourceModel implements SelectCapableInterface
{
    /*
     * Provides PDO SQL SELECT functionality.
     *
     * @since [*next-version*]
     */
    use PdoSelectCapableTrait;

    /*
     * Provides SQL SELECT query building functionality.
     *
     * @since [*next-version*]
     */
    use BuildSelectSqlCapableTrait;

    /*
     * Provides SQL JOIN building functionality.
     *
     * @since [*next-version*]
     */
    use BuildSqlJoinsCapableTrait;

    /*
     * Provides SQL WHERE clause building functionality.
     *
     * @since [*next-version*]
     */
    use BuildSqlWhereClauseCapableTrait;

    /*
     * Provides SQL reference escaping functionality.
     *
     * @since [*next-version*]
     */
    use EscapeSqlReferenceCapableTrait;

    /*
     * Provides PDO expression value hash map generation functionality.
     *
     * @since [*next-version*]
     */
    use GetPdoExpressionHashMapCapableTrait;

    /*
     * Provides PDO value hash string generation functionality.
     *
     * @since [*next-version*]
     */
    use GetPdoValueHashStringCapableTrait;

    /*
     * Provides SqL condition rendering functionality (via a template).
     *
     * @since [*next-version*]
     */
    use RenderSqlConditionCapableTrait;

    /*
     * Provides PDO query execution functionality.
     *
     * @since [*next-version*]
     */
    use ExecutePdoQueryCapableTrait;

    /*
     * Provides PDO instance storage functionality.
     *
     * @since [*next-version*]
     */
    use PdoAwareTrait;

    /**
     * Provides string normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeStringCapableTrait;

    /**
     * Provides functionality for creating invalid argument exceptions.
     *
     * @since [*next-version*]
     */
    use CreateInvalidArgumentExceptionCapableTrait;

    /**
     * Provides string translating functionality.
     *
     * @since [*next-version*]
     */
    use StringTranslatingTrait;

    /**
     * The JOIN mode to use.
     *
     * @since [*next-version*]
     */
    const JOIN_MODE = 'LEFT';

    /**
     * The condition template to use for rendering conditions.
     *
     * @since [*next-version*]
     *
     * @var TemplateInterface
     */
    protected $conditionTemplate;

    /**
     * The tables from which to SELECT from.
     *
     * @since [*next-version*]
     *
     * @var string|Stringable[]
     */
    protected $tables;

    /**
     * A map of field names to table column names.
     *
     * @since [*next-version*]
     *
     * @var string[]|Stringable[]
     */
    protected $fieldColumnMap;

    /**
     * A list of JOIN expressions to use in SELECT queries.
     *
     * @since [*next-version*]
     *
     * @var LogicalExpressionInterface[]
     */
    protected $joins;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param PDO                          $pdo               The PDO instance to use to prepare and execute queries.
     * @param TemplateInterface            $conditionTemplate The template for rendering conditions.
     * @param string[]|Stringable[]        $tables            The tables from which to SELECT from.
     * @param string[]|Stringable[]        $fieldColumnMap    A map of field names to table column names.
     * @param LogicalExpressionInterface[] $joins             A list of JOIN expressions to use in SELECT queries.
     */
    public function __construct(PDO $pdo, TemplateInterface $conditionTemplate, $tables, $fieldColumnMap, $joins = [])
    {
        $this->_setPdo($pdo);
        $this->conditionTemplate = $conditionTemplate;
        $this->tables = $tables;
        $this->fieldColumnMap = $fieldColumnMap;
        $this->joins = $joins;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function select(LogicalExpressionInterface $condition = null)
    {
        return $this->_select($condition);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getSqlSelectTables()
    {
        return $this->tables;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getSqlSelectColumns()
    {
        return array_values($this->fieldColumnMap);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getSqlSelectFieldNames()
    {
        return array_keys($this->fieldColumnMap);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getSqlSelectJoinConditions()
    {
        return $this->joins;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getSqlJoinType(ExpressionInterface $expression)
    {
        return 'INNER';
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getSqlConditionTemplate(LogicalExpressionInterface $condition)
    {
        return $this->conditionTemplate;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getSqlFieldColumnMap()
    {
        return $this->fieldColumnMap;
    }
}
