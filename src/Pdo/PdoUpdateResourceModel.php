<?php

namespace RebelCode\Storage\Resource\Pdo;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Expression\TermInterface;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Iterator\CountIterableCapableTrait;
use Dhii\Iterator\ResolveIteratorCapableTrait;
use Dhii\Output\TemplateInterface;
use Dhii\Storage\Resource\UpdateCapableInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeIntCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use PDO;
use RebelCode\Storage\Resource\Pdo\Query\BuildSqlWhereClauseCapableTrait;
use RebelCode\Storage\Resource\Pdo\Query\BuildUpdateSqlCapableTrait;
use RebelCode\Storage\Resource\Pdo\Query\EscapeSqlReferenceCapableTrait;
use RebelCode\Storage\Resource\Pdo\Query\GetPdoExpressionHashMapCapableTrait;
use RebelCode\Storage\Resource\Pdo\Query\GetPdoValueHashStringCapableTrait;
use RebelCode\Storage\Resource\Pdo\Query\RenderSqlExpressionCapableTrait;

/**
 * PdoUpdateResourceModel.
 *
 * @since [*next-version*]
 */
class PdoUpdateResourceModel extends AbstractPdoResourceModel implements UpdateCapableInterface
{
    /*
     * Provides PDO SQL UPDATE functionality.
     *
     * @since [*next-version*]
     */
    use PdoUpdateCapableTrait;

    /*
     * Provides SQL UPDATE query building functionality.
     *
     * @since [*next-version*]
     */
    use BuildUpdateSqlCapableTrait;

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
    use RenderSqlExpressionCapableTrait;

    /*
     * Provides SQL table name storage functionality.
     *
     * @since [*next-version*]
     */
    use SqlTableAwareTrait;

    /*
     * Provides SQL field-to-column map storage functionality.
     *
     * @since [*next-version*]
     */
    use SqlFieldColumnMapAwareTrait;

    /*
     * Provides SQL expression template storage functionality.
     *
     * @since [*next-version*]
     */
    use SqlExpressionTemplateAwareTrait;

    /*
     * Provides string normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeStringCapableTrait;

    /*
     * Provides array normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeArrayCapableTrait;

    /*
     * Provides integer normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeIntCapableTrait;

    /*
     * Provides functionality for counting iterable variables.
     *
     * @since [*next-version*]
     */
    use CountIterableCapableTrait;

    /*
     * Provides iterator resolution functionality.
     *
     * @since [*next-version*]
     */
    use ResolveIteratorCapableTrait;

    /*
     * Provides functionality for creating invalid argument exceptions.
     *
     * @since [*next-version*]
     */
    use CreateInvalidArgumentExceptionCapableTrait;

    /*
     * Provides functionality for creating out of range exceptions.
     *
     * @since [*next-version*]
     */
    use CreateOutOfRangeExceptionCapableTrait;

    /*
     * Provides string translating functionality.
     *
     * @since [*next-version*]
     */
    use StringTranslatingTrait;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param PDO                   $pdo                The PDO instance to use to prepare and execute queries.
     * @param TemplateInterface     $expressionTemplate The template for rendering SQL expressions.
     * @param string|Stringable     $table              The name of the table for which records will be updated.
     * @param string[]|Stringable[] $fieldColumnMap     A map of field names to table column names.
     */
    public function __construct(PDO $pdo, TemplateInterface $expressionTemplate, $table, $fieldColumnMap)
    {
        $this->_setPdo($pdo);
        $this->_setSqlExpressionTemplate($expressionTemplate);
        $this->_setSqlTable($table);
        $this->_setSqlFieldColumnMap($fieldColumnMap);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function update($changeSet, LogicalExpressionInterface $condition = null)
    {
        return $this->_update($changeSet, $condition);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getSqlUpdateTable()
    {
        return $this->_getSqlTable();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getSqlUpdateFieldColumnMap()
    {
        return $this->_getSqlFieldColumnMap();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _renderSqlCondition(LogicalExpressionInterface $condition, array $valueHashMap = [])
    {
        return $this->_renderSqlExpression($condition, $valueHashMap);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getTemplateForSqlExpression(TermInterface $expression)
    {
        return $this->_getSqlExpressionTemplate();
    }
}
