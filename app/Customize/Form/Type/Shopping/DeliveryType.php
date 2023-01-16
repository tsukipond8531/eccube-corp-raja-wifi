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
use Customize\Entity\DeliveryItem;
use Eccube\Entity\OrderItem;
use Eccube\Entity\Shipping;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Eccube\Form\Type\KanaType;
use Eccube\Form\Type\NameType;
use Eccube\Form\Type\AddressType;
use Eccube\Form\Type\PhoneNumberType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Eccube\Form\Type\PostalType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Eccube\Form\Type\Front\CustomerAddressType;
use Eccube\Form\Type\Front\ShoppingShippingType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;
use Eccube\Common\EccubeConfig;

class DeliveryType extends AbstractType
{

    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * @param EccubeConfig $eccubeConfig
     */
    public function __construct(EccubeConfig $eccubeConfig)
    {
        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('shipping_method', RadioType::class)
            ->add('address_choice', RadioType::class)
            // ->add('Shippings', CustomerAddressType::class, [
            //     // 'data_class' => CustomerAddressType::class,
            //     // 'by_reference' => false,
            //     ]);
            ->add('name', NameType::class, [
                'required' => false,
            ])
            ->add('kana', KanaType::class, [
                'required' => false,
            ])
            ->add('company_name', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                ],
            ])
            ->add('postal_code', PostalType::class, [
                'required' => false,
            ])
            ->add('address', AddressType::class, [
                'required' => false,
            ])
            ->add('phone_number', PhoneNumberType::class, [
                'required' => false,
            ]);

        // // お届け時間のプルダウンを生成
        // $builder->addEventListener(
        //     FormEvents::PRE_SET_DATA,
        //     function (FormEvent $event) {
        //         /** @var Shipping $Shipping */
        //         $Shipping = $event->getData();
        //         if (is_null($Shipping)) {
        //             return;
        //         }

        //         $ShippingDeliveryTime = null;
        //         $DeliveryTimes = [];
        //         $Delivery = $Shipping->getDelivery();
        //         if ($Delivery) {
        //             $DeliveryTimes = $Delivery->getDeliveryTimes();
        //             $DeliveryTimes = $DeliveryTimes->filter(function (DeliveryTime $DeliveryTime) {
        //                 return $DeliveryTime->isVisible();
        //             });

        //             foreach ($DeliveryTimes as $deliveryTime) {
        //                 if ($deliveryTime->getId() == $Shipping->getTimeId()) {
        //                     $ShippingDeliveryTime = $deliveryTime;
        //                     break;
        //                 }
        //             }
        //         }

        //         $form = $event->getForm();
        //         $form->add(
        //             'DeliveryTime',
        //             EntityType::class,
        //             [
        //                 'label' => 'front.shopping.delivery_time',
        //                 'class' => 'Eccube\Entity\DeliveryTime',
        //                 'choice_label' => 'deliveryTime',
        //                 'choices' => $DeliveryTimes,
        //                 'required' => false,
        //                 'placeholder' => 'common.select__unspecified',
        //                 'mapped' => false,
        //                 'data' => $ShippingDeliveryTime,
        //             ]
        //         );
        //     }
        // );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DeliveryItem::class,
        ]);
    }
}
