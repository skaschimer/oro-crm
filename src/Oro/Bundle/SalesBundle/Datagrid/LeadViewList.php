<?php

namespace Oro\Bundle\SalesBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Entity\GridView;
use Oro\Bundle\DataGridBundle\Extension\GridViews\AbstractViewsList;
use Oro\Bundle\FilterBundle\Form\Type\Filter\EnumFilterType;

/**
 * View list for leads.
 */
class LeadViewList extends AbstractViewsList
{
    protected $systemViews =  [
        [
            'name'         => 'lead.open',
            'label'         => 'oro.sales.lead.datagrid.views.open',
            'is_default'    => true,
            'grid_name'     => 'sales-lead-grid',
            'type'          => GridView::TYPE_PUBLIC,
            'filters'       => [
                'status' => [
                    'type'  => EnumFilterType::TYPE_NOT_IN,
                    'value' => ['lead_status.qualified', 'lead_status.canceled']
                ]
            ],
            'sorters'       => [],
            'columns'       => []
        ]
    ];

    #[\Override]
    protected function getViewsList()
    {
        return $this->getSystemViewsList();
    }
}
