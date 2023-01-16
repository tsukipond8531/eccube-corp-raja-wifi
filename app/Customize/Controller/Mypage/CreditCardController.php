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

namespace Customize\Controller\Mypage;

use Eccube\Controller\AbstractController;
use Eccube\Entity\Customer;
use Eccube\Event\EventArgs;
use Eccube\Repository\PageRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Doctrine\Common\Collections\ArrayCollection;
use Customize\Entity\CreditCard;
use Customize\Form\Type\Front\CreditCardType;
use Customize\Repository\CreditCardRepository;

use Customize\Service\StripeRequestService;

use Stripe\Stripe;
use Eccube\Common\EccubeConfig;
use Stripe\PaymentMethod;

class CreditCardController extends AbstractController
{

    /**
     * @var CreditCardRepository;
     */
    protected $creditCardRepository;

    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * @var StripeRequestService;
     */
    private $stripeRequestService;

    const FRONT_MYPAGE_CREDITCARD_INDEX = "front.mypage.creditcard.index";
    const FRONT_MYPAGE_CREDITCARD_INPUT = 'front.mypage.creditcard.input';
    const FRONT_MYPAGE_CREDITCARD_REGIST = 'front.mypage.creditcard.regist';
    const FRONT_MYPAGE_CREDITCARD_CONFIRM = 'front.mypage.creditcard.confirm';
    const FRONT_MYPAGE_CREDITCARD_DELETE = 'front.mypage.creditcard.delete';

    /**
     * CreditCardController constructor.
     *
     * @param CreditCardRepository $creditCardRepository
     * @param PageRepository $pageRepository
     * @param StripeRequestService $stripeRequestService
     * @param EccubeConfig $eccubeConfig
     */
    public function __construct(
        CreditCardRepository $creditCardRepository,
        PageRepository $pageRepository,
        StripeRequestService $stripeRequestService,
        EccubeConfig $eccubeConfig
    ) {
        $this->creditCardRepository = $creditCardRepository;
        $this->pageRepository = $pageRepository;
        $this->stripeRequestService = $stripeRequestService;
        $this->eccubeConfig = $eccubeConfig;

        Stripe::setApiKey($this->eccubeConfig['stripe_secret_key']);
    }

    /**
     * クレジットカード情報を表示する.
     *
     * @Route("/mypage/creditcard", name="mypage_creditcard", methods={"GET", "POST"})
     * @Template("Mypage/creditcard.twig")
     */
    public function index(Request $request, PaginatorInterface $paginator)
    {
        $customer = $this->getUser();

		if ($request->getMethod() == 'POST') {
            $creditCard = new CreditCard();
            $creditCard->setHolderName($request->get('holder_name'));
            $creditCard->setExpirationMonth($request->get('exp_month'));
            $creditCard->setExpirationYear($request->get('exp_year'));
            $creditCard->setSecurityCode($request->get('security_code'));
            $creditCard->setCreditCardNumber($request->get('card_number'));

            // Stripeサーバーに決済用クレジットカード情報を登録
            $stripe_cardkey = $this->stripeRequestService->createCreditCardOnStripeService($creditCard, $customer);
            $creditCard->setStripeCardkey($stripe_cardkey);
        }

        $cards = new ArrayCollection();
        if ($customer->hasStripeCustomerId()) {
            $cards = PaymentMethod::all([
                'customer' => $customer->getStripeCustomerId(),
                'type' => 'card'
            ]);
            $cards = new ArrayCollection($cards->data);
        }
        // dump($cards); die();

        $qb = $this->creditCardRepository->getQueryBuilderByCustomerID($customer);

        $event = new EventArgs(
            [
                'qb' => $qb,
                'Customer' => $customer,
            ],
            $request
        );

        $this->eventDispatcher->dispatch(self::FRONT_MYPAGE_CREDITCARD_INDEX, $event);

        $pagination = $paginator->paginate(
            $qb,
            $request->get('pageno', 1),
            $this->eccubeConfig['eccube_search_pmax']
        );
	// dump($cards);die();
        return [
            'cards' => $cards,
			'Customer' => $customer,
            'pagination' => $pagination,
        ];
    }

    /**
     * クレジットカードを追加する.
     *
     * @Route("/mypage/creditcard/input", name="mypage_creditcard_input", methods={"GET", "POST"})
     * @Route("/mypage/creditcard/input", name="mypage_creditcard_input_confirm", methods={"GET", "POST"})
     * @Template("Mypage/creditcard_input.twig")
     */
    public function input(Request $request)
    {
        /** @var Customer $user */
        $customer = $this->getUser();

        $builder = $this->formFactory->createBuilder(CreditCardType::class);

        if ($this->isGranted('ROLE_USER')) {

            $builder->setData(
                [
                    'customer_id' => $customer->getId(),
                ]
            );
        }

        $event = new EventArgs(
            [
                'builder' => $builder,
                'customer' => $customer,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(self::FRONT_MYPAGE_CREDITCARD_INPUT, $event);

        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($request->get('mode')) {
                case 'confirm':
                    return $this->render('Mypage/creditcard_confirm.twig', [
                        'form' => $form->createView(),
                        'Page' => $this->pageRepository->getPageByRoute('mypage_creditcard_confirm'),
                    ]);
                case 'complete':
                    $creditcard_data = $form->getData();
                    $event = new EventArgs(
                        [
                            'form' => $form,
                            'data' => $creditcard_data,
                        ],
                        $request
                    );
                    $this->eventDispatcher->dispatch(self::FRONT_MYPAGE_CREDITCARD_REGIST, $event);

                    $creditCard = new CreditCard();
                    $creditCard->fromArray($this->creditCardRepository->prepareAttributes($creditcard_data));

                    // Stripeサーバーに決済用クレジットカード情報を登録
                    $stripe_cardkey = $this->stripeRequestService->createCreditCardOnStripeService($creditCard, $customer);
                    $creditCard->setStripeCardkey($stripe_cardkey);
                    $this->creditCardRepository->addCreditCard($creditCard);


                    log_info('クレジットカード登録完了');

                    return $this->redirect($this->generateUrl('mypage_creditcard_complete'));
            }
        }
        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * クレジットカード登録完了画面.
     *
     * @Route("/mypage/creditcard/complete", name="mypage_creditcard_complete", methods={"GET"})
     * @Template("Mypage/creditcard_complete.twig")
     */
    public function complete()
    {
        return [];
    }

    /**
     * クレジットカードを削除する.
     *
     * @Route("/mypage/creditcard/delete/{pm}", name="mypage_creditcard_delete", methods={"DELETE"})
     */
    public function delete(String $pm)
    {
        $this->isTokenValid();

        // log_info('クレジットカード削除開始', [$creditCard->getId()]);

        // $customer = $this->getUser();

        // if ($customer->getId() != $creditCard->getCustomerId()) {
        //     throw new BadRequestHttpException();
        // }

        // $this->creditCardRepository->deleteCreditCard($creditCard);

        // $event = new EventArgs(
        //     [
        //         'Customer' => $customer,
        //         'CreditCard' => $creditCard,
        //     ], $request
        // );
        // $this->eventDispatcher->dispatch(self::FRONT_MYPAGE_CREDITCARD_DELETE, $event);

        // $this->stripeRequestService->deleteCreditCardOnStripeService($creditCard, $customer);

        // log_info('クレジットカード削除開始完了', [$creditCard->getId()]);

        /** @var Customer $customer */
        $customer = $this->getUser();


        if (!$customer instanceof Customer) {
            return $this->redirectToRoute('mypage_login');
        }

        $paymentMethod = PaymentMethod::retrieve($pm);
        if ($customer->getStripeCustomerId() === $paymentMethod->customer) {
            $paymentMethod->detach();
        }

        return $this->redirect($this->generateUrl('mypage_creditcard'));
    }
}
