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

namespace Customize\Form\Type\Front;

use Customize\Service\GetYearsService;
use DateTime;
use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class CreditCardType extends AbstractType
{
    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * @var GetYearsService
     */
    private $getYearsService;

    /**
     * ContactType constructor.
     *
     * @param EccubeConfig $eccubeConfig
     */
    public function __construct(EccubeConfig $eccubeConfig, GetYearsService $getYearsService)
    {
        $this->eccubeConfig = $eccubeConfig;
        $this->getYearsService = $getYearsService;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('credit_card_type', ChoiceType::class, [
                'required' => true,
                'expanded' => false,
                'multiple' => false,
                'choices'  => [
                    'VISA'    => 1,
                    'MASTER'  => 2,
                    'DINERS'  => 3,
                    'JCB'     => 4,
                    'AMEX'    => 5
                ],
                'eccube_form_options' =>
                    [
                        'auto_render' => true,
                        'form_theme' => null,
                        'style_class' => 'ec-select',
                    ]
            ])
            ->add('credit_card_number', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Type([
                        'type' => 'numeric',
                        'message' => 'クレジットカード番号は数値を入力してください。',
                    ]),
                    new NotBlank(),
                    new Length([
                        'max' => 16,
                        'min' => 14,
                    ]),
                ],
                'attr' => ['maxlength' => 16],
            ])
            ->add('holder_name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                    new Assert\Regex(array('pattern' => "/[^a-zA-Z\d\s]/", 'match' => false, 'message' => '※ カードの名義人名は英数字で入力してください。')),
                ],
            ])
            ->add('expiration_month', ChoiceType::class, [
                'required' => true,
                'choices' => [
                    '-' => null,
                    '1月' => '1',
                    '2月' => '2',
                    '3月' => '3',
                    '4月' => '4',
                    '5月' => '5',
                    '6月' => '6',
                    '7月' => '7',
                    '8月' => '8',
                    '9月' => '9',
                    '10月' => '10',
                    '11月' => '11',
                    '12月' => '12',
                ],
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('expiration_year', ChoiceType::class, [
                'required' => true,
                'choices' => $this->getYearsService->get(10),
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('security_code', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Type([
                        'type' => 'numeric',
                        'message' => 'セキュリティコードは数値を入力してください。',
                    ]),
                    new NotBlank(),
                    new Length([
                        'max' => 4,
                        'min' => 3,
                    ]),
                ],
                'attr' => ['maxlength' => 4],
            ]);

        $builder
            ->addEventListener(
                FormEvents::POST_SUBMIT,
                [$this, 'validateExpiration']
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'creditcard';
    }

    public function validateExpiration(FormEvent $event)
    {
        /** @var Form $form */
        $form = $event->getForm();
        $form['expiration_month'];
        $form['expiration_year'];
        $inputExpirationDateTime = new DateTime($form['expiration_year']->getData().'-'.sprintf('%02d', $form['expiration_month']->getData()).'-01');
        $firstDayOfThisMonth = new Datetime('first day of this month');
        $firstDayOfThisMonth->setTime(00, 00, 00);

        if ($firstDayOfThisMonth > $inputExpirationDateTime) {
            $form['expiration_year']->addError(new FormError('有効な年月を入力する必要があります。'));
            $form['expiration_month']->addError(new FormError(''));
        }
    }
}
