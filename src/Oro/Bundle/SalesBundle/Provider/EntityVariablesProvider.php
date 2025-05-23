<?php

namespace Oro\Bundle\SalesBundle\Provider;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\EntityBundle\Twig\Sandbox\EntityVariablesProviderInterface;
use Oro\Bundle\EntityExtendBundle\Extend\RelationType;
use Oro\Bundle\SalesBundle\Entity\Customer;

/**
 * Adds the account variables to the email template.
 */
class EntityVariablesProvider implements EntityVariablesProviderInterface
{
    #[\Override]
    public function getVariableGetters(): array
    {
        return [
            Customer::class => ['account' => 'getAccount']
        ];
    }

    #[\Override]
    public function getVariableDefinitions(): array
    {
        return [
            Customer::class => [
                'account' => [
                    'type' => RelationType::TO_ONE,
                    'label' => 'Account',
                    'related_entity_name' => Account::class
                ]
            ]
        ];
    }

    #[\Override]
    public function getVariableProcessors(string $entityClass): array
    {
        return [];
    }
}
