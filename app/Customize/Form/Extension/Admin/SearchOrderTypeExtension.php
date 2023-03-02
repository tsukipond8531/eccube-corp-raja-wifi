<?php

declare(strict_types=1);

namespace Customize\Form\Extension\Admin;

use Eccube\Common\EccubeConfig;
use Eccube\Form\Type\Admin\SearchOrderType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;

class SearchOrderTypeExtension extends AbstractTypeExtension
{
    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * SearchOrderType constructor.
     *
     * @param EccubeConfig $eccubeConfig
     */
    public function __construct(
      EccubeConfig $eccubeConfig
    ) {
        $this->eccubeConfig = $eccubeConfig;
    }

    public function getExtendedType(): string
    {
        return SearchOrderType::class;
    }

    public function getExtendedTypes(): iterable
    {
        return [SearchOrderType::class];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
      $builder
        ->add('rental_start', DateType::class, [
            'label' => '利用開始日',
            'required' => false,
            'input' => 'datetime',
            'widget' => 'single_text',
            'format' => 'yyyy-MM-dd',
            'attr' => [
                'class' => 'datepicker-input',
                'data-target' => '#'.$this->getBlockPrefix().'_rental_start',
                'data-toggle' => 'datepicker',
            ],
        ])
        ->add('rental_end', DateType::class, [
            'label' => '終了予定日',
            'required' => false,
            'input' => 'datetime',
            'widget' => 'single_text',
            'format' => 'yyyy-MM-dd',
            'attr' => [
                'class' => 'datepicker-input',
                'data-target' => '#'.$this->getBlockPrefix().'_rental_end',
                'data-toggle' => 'datepicker',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'admin_search_order';
    }
}