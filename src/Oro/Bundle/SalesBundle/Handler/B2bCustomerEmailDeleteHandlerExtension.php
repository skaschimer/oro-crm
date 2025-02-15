<?php

namespace Oro\Bundle\SalesBundle\Handler;

use Oro\Bundle\EntityBundle\Handler\AbstractEntityDeleteHandlerExtension;
use Oro\Bundle\SalesBundle\Entity\B2bCustomerEmail;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The delete handler extension for B2bCustomerEmail entity.
 */
class B2bCustomerEmailDeleteHandlerExtension extends AbstractEntityDeleteHandlerExtension
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        TranslatorInterface $translator
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->translator = $translator;
    }

    #[\Override]
    public function assertDeleteGranted($entity): void
    {
        /** @var B2bCustomerEmail $entity */

        $customer = $entity->getOwner();
        if (null === $customer) {
            return;
        }

        if (!$this->authorizationChecker->isGranted('EDIT', $customer)) {
            throw $this->createAccessDeniedException();
        }

        if ($entity->isPrimary() && $customer->getEmails()->count() !== 1) {
            throw $this->createAccessDeniedException(
                $this->translator->trans('oro.sales.validation.b2bcustomer.emails.delete.more_one', [], 'validators')
            );
        }
    }
}
