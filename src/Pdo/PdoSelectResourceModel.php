<?php

namespace RebelCode\Storage\Resource\Pdo;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Expression\ExpressionInterface;
use Dhii\Expression\LogicalExpressionInterface;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Output\TemplateInterface;
use Dhii\Storage\Resource\SelectCapableInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use PDO;
use RebelCode\Storage\Resource\Pdo\Query\BuildSelectSqlCapableTrait;
use RebelCode\Storage\Resource\Pdo\Query\BuildSqlJoinsCapableTrait;
use RebelCode\Storage\Resource\Pdo\Query\BuildSqlWhereClauseCapableTrait;
use RebelCode\Storage\Resource\Pdo\Query\EscapeSqlReferenceCapableTrait;
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
 * This implementation is also dependent on only a single template for rendering SQL conditions. The template instance
 * must be able to render any condition type. A delegate template is recommended.
 *
 * @since [*next-version*]
 */
class PdoSelectResourceModel extends AbstractPdoResourceModel implements SelectCapableInterface
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
     * Provides SQL table list storage functionality.
     *
     * @since [*next-version*]
     */
    use SqlTableListAwareTrait;

    /*
     * Provides SQL field-to-column map storage functionality.
     *
     * @since [*next-version*]
     */
    use SqlFieldColumnMapAwareTrait;

    /*
     * Provides SQL field name list storage functionality.
     *
     * @since [*next-version*]
     */
    use SqlFieldNamesAwareTrait;

    /*
     * Provides SQL column name list storage functionality.
     *
     * @since [*next-version*]
     */
    use SqlColumnNamesAwareTrait;

    /*
     * Provides SQL join condition list storage functionality.
     *
     * @since [*next-version*]
     */
    use SqlJoinConditionsAwareTrait;

    /*
     * Provides SQL condition template storage functionality.
     *
     * @since [*next-version*]
     */
    use SqlConditionTemplateAwareTrait;

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
        $this->_setSqlConditionTemplate($conditionTemplate);
        $this->_setSqlTableList($tables);
        $this->_setSqlFieldColumnMap($fieldColumnMap);
        $this->_setSqlJoinConditions($joins);
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
        return $this->_getSqlTableList();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getTemplateForSqlCondition(LogicalExpressionInterface $condition)
    {
        return $this->_getSqlConditionTemplate();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getSqlSelectFieldNames()
    {
        return $this->_getSqlFieldNames();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getSqlSelectColumns()
    {
        return $this->_getSqlColumnNames();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getSqlSelectJoinConditions()
    {
        return $this->_getSqlJoinConditions();
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
}
