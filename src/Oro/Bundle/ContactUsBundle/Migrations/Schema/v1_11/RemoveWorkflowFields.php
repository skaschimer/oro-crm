<?php

namespace Oro\Bundle\ContactUsBundle\Migrations\Schema\v1_11;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\WorkflowBundle\Migrations\Schema\RemoveWorkflowFieldsTrait;

class RemoveWorkflowFields implements Migration
{
    use RemoveWorkflowFieldsTrait;

    #[\Override]
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->removeWorkflowFields($schema->getTable('orocrm_contactus_request'));
        $this->removeConfigsForWorkflowFields('Oro\Bundle\ContactUsBundle\Entity\ContactRequest', $queries);
    }
}
