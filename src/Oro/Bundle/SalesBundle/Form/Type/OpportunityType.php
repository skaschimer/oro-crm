<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Oro\Bundle\ContactBundle\Form\Type\ContactSelectType;
use Oro\Bundle\CurrencyBundle\Form\Type\MultiCurrencyType;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOptionInterface;
use Oro\Bundle\EntityExtendBundle\Form\Util\EnumTypeHelper;
use Oro\Bundle\EntityExtendBundle\Provider\EnumOptionsProvider;
use Oro\Bundle\FormBundle\Form\Type\OroDateType;
use Oro\Bundle\FormBundle\Form\Type\OroPercentType;
use Oro\Bundle\FormBundle\Form\Type\OroResizeableRichTextType;
use Oro\Bundle\SalesBundle\Builder\OpportunityRelationsBuilder;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Entity\OpportunityCloseReason;
use Oro\Bundle\SalesBundle\Provider\ProbabilityProvider;
use Oro\Bundle\TranslationBundle\Form\Type\TranslatableEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * The form type for Opportunity entity.
 */
class OpportunityType extends AbstractType
{
    const NAME = 'oro_sales_opportunity';

    /** @var ProbabilityProvider */
    protected $probabilityProvider;

    /** @var EnumOptionsProvider */
    protected $enumOptionsProvider;

    /** @var EnumTypeHelper */
    protected $typeHelper;

    /** @var OpportunityRelationsBuilder */
    protected $relationsBuilder;

    public function __construct(
        ProbabilityProvider $probabilityProvider,
        EnumOptionsProvider $enumOptionsProvider,
        EnumTypeHelper $typeHelper,
        OpportunityRelationsBuilder $relationsBuilder
    ) {
        $this->probabilityProvider = $probabilityProvider;
        $this->enumOptionsProvider   = $enumOptionsProvider;
        $this->typeHelper          = $typeHelper;
        $this->relationsBuilder    = $relationsBuilder;
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'closeReason',
                TranslatableEntityType::class,
                [
                    'label'        => 'oro.sales.opportunity.close_reason.label',
                    'class'        => OpportunityCloseReason::class,
                    'choice_label' => 'label',
                    'required'     => false,
                    'disabled'     => false,
                    'placeholder'  => 'oro.sales.form.choose_close_rsn'
                ]
            )
            ->add(
                'contact',
                ContactSelectType::class,
                [
                    'required'               => false,
                    'label'                  => 'oro.sales.opportunity.contact.label',
                    'new_item_property_name' => 'firstName',
                    'configs'                => [
                        'allowCreateNew'          => true,
                        'renderedPropertyName'    => 'fullName',
                        'placeholder'             => 'oro.contact.form.choose_contact',
                        'result_template_twig'    => '@OroForm/Autocomplete/fullName/result.html.twig',
                        'selection_template_twig' => '@OroForm/Autocomplete/fullName/selection.html.twig'
                    ]
                ]
            )
            ->add(
                'customerAssociation',
                CustomerType::class,
                [
                    'required' => true,
                    'label'    => 'oro.sales.opportunity.customer.label',
                    'parent_class' => $options['data_class'],
                    'constraints' => [new NotBlank()],
                    'error_bubbling' => false,
                ]
            )
            ->add('name', TextType::class, ['required' => true, 'label' => 'oro.sales.opportunity.name.label'])
            ->add(
                'closeDate',
                OroDateType::class,
                ['required' => false, 'label' => 'oro.sales.opportunity.close_date.label']
            )
            ->add(
                'probability',
                OroPercentType::class,
                ['required' => false, 'label' => 'oro.sales.opportunity.probability.label']
            )
            ->add(
                'budgetAmount',
                MultiCurrencyType::class,
                [
                    'required' => false,
                    'label' => 'oro.sales.opportunity.budget_amount.label',
                    'currency_empty_value' => false,
                    'full_currency_list' => false,
                    'attr' => ['class' => 'currency-price'],
                    'error_bubbling' => false,
                ]
            )
            ->add(
                'closeRevenue',
                MultiCurrencyType::class,
                [
                    'required' => false,
                    'label' => 'oro.sales.opportunity.close_revenue.label',
                    'currency_empty_value' => false,
                    'full_currency_list' => false,
                    'error_bubbling' => false,
                ]
            )
            ->add(
                'customerNeed',
                OroResizeableRichTextType::class,
                ['required' => false, 'label' => 'oro.sales.opportunity.customer_need.label']
            )
            ->add(
                'proposedSolution',
                OroResizeableRichTextType::class,
                ['required' => false, 'label' => 'oro.sales.opportunity.proposed_solution.label']
            )
            ->add(
                'notes',
                OroResizeableRichTextType::class,
                ['required' => false, 'label' => 'oro.sales.opportunity.notes.label']
            )
            ->add(
                'status',
                OpportunityStatusSelectType::class,
                [
                    'required'    => true,
                    'label'       => 'oro.sales.opportunity.status.label',
                    'enum_code'   => Opportunity::INTERNAL_STATUS_CODE,
                    'constraints' => [new NotNull()]
                ]
            );

        $this->addListeners($builder);
    }

    /**
     * Set new opportunities default probability based on default enum status value
     */
    public function onFormPreSetData(FormEvent $event)
    {
        $opportunity = $event->getData();
        if (null === $opportunity) {
            return;
        }

        if ($opportunity->getId()) {
            return;
        }

        if (null !== $opportunity->getProbability()) {
            return;
        }

        $status = $opportunity->getStatus();

        if (!$status) {
            $status = $this->getDefaultStatus();
        }

        if (!$status) {
            return;
        }

        $opportunity->setProbability($this->probabilityProvider->get($status));
        $event->setData($opportunity);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Opportunity::class,
                'csrf_token_id' => 'opportunity'
            ]
        );
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }

    protected function addListeners(FormBuilderInterface $builder)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onFormPreSetData']);

        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                $this->relationsBuilder->buildAll($event->getData());
            }
        );
    }

    /**
     * Return default enum value for Opportunity Status
     *
     * @return EnumOptionInterface|null Return null if there is no default status
     */
    private function getDefaultStatus()
    {
        return $this->enumOptionsProvider->getDefaultEnumOptionByCode(
            $this->typeHelper->getEnumCode(Opportunity::class, 'status')
        );
    }
}
