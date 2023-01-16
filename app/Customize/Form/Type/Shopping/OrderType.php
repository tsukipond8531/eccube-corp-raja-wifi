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

use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Entity\Delivery;
use Eccube\Entity\Order;
use Eccube\Entity\Payment;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\DeliveryRepository;
use Eccube\Repository\OrderRepository;
use Eccube\Repository\PaymentRepository;
use Eccube\Request\Context;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

use Eccube\Common\EccubeConfig;
use Eccube\Form\Type\AddressType;
use Eccube\Form\Type\KanaType;
use Eccube\Form\Type\NameType;
use Eccube\Form\Type\PhoneNumberType;
use Eccube\Form\Type\PostalType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Eccube\Form\Type\Shopping\ShippingType;
use Symfony\Component\Validator\Constraints as Assert;

use Plugin\Stripe4\Service\Method\CreditCard;
use Eccube\Form\Type\ToggleSwitchType;
use Stripe\PaymentMethod;
use Stripe\Stripe;

class OrderType extends AbstractType
{
    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var DeliveryRepository
     */
    protected $deliveryRepository;

    /**
     * @var PaymentRepository
     */
    protected $paymentRepository;

    /**
     * @var BaseInfoRepository
     */
    protected $baseInfoRepository;

    /**
     * @var Context
     */
    protected $requestContext;

    // custorm part start

    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    // custorm part end

    /**
     * OrderType constructor.
     *
     * @param OrderRepository $orderRepository
     * @param DeliveryRepository $deliveryRepository
     * @param PaymentRepository $paymentRepository
     * @param BaseInfoRepository $baseInfoRepository
     * @param Context $requestContext
     * @param EccubeConfig $eccubeConfig
     */
    public function __construct(
        OrderRepository $orderRepository,
        DeliveryRepository $deliveryRepository,
        PaymentRepository $paymentRepository,
        BaseInfoRepository $baseInfoRepository,
        Context $requestContext,
        // custorm start
        EccubeConfig $eccubeConfig
    ) {
        $this->orderRepository = $orderRepository;
        $this->deliveryRepository = $deliveryRepository;
        $this->paymentRepository = $paymentRepository;
        $this->baseInfoRepository = $baseInfoRepository;
        $this->requestContext = $requestContext;
        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // ShoppingController::checkoutから呼ばれる場合は, フォーム項目の定義をスキップする.
        if ($options['skip_add_form']) {
            return;
        }

        $builder->add('message', TextareaType::class, [
            'required' => false,
            'constraints' => [
                new Length(['min' => 0, 'max' => 3000]),
            ],
        ])->add('Shippings', CollectionType::class, [
            'entry_type' => ShippingType::class,
            'by_reference' => false,
        ])->add('redirect_to', HiddenType::class, [
            'mapped' => false,
        ]);

        // custorm start
        $builder
            ->add('name', NameType::class, [
                'required' => true,
            ])
            ->add('kana', KanaType::class, [
                'required' => true,
            ])
            ->add('company_name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                ],
            ])
            ->add('postal_code', PostalType::class, [
                'required' => true,
            ])
            ->add('address', AddressType::class, [
                'required' => true,
            ])
            ->add('phone_number', PhoneNumberType::class, [
                'required' => true,
            ]);

        $builder
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
                /** @var FormInterface $form */
                $form = $event->getForm();
                /** @var Order $order */
                $order = $event->getData();
				// dump($order->getPayment()); die();
                // if ($order->getPayment()->getMethodClass() === CreditCard::class) {
                    $form
                        ->add('stripe_payment_method_id', HiddenType::class, [
                            'error_bubbling' => false
                        ]);

                    if ($customer = $order->getCustomer()) {
                        $form
                            ->add('stripe_save_card', ToggleSwitchType::class, [
                                'mapped' => true,
                                'label' => 'カード情報を保存する'
                            ]);

                        $cards = new ArrayCollection();
                        if ($customer->hasStripeCustomerId()) {
                            $cards = PaymentMethod::all([
                                'customer' => $customer->getStripeCustomerId(),
                                'type' => 'card'
                            ]);
                            $cards = new ArrayCollection($cards->data);
                        }

                        $form
                            ->add('cards', ChoiceType::class, [
                                'mapped' => false,
                                'choices' => $cards,
                                'multiple' => false,
                                'expanded' => true,
                                'required' => false,
                                'placeholder' => false,
                                'choice_label' => function (PaymentMethod $paymentMethod) {
                                    return $paymentMethod->card->brand . ' •••• ' . $paymentMethod->card->last4;
                                },
                                'choice_value' => function (?PaymentMethod $paymentMethod) {
                                    return $paymentMethod ? $paymentMethod->id : '';
                                },
                            ]);
                    }
                // }
            });

        $builder
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {

                /** @var FormInterface $form */
                $form = $event->getForm();
                /** @var Order $order */
                $order = $event->getData();

                if ($order->getPayment()->getMethodClass() === CreditCard::class) {
                    if ($form->get('redirect_to')->getData()) {
                        return;
                    }

                    if ($form->has('stripe_payment_method_id')) {
                        if (null === $order->getStripePaymentMethodId()) {
                            $form->get('stripe_payment_method_id')->addError(new FormError(trans('クレジットカード情報を入力してください。')));
                        }
                    }
                }
            });
        // custorm end

        if ($this->baseInfoRepository->get()->isOptionPoint() && $this->requestContext->getCurrentUser()) {
            $builder->add('use_point', IntegerType::class, [
                'required' => false,
                'constraints' => [
                    new NotBlank(),
                    new Regex([
                        'pattern' => "/^\d+$/u",
                        'message' => 'form_error.numeric_only',
                    ]),
                    new Length(['max' => 11]),
                ],
            ]);
        }

        // 支払い方法のプルダウンを生成
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            /** @var Order $Order */
            $Order = $event->getData();
            if (null === $Order || !$Order->getId()) {
                return;
            }

            $Deliveries = $this->getDeliveries($Order);
            $Payments = $this->getPayments($Deliveries);
            // @see https://github.com/EC-CUBE/ec-cube/issues/4881
            $charge = $Order->getPayment() ? $Order->getPayment()->getCharge() : 0;
            $Payments = $this->filterPayments($Payments, $Order->getPaymentTotal() - $charge);

            $form = $event->getForm();
            $this->addPaymentForm($form, $Payments, $Order->getPayment());
        });

        // 支払い方法のプルダウンを生成(Submit時)
        // 配送方法の選択によって使用できる支払い方法がかわるため, フォームを再生成する.
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            /** @var Order $Order */
            $Order = $event->getForm()->getData();
            $data = $event->getData();

            $Deliveries = [];
            if (!empty($data['Shippings'])) {
                foreach ($data['Shippings'] as $Shipping) {
                    if (!empty($Shipping['Delivery'])) {
                        $Delivery = $this->deliveryRepository->find($Shipping['Delivery']);
                        if ($Delivery) {
                            $Deliveries[] = $Delivery;
                        }
                    }
                }
            }

            $Payments = $this->getPayments($Deliveries);
            // @see https://github.com/EC-CUBE/ec-cube/issues/4881
            $charge = $Order->getPayment() ? $Order->getPayment()->getCharge() : 0;
            $Payments = $this->filterPayments($Payments, $Order->getPaymentTotal() - $charge);

            $form = $event->getForm();
            $this->addPaymentForm($form, $Payments);
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var Order $Order */
            $Order = $event->getData();
            $Payment = $Order->getPayment();
            if ($Payment && $Payment->getMethod()) {
                $Order->setPaymentMethod($Payment->getMethod());
            }
        });


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Eccube\Entity\Order',
                'skip_add_form' => false,
            ]
        );
    }

    public function getBlockPrefix()
    {
        return '_shopping_order';
    }

    private function addPaymentForm(FormInterface $form, array $choices, Payment $data = null)
    {
        $message = trans('front.shopping.payment_method_unselected');

        if (empty($choices)) {
            $message = trans('front.shopping.payment_method_not_fount');
        }

        $form->add('Payment', EntityType::class, [
            'class' => Payment::class,
            'choice_label' => 'method',
            'expanded' => true,
            'multiple' => false,
            'placeholder' => false,
            'constraints' => [
                new NotBlank(['message' => $message]),
            ],
            'choices' => $choices,
            'data' => $data,
            'invalid_message' => $message,
        ]);
    }

    /**
     * 出荷に紐づく配送方法を取得する.
     *
     * @param Order $Order
     *
     * @return Delivery[]
     */
    private function getDeliveries(Order $Order)
    {
        $Deliveries = [];
        foreach ($Order->getShippings() as $Shipping) {
            $Delivery = $Shipping->getDelivery();
            if ($Delivery->isVisible()) {
                $Deliveries[] = $Shipping->getDelivery();
            }
        }

        return array_unique($Deliveries);
    }

    /**
     * 配送方法に紐づく支払い方法を取得する
     * 各配送方法に共通する支払い方法のみ返す.
     *
     * @param Delivery[] $Deliveries
     *
     * @return ArrayCollection
     */
    private function getPayments($Deliveries)
    {
        $PaymentsByDeliveries = [];
        foreach ($Deliveries as $Delivery) {
            $PaymentOptions = $Delivery->getPaymentOptions();
            foreach ($PaymentOptions as $PaymentOption) {
                /** @var Payment $Payment */
                $Payment = $PaymentOption->getPayment();
                if ($Payment->isVisible()) {
                    $PaymentsByDeliveries[$Delivery->getId()][] = $Payment;
                }
            }
        }

        if (empty($PaymentsByDeliveries)) {
            return new ArrayCollection();
        }

        $i = 0;
        $PaymentsIntersected = [];
        foreach ($PaymentsByDeliveries as $Payments) {
            if ($i === 0) {
                $PaymentsIntersected = $Payments;
            } else {
                $PaymentsIntersected = array_intersect($PaymentsIntersected, $Payments);
            }
            $i++;
        }

        return new ArrayCollection($PaymentsIntersected);
    }

    /**
     * 支払い方法の利用条件でフィルタをかける.
     *
     * @param ArrayCollection $Payments
     * @param $total
     *
     * @return Payment[]
     */
    private function filterPayments(ArrayCollection $Payments, $total)
    {
        $PaymentArrays = $Payments->filter(function (Payment $Payment) use ($total) {
            $charge = $Payment->getCharge();
            $min = $Payment->getRuleMin();
            $max = $Payment->getRuleMax();

            if (null !== $min && ($total + $charge) < $min) {
                return false;
            }

            if (null !== $max && ($total + $charge) > $max) {
                return false;
            }

            return true;
        })->toArray();
        usort($PaymentArrays, function (Payment $a, Payment $b) {
            return $a->getSortNo() < $b->getSortNo() ? 1 : -1;
        });

        return $PaymentArrays;
    }
}
