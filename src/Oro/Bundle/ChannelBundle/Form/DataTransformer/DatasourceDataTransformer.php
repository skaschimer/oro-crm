<?php

namespace Oro\Bundle\ChannelBundle\Form\DataTransformer;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Form\Type\ChannelType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;

class DatasourceDataTransformer implements DataTransformerInterface
{
    /** @var FormFactoryInterface */
    protected $formFactory;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    #[\Override]
    public function transform($value)
    {
        if (null === $value || (!$value instanceof Integration)) {
            return null;
        }

        /** @var Integration $value */
        return [
            'type'       => $value->getType(),
            'data'       => null,
            'identifier' => $value,
            'name'       => $value->getName()
        ];
    }

    #[\Override]
    public function reverseTransform($value)
    {
        if (null === $value) {
            return null;
        } elseif (!is_array($value)) {
            throw new UnexpectedTypeException($value, 'array');
        }

        $data        = $value['data'];
        $integration = $value['identifier'] ? $value['identifier'] : (!empty($data) ? new Integration() : null);

        $form = $this->formFactory->create(
            ChannelType::class,
            $integration,
            ['csrf_protection' => false, 'disable_customer_datasource_types' => false]
        );

        if (!empty($data)) {
            $form->submit($data);

            if (!$form->isValid()) {
                $errorMessages = array_map(
                    function (FormError $error) {
                        return $error->getMessage();
                    },
                    $form->getErrors()
                );

                throw new \LogicException(sprintf('Malware data received. Errors: %s', implode(', ', $errorMessages)));
            }
        }

        return $integration;
    }
}
