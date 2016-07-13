<?php

namespace OroCRM\Bundle\ReportBundle\EventListener\Datagrid;

use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractDateFilterType;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

class OpportunitiesByStatusReportListener
{
    /**
     * @var array Map of date filters and comparison operators
     */
    public static $comparatorsMap = [
        AbstractDateFilterType::TYPE_LESS_THAN => '<',
        AbstractDateFilterType::TYPE_MORE_THAN => '>',
        AbstractDateFilterType::TYPE_EQUAL => '=',
        AbstractDateFilterType::TYPE_NOT_EQUAL => '<>',
        AbstractDateFilterType::TYPE_BETWEEN => ['>=', 'AND', '<='],
        AbstractDateFilterType::TYPE_NOT_BETWEEN => ['<=', 'OR', '>='],
    ];

    public static $joinFilterKeys = ['createdAt', 'updatedAt', 'closeDate'];

    /**
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $className = ExtendHelper::buildEnumValueClassName(Opportunity::INTERNAL_STATUS_CODE);
        $config = $event->getConfig();
        $from[] = [
            'table' => $className,
            'alias' => 'status'
        ];
        $config->offsetSetByPath('[source][query][from]', $from);
    }

    /**
     * Move the date filters into join clause to avoid filtering statuses from the report
     *
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $dataGrid = $event->getDatagrid();
        $dataSource = $dataGrid->getDatasource();
        if (!$dataSource instanceof OrmDatasource) {
            return;
        }

        $joinCondition = '';
        $filters = $dataGrid->getParameters()->get('_filter');
        if (!$filters) {
            return;
        }

        $filtersConfig = $dataGrid->getConfig()->offsetGetByPath('[filters][columns]');

        foreach ($filtersConfig as $key => $config) {
            // get date and datetime filters only
            if (in_array($config['type'], ['date', 'datetime']) && array_key_exists($key, $filters)) {
                // build a join clause
                $joinCondition .= $this->buildDateCondition($filters[$key], $config['data_name']);
                // remove filters so it does not appear in the where clause
                unset($filters[$key]);
            }
        }

        // update filter params (without removed ones)
        $dataGrid->getParameters()->set('_filter', $filters);

        // Prepare new join
        $queryBuilder = $dataSource->getQueryBuilder();
        $rootAlias = $queryBuilder->getRootAliases()[0];
        $joinParts = $queryBuilder->getDQLPart('join');

        /** @var \Doctrine\ORM\Query\Expr\Join $joinPart */
        $joinPart = $joinParts[$rootAlias][0];

        // Append $joinCondition to the join part of the query
        $queryBuilder->resetDQLPart('join');
        $queryBuilder->leftJoin(
            $joinPart->getJoin(),
            $joinPart->getAlias(),
            $joinPart->getConditionType(),
            $joinPart->getCondition() . $joinCondition,
            $joinPart->getIndexBy()
        );
    }

    /**
     * Generates SQL date comparison string depending on filter $options
     *
     * @param array $options Filter options
     * @param string $fieldName
     *
     * @return string
     */
    protected function buildDateCondition(array $options, $fieldName)
    {
        $type = $options['type'];

        if (!array_key_exists($type, self::$comparatorsMap)) {
            return '';
        }

        $comparator = self::$comparatorsMap[$type];

        // date range comparison
        if (is_array($comparator)) {
            return sprintf(
                ' AND (%s %s %s)',
                $this->formatComparison($fieldName, $comparator[0], $options['value']['start']),
                $comparator[1],
                $this->formatComparison($fieldName, $comparator[2], $options['value']['end'])
            );
        }

        // simple date comparison
        return $this->formatComparison($fieldName, $comparator, $options['value']['start']);
    }

    /**
     * Generates a comparison string
     *
     * @param string $fieldName
     * @param string $operator
     * @param string $value
     *
     * @return string
     */
    protected function formatComparison($fieldName, $operator, $value)
    {
        return sprintf('%s %s \'%s\'', $fieldName, $operator, $value);
    }
}
