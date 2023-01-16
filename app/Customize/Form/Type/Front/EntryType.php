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

use Eccube\Common\EccubeConfig;
use Eccube\Entity\Customer;
use Customize\Form\Type\AddressType;
use Eccube\Form\Type\KanaType;
use Eccube\Form\Type\Master\JobType;
use Eccube\Form\Type\Master\SexType;
use Eccube\Form\Type\NameType;
use Eccube\Form\Type\PhoneNumberType;
use Eccube\Form\Type\RepeatedEmailType;
use Eccube\Form\Type\RepeatedPasswordType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Eccube\Form\Type\PostalType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class EntryType extends AbstractType
{
    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * EntryType constructor.
     *
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
            ->add('name', NameType::class, [
                'required' => true,
            ])
            ->add('kana', KanaType::class, [])
            ->add('company_name', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                ],
            ])
            ->add('postal_code', PostalType::class, [
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_postal_code'],
                    ]),
                ],
            ])
            ->add('address', AddressType::class)
            ->add('phone_number', PhoneNumberType::class, [
                'required' => true,
				'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_tel_len_max'],
                    ]),
                ],
            ])
            ->add('email', RepeatedEmailType::class, [
                'constraints' => [
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_email_max_len'],
                    ]),
                ],
            ])
            ->add('password', RepeatedPasswordType::class)
            ->add('birth', BirthdayType::class, [
                'required' => false,
                'input' => 'datetime',
                'years' => range(date('Y'), date('Y') - $this->eccubeConfig['eccube_birth_max']),
                'widget' => 'choice',
                'format' => 'yyyy/MM/dd',
                'placeholder' => ['year' => '----', 'month' => '--', 'day' => '--'],
                'constraints' => [
                    new Assert\LessThanOrEqual([
                        'value' => date('Y-m-d', strtotime('-1 day')),
                        'message' => 'form_error.select_is_future_or_now_date',
                    ]),
                    new Assert\NotBlank([
                        'message' => '生年月日を入力してください。'
                    ]),
                ],
            ])
            ->add('sex', SexType::class, [
                'required' => true,
            ])
            ->add('job', JobType::class, [
                'required' => false,
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $Customer = $event->getData();
            if ($Customer instanceof Customer && !$Customer->getId()) {
                $form = $event->getForm();

                $form->add('user_policy_check', CheckboxType::class, [
                        'required' => true,
                        'label' => null,
                        'mapped' => false,
                        'constraints' => [
                            new Assert\NotBlank([
                                'message' => '利用規約に同意してください。'
                            ]),
                        ],
                    ]);
            }
        }
        );

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            /** @var Customer $Customer */
            $Customer = $event->getData();
            if ($Customer->getPassword() != '' && $Customer->getPassword() == $Customer->getEmail()) {
                $form['password']['first']->addError(new FormError(trans('common.password_eq_email')));
            }
			if ($form['magazine_usage_id']->getViewData() == '') {
                $form['magazine_usage_id']->addError(new FormError(trans('お受け取り方法を選択してください。')));
            }
			if ($form['email']['first']->getViewData() == '' && $form['email']['second']->getViewData() != '') {
                $form['email']['first']->addError(new FormError(trans('入力されていません。')));
            }
			if ($Customer->getPassword() == '') {
                $form['password']['first']->clearErrors();
                $form['password']['second']->clearErrors();
                $form['password']['first']->addError(new FormError(trans('パスワードを入力してください。')));
                $form['password']['second']->addError(new FormError(trans('同じパスワードを入力してください。')));
            }
			if ($form['overseas_address']->getViewData() != '') {
                $form['address']['pref']->clearErrors();
                $form['address']['addr01']->clearErrors();
                $form['address']['addr02']->clearErrors();
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Eccube\Entity\Customer',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        // todo entry,mypageで共有されているので名前を変更する
        return 'entry';
    }
}
