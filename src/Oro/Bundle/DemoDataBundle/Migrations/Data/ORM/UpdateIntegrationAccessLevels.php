<?php

namespace Oro\Bundle\DemoDataBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\DistributionBundle\Handler\ApplicationState;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\SecurityBundle\Migrations\Data\ORM\AbstractUpdatePermissions;

/**
 * Updates permissions for Channel entity for the following roles:
 * * ROLE_USER
 * * ROLE_ONLINE_SALES_REP
 * * ROLE_LEADS_DEVELOPMENT_REP
 */
class UpdateIntegrationAccessLevels extends AbstractUpdatePermissions implements DependentFixtureInterface
{
    #[\Override]
    public function getDependencies(): array
    {
        return [LoadRolesData::class];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        if (!$this->container->get(ApplicationState::class)->isInstalled()) {
            return;
        }

        $aclManager = $this->getAclManager();
        if (!$aclManager->isAclEnabled()) {
            return;
        }

        $roleNames = [
            'ROLE_USER',
            'ROLE_ONLINE_SALES_REP',
            'ROLE_LEADS_DEVELOPMENT_REP',
        ];
        $permissions = ['VIEW_SYSTEM'];
        foreach ($roleNames as $roleName) {
            $this->setEntityPermissions(
                $aclManager,
                $this->getRole($manager, $roleName),
                Channel::class,
                $permissions
            );
        }
        $aclManager->flush();
    }
}
