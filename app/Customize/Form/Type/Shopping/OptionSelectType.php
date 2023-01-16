<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Customize\Form\Type\Shopping;

use Symfony\Component\Form\AbstractType;
use Customize\Entity\OptionItem;
use Eccube\Entity\OrderItem;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class OptionSelectType extends AbstractType
{
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('optionItem', TextType::class)
            ->add('product_1', TextType::class)
            ->add('product_2', TextType::class)
            ->add('from', TextType::class)
            ->add('to', TextType::class)
            ->add('des_price', TextType::class)
            ->add('country_name_jp', TextType::class)
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OptionItem::class,
        ]);
    }
}
