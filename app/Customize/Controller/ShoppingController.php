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

namespace Customize\Controller;

use Eccube\Controller\AbstractShoppingController;
// use Customize\Entity\OptionItem;
use Customize\Entity\DeliveryItem;
use Eccube\Entity\BaseInfo;
use Eccube\Entity\Product;
use Eccube\Entity\CustomerAddress;
use Eccube\Entity\Order;
use Eccube\Entity\OrderItem;
use Eccube\Entity\Master\OrderItemType;
use Eccube\Entity\Shipping;
use Eccube\Entity\CartItem;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Entity\ProductClass;
use Eccube\Exception\ShoppingException;
use Eccube\Form\Type\Front\CustomerLoginType;
use Eccube\Form\Type\Front\ShoppingShippingType;
use Customize\Form\Type\Front\EntryType;
use Eccube\Form\Type\Shopping\CustomerAddressType;
use Customize\Form\Type\Shopping\OrderType;
use Customize\Form\Type\Shopping\OptionSelectType;
use Customize\Form\Type\Shopping\DeliveryType;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\OrderRepository;
use Eccube\Repository\CustomerRepository;
use Eccube\Repository\PageRepository;
use Eccube\Repository\CartItemRepository;
use Eccube\Repository\OrderItemRepository;
use Eccube\Repository\Master\ProductStatusRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Repository\TaxRuleRepository;
use Eccube\Repository\Master\OrderItemTypeRepository;
use Eccube\Entity\Master\ProductStatus;
use Eccube\Entity\ProductStock;
use Eccube\Service\CartService;
use Eccube\Service\MailService;
use Eccube\Service\OrderHelper;
use Eccube\Service\Payment\PaymentDispatcher;
use Eccube\Service\Payment\PaymentMethodInterface;
use Eccube\Service\PurchaseFlow\PurchaseContext;
use Eccube\Service\PurchaseFlow\PurchaseFlow;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Eccube\Repository\Master\PrefRepository;
use Eccube\Common\EccubeConfig;
use Stripe\PaymentMethod;
use Stripe\Stripe;
use Plugin\Stripe4\Repository\ConfigRepository;
use Stripe\PaymentIntent;
use Symfony\Component\HttpFoundation\ParameterBag;
use Plugin\Stripe4\Service\Method\CreditCard;
use Plugin\Paidy4\Service\Method\Paidy;
use Eccube\Repository\PaymentRepository;
use Eccube\Repository\CalendarRepository;

class ShoppingController extends AbstractShoppingController
{
    /**
     * @var CartService
     */
    protected $cartService;

    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * @var BaseInfo
     */
    protected $BaseInfo;

    /**
     * @var OrderHelper
     */
    protected $orderHelper;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var TaxRuleRepository
     */
    private $taxRuleRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var OrderItemRepository
     */
    protected $orderItemRepository;

    /**
     * @var ProductStatusRepository
     */
    protected $productStatusRepository;


    /**
     * @var EncoderFactoryInterface
     */
    protected $encoderFactory;

    /**
     * @var PageRepository
     */
    protected $pageRepository;

    /**
     * @var DeliveryTimeRepository
     */
    protected $saleTypeRepository;

    /**
     * @var PrefRepository
     */
    protected $prefRepository;

    /**
     * @var OrderItemTypeRepository
     */
    private $orderItemTypeRepository;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ParameterBag
     */
    private $parameterBag;

	/**
     * @var PaymentRepository
     */
    private $paymentRepository;
    private $calendarRepository;

    /**
     * ProductController constructor.
     *
     * @param ProductRepository $productRepository
     * @param TaxRuleRepository $taxRuleRepository
     * @param ProductStatusRepository $productStatusRepository
     * @param DeliveryTimeRepository $saleTypeRepository
     * @param OrderItemTypeRepository $orderItemTypeRepository
     * @param BaseInfoRepository $baseInfoRepository
     * @param CartService $cartService
     * @param MailService $mailService
     * @param PurchaseFlow $cartPurchaseFlow
     * @param OrderRepository $orderRepository
     * @param CustomerRepository $customerRepository
     * @param OrderItemRepository $orderItemRepository
     * @param EncoderFactoryInterface $encoderFactory
     * @param PageRepository $pageRepository
     * @param PrefRepository $prefRepository
     * @param OrderHelper $orderHelper
     * @param EccubeConfig $eccubeConfig
     * @param ConfigRepository $configRepository
	 * @param PaymentRepository $paymentRepository
     * @param ParameterBag $parameterBag
     * @param CalendarRepository $calendarRepository
     */

    public function __construct(
        CartService $cartService,
        MailService $mailService,
        PurchaseFlow $cartPurchaseFlow,
        OrderRepository $orderRepository,
        OrderItemTypeRepository $orderItemTypeRepository,
        CustomerRepository $customerRepository,
        ProductRepository $productRepository,
        TaxRuleRepository $taxRuleRepository,
        OrderItemRepository $orderItemRepository,
        BaseInfoRepository $baseInfoRepository,
        EncoderFactoryInterface $encoderFactory,
        PageRepository $pageRepository,
        ProductStatusRepository $productStatusRepository,
        PrefRepository $prefRepository,
        OrderHelper $orderHelper,
        EccubeConfig $eccubeConfig,
        ConfigRepository $configRepository,
		PaymentRepository $paymentRepository,
        ParameterBag $parameterBag,
        CalendarRepository $calendarRepository
    ) {
        $this->cartService = $cartService;
        $this->mailService = $mailService;
        $this->purchaseFlow = $cartPurchaseFlow;
        $this->orderRepository = $orderRepository;
        $this->BaseInfo = $baseInfoRepository->get();
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->orderItemTypeRepository = $orderItemTypeRepository;
        $this->taxRuleRepository = $taxRuleRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->encoderFactory = $encoderFactory;
        $this->orderHelper = $orderHelper;
        $this->pageRepository = $pageRepository;
        $this->prefRepository = $prefRepository;
        $this->productStatusRepository = $productStatusRepository;
        $this->eccubeConfig = $eccubeConfig;
        $this->config = $configRepository->get();
        $this->parameterBag = $parameterBag;
		$this->paymentRepository = $paymentRepository;
		$this->calendarRepository = $calendarRepository;
        
        Stripe::setApiKey($this->eccubeConfig['stripe_secret_key']);
    }

    /**
     * 注文手続き画面を表示する
     *
     * 未ログインまたはRememberMeログインの場合はログイン画面に遷移させる.
     * ただし、非会員でお客様情報を入力済の場合は遷移させない.
     *
     * カート情報から受注データを生成し, `pre_order_id`でカートと受注の紐付けを行う.
     * 既に受注が生成されている場合(pre_order_idで取得できる場合)は, 受注の生成を行わずに画面を表示する.
     *
     * purchaseFlowの集計処理実行後, warningがある場合はカートど同期をとるため, カートのPurchaseFlowを実行する.
     *
     * @Route("/shopping", name="shopping", methods={"GET"})
     * @Template("Shopping/index.twig")
     */
    public function index(Request $request, PurchaseFlow $cartPurchaseFlow)
    {
        // ログイン状態のチェック.
        if ($this->orderHelper->isLoginRequired()) {
            log_info('[注文手続] 未ログインもしくはRememberMeログインのため, ログイン画面に遷移します.');

            return $this->redirectToRoute('shopping_login');
        }

        // カートチェック.
        $Cart = $this->cartService->getCart();
        if (!($Cart && $this->orderHelper->verifyCart($Cart))) {
            log_info('[注文手続] カートが購入フローへ遷移できない状態のため, カート画面に遷移します.');

            return $this->redirectToRoute('shopping');
        }

        // 受注の初期化.
        log_info('[注文手続] 受注の初期化処理を開始します.');
        $Customer = $this->getUser() ? $this->getUser() : $this->orderHelper->getNonMember();
        $Order = $this->orderHelper->initializeOrder($Cart, $Customer);

        // 集計処理.
        log_info('[注文手続] 集計処理を開始します.', [$Order->getId()]);
        $flowResult = $this->executePurchaseFlow($Order, false);
        $this->entityManager->flush();

        if ($flowResult->hasError()) {
            log_info('[注文手続] Errorが発生したため購入エラー画面へ遷移します.', [$flowResult->getErrors()]);

            return $this->redirectToRoute('shopping_error');
        }

        if ($flowResult->hasWarning()) {
            log_info('[注文手続] Warningが発生しました.', [$flowResult->getWarning()]);

            // 受注明細と同期をとるため, CartPurchaseFlowを実行する
            $cartPurchaseFlow->validate($Cart, new PurchaseContext($Cart, $this->getUser()));

            // 注文フローで取得されるカートの入れ替わりを防止する
            // @see https://github.com/EC-CUBE/ec-cube/issues/4293
            $this->cartService->setPrimary($Cart->getCartKey());
        }

        // マイページで会員情報が更新されていれば, Orderの注文者情報も更新する.
        if ($Customer->getId()) {
            $this->orderHelper->updateCustomerInfo($Order, $Customer);
            $this->entityManager->flush();
        }

        $form = $this->createForm(OrderType::class, $Order);
        // $DeliveryItem = new DeliveryItem();
        // $form = $this->createForm(DeliveryType::class, $DeliveryItem);

        $sid = $request->get('sid');
        $sname = $request->get('sname');
        $saddr = $request->get('saddr');
        $spt = $request->get('spt');
        $stflg = $request->get('stflg');
        $uid = $request->get('uid');
        $sjarea = $request->get('sjarea');
        $szcd = $request->get('szcd');
        $stel = $request->get('stel');
        $scode = $request->get('scode');
        $spare3 = $request->get('spare3');
        $spare4 = $request->get('spare4');


        $sname = mb_convert_encoding($sname, "UTF-8", "EUC-JP");
        $spare3 = mb_convert_encoding($spare3, "UTF-8", "EUC-JP");
        $spare4 = mb_convert_encoding($spare4, "UTF-8", "EUC-JP");
        $saddr = mb_convert_encoding($saddr, "UTF-8", "EUC-JP");


        $paymentMethodId = $Order->getStripePaymentMethodId();
        $paymentIntentData = [
            "amount" => (int)$Order->getPaymentTotal(),
            "currency" => $this->eccubeConfig["currency"],
            // "payment_method" => $paymentMethodId,
            'automatic_payment_methods' => ['enabled' => true],
            // 'return_url' => 'http://localhost/shopping/confirm',
            // "confirmation_method" => "manual",
            // "confirm" => true,
            // "capture_method" => $this->config->getCapture() ? "automatic" : "manual",
        ];

        if ($customer = $Order->getCustomer()) {
            if ($customer->hasStripeCustomerId()) {
                $paymentIntentData['customer'] = $customer->getStripeCustomerId();
            }

            if ($Order->isStripeSaveCard()) {
                if (false === $customer->hasStripeCustomerId()) {
                    $stripeCustomer = Customer::create([
                        "email" => $Order->getCustomer()->getEmail()
                    ]);
                    logs('stripe')->info($stripeCustomer->status);
                    $paymentIntentData['customer'] = $stripeCustomer->id;
                }
                // Stripe顧客にカード情報をアタッチする
                $paymentIntentData['setup_future_usage'] = 'off_session';
            }
        }

        $intent = PaymentIntent::create($paymentIntentData);
        $Order->setStripePaymentIntentId($intent->id);
        $this->entityManager->flush();
        
        $Calendars = $this->calendarRepository->findAll();
        $holidays = [];
        foreach ($Calendars as $Calendar){
            $holidays[] = date_format($Calendar->getHoliday(), 'Y-m-d');
        }

        return [
            'form' => $form->createView(),
            'Order' => $Order,
            'sid' => $sid,
            'sname' => $sname,
            'saddr' => $saddr,
            'spt' => $spt,
            'stflg' => $stflg,
            'uid' => $uid,
            'sjarea' => $sjarea,
            'szcd' => $szcd,
            'stel' => $stel,
            'scode' => $scode,
            'spare3' => $spare3,
            'spare4' => $spare4,
            'client_secret' => $intent['client_secret'],
            'holidays' => $holidays,
        ];
    }

    /**
     * 他画面への遷移を行う.
     *
     * お届け先編集画面など, 他画面へ遷移する際に, フォームの値をDBに保存してからリダイレクトさせる.
     * フォームの`redirect_to`パラメータの値にリダイレクトを行う.
     * `redirect_to`パラメータはpath('遷移先のルーティング')が渡される必要がある.
     *
     * 外部のURLやPathを渡された場合($router->matchで展開出来ない場合)は, 購入エラーとする.
     *
     * プラグインやカスタマイズでこの機能を使う場合は, twig側で以下のように記述してください.
     *
     * <button data-trigger="click" data-path="path('ルーティング')">更新する</button>
     *
     * data-triggerは, click/change/blur等のイベント名を指定してください。
     * data-pathは任意のパラメータです. 指定しない場合, 注文手続き画面へリダイレクトします.
     *
     * @Route("/shopping/redirect_to", name="shopping_redirect_to", methods={"POST"})
     * @Template("Shopping/index.twig")
     */
    public function redirectTo(Request $request, RouterInterface $router)
    {
        // ログイン状態のチェック.
        if ($this->orderHelper->isLoginRequired()) {
            log_info('[リダイレクト] 未ログインもしくはRememberMeログインのため, ログイン画面に遷移します.');

            return $this->redirectToRoute('shopping_login');
        }

        // 受注の存在チェック.
        $preOrderId = $this->cartService->getPreOrderId();
        $Order = $this->orderHelper->getPurchaseProcessingOrder($preOrderId);
        if (!$Order) {
            log_info('[リダイレクト] 購入処理中の受注が存在しません.');

            return $this->redirectToRoute('shopping_error');
        }

        $form = $this->createForm(OrderType::class, $Order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            log_info('[リダイレクト] 集計処理を開始します.', [$Order->getId()]);
            $response = $this->executePurchaseFlow($Order);
            $this->entityManager->flush();

            if ($response) {
                return $response;
            }

            $redirectTo = $form['redirect_to']->getData();
            if (empty($redirectTo)) {
                log_info('[リダイレクト] リダイレクト先未指定のため注文手続き画面へ遷移します.');

                return $this->redirectToRoute('shopping');
            }

            try {
                // リダイレクト先のチェック.
                $pattern = '/^'.preg_quote($request->getBasePath(), '/').'/';
                $redirectTo = preg_replace($pattern, '', $redirectTo);
                $result = $router->match($redirectTo);
                // パラメータのみ抽出
                $params = array_filter($result, function ($key) {
                    return 0 !== \strpos($key, '_');
                }, ARRAY_FILTER_USE_KEY);

                log_info('[リダイレクト] リダイレクトを実行します.', [$result['_route'], $params]);

                // pathからurlを再構築してリダイレクト.
                return $this->redirectToRoute($result['_route'], $params);
            } catch (\Exception $e) {
                log_info('[リダイレクト] URLの形式が不正です', [$redirectTo, $e->getMessage()]);

                return $this->redirectToRoute('shopping_error');
            }
        }

        log_info('[リダイレクト] フォームエラーのため, 注文手続き画面を表示します.', [$Order->getId()]);

        return [
            'form' => $form->createView(),
            'Order' => $Order,
        ];
    }

    /**
     * 注文確認画面を表示する.
     *
     * ここではPaymentMethod::verifyがコールされます.
     * PaymentMethod::verifyではクレジットカードの有効性チェック等, 注文手続きを進められるかどうかのチェック処理を行う事を想定しています.
     * PaymentMethod::verifyでエラーが発生した場合は, 注文手続き画面へリダイレクトします.
     *
     * @Route("/shopping/confirm", name="shopping_confirm", methods={"GET", "POST"})
     * @Template("Shopping/confirm.twig")
     */
    public function confirm(Request $request)
    {
        // ログイン状態のチェック.
        if ($this->orderHelper->isLoginRequired()) {
            log_info('[注文確認] 未ログインもしくはRememberMeログインのため, ログイン画面に遷移します.');

            return $this->redirectToRoute('shopping_login');
        }

        // 受注の存在チェック
        $preOrderId = $this->cartService->getPreOrderId();
        $Order = $this->orderHelper->getPurchaseProcessingOrder($preOrderId);
        
        if (!$Order) {
            log_info('[注文確認] 購入処理中の受注が存在しません.', [$preOrderId]);

            return $this->redirectToRoute('shopping_error');
        }

        $form = $this->createForm(OrderType::class, $Order);
        
        $form->handleRequest($request);

        // if ($form->isSubmitted() && $form->isValid()) {
            
        if ($form->isSubmitted()) {
            // $Order = $this->orderHelper->getPurchaseProcessingOrder($preOrderId);

            log_info('[注文確認] 集計処理を開始します.', [$Order->getId()]);
            $response = $this->executePurchaseFlow($Order);
            $Order->setUsePoint(0);

            if ($Order->getName01() == '') {
                $Order->setName01($Order->getCustomer()->getName01());
            }
            if ($Order->getName02() == '') {
                $Order->setName02($Order->getCustomer()->getName02());
            }
            if ($Order->getKana01() == '') {
                $Order->setKana01($Order->getCustomer()->getkana01());
            }
            if ($Order->getKana02() == '') {
                $Order->setKana02($Order->getCustomer()->getkana02());
            }
            if ($Order->getCompanyName() == '') {
                $Order->setCompanyName($Order->getCustomer()->getCompanyName());
            }
            if ($Order->getPostalCode() == '') {
                $Order->setPostalCode($Order->getCustomer()->getPostalCode());
            }
            if ($Order->getPhonenumber() == '') {
                $Order->setPhoneNumber($Order->getCustomer()->getPhonenumber());
            }
            if ($Order->getPref() == '') {
                $Order->setPref($Order->getCustomer()->getPref());
            }
            if ($Order->getAddr01() == '') {
                $Order->setAddr01($Order->getCustomer()->setAddr01());
            }
            if ($Order->getAddr02() == '') {
                $Order->setAddr02($Order->getCustomer()->setAddr02());
            }
            $this->entityManager->flush();

            if ($response) {
                return $response;
            }

            $deliver_group = $request->get('deliver-group');
            $receive_group = $request->get('receive-group');
			$payment_group = $request->get('radio-group');
            $production2 = $request->get('productselection2-group');

            if ($receive_group == '1') { 
                if ($deliver_group == '2') {
                    $order_data = $request->get('_shopping_order');
                    $CustomerAddress = new CustomerAddress();
                    
                    $CustomerAddress->setName01($order_data['name']['name01']);
                    $CustomerAddress->setName02($order_data['name']['name02']);
                    $CustomerAddress->setKana01($order_data['kana']['kana01']);
                    $CustomerAddress->setKana02($order_data['kana']['kana02']);
                    $CustomerAddress->setCompanyName($order_data['company_name']);
                    $CustomerAddress->setPostalCode($order_data['postal_code']);
                    $CustomerAddress->setPhoneNumber($order_data['phone_number']);
                    $Pref = $this->prefRepository->find($order_data['address']['pref']);
                    $CustomerAddress->setPref($Pref);
                    $CustomerAddress->setAddr01($order_data['address']['addr01']);
                    $CustomerAddress->setAddr02($order_data['address']['addr02']);
                }
            } else if ($receive_group == '3') {
                $Order->setPostalCode(str_replace('〒', '', $request->get('convenie_szcd')));
                $Order->setPhoneNumber($request->get('convenie_stel'));
                $addr = $request->get('convenie_saddr');
                if (str_contains($addr, '都')) {
                    $pref = explode('都', $addr)[0].'都';
                    $addr = str_replace($pref, '', $addr);
                } else if (str_contains($addr, '道')) {
                    $pref = explode('道', $addr)[0].'道';
                    $addr = str_replace($pref, '', $addr);
                } else if (str_contains($addr, '府')) {
                    $pref = explode('府', $addr)[0].'府';
                    $addr = str_replace($pref, '', $addr);
                } else if (str_contains($addr, '県')) {
                    $pref = explode('県', $addr)[0].'県';
                    $addr = str_replace($pref, '', $addr);
                }
                $Pref = $this->prefRepository->findBy(['name' => $pref])[0];
                $Order->setPref($Pref);
                $Order->setAddr01($addr);
                $Order->setAddr02('');
                $Order->setCompanyName($request->get('convenie_sname'));

            } else if ($receive_group == '4') {
                $Order->setPostalCode('1600023');
                $Pref = $this->prefRepository->findBy(['name' => '東京都'])[0];
                $Order->setPref($Pref);
                $Order->setAddr01('新宿区⻄新宿7-18-19');
                $Order->setAddr02('新宿税理⼠ビル第⼆別館佐⽵ビル2F');
                $Order->setCompanyName('Wi-Fi東京レンタルショップ');
                $Order->setPhoneNumber('0120295755');
            }

			if ($payment_group == '1') {
                $payment = $this->paymentRepository->findOneBy([
                    "method_class" => CreditCard::class
                ]);
                $Order->setPayment($payment);
				$Order->setPaymentMethod($payment->getMethod());
            } else if ($payment_group == '2') {
                $payment = $this->paymentRepository->findOneBy([
                    "method_class" => Paidy::class
                ]);
                $Order->setPayment($payment);
				$Order->setPaymentMethod($payment->getMethod());
            }

			$this->entityManager->persist($Order);
			$this->entityManager->flush();
            $is_production2 = false;

            foreach ($Order->getOrderItems() as $OrderItem) {
                if ($OrderItem->isProduct()) {
                    if ($OrderItem->getProduct()->getId() == '7') {
                        $is_production2 = true;
                    }
                }
                if ($Shipping = $OrderItem->getShipping()) {
                    if ($receive_group == '1') {
                        if ($deliver_group == '2') {
                            $Shipping->setFromCustomerAddress($CustomerAddress);
                        }
                    } else if ($receive_group == '3') {
                        $Shipping->setPref($Pref);
                        $Shipping->setAddr01($addr);
                        $Shipping->setAddr02('');
                        $Shipping->setCompanyName($request->get('convenie_sname'));
                        $Shipping->setPostalCode(str_replace('〒', '', $request->get('convenie_szcd')));
                        $Shipping->setPhoneNumber($request->get('convenie_stel'));
                    } else if ($receive_group == '4') {
                        $Shipping->setPref($Pref);
                        $Shipping->setAddr01('新宿区⻄新宿7-18-19');
                        $Shipping->setAddr02('新宿税理⼠ビル第⼆別館佐⽵ビル2F');
                        $Shipping->setCompanyName('Wi-Fi東京レンタルショップ');
                        $Shipping->setPostalCode('1600023');
                        $Shipping->setPhoneNumber('0120295755');
                    }
                    $OrderItem->setShipping($Shipping); 
                    $this->entityManager->persist($OrderItem);
                    $this->entityManager->flush();
                }
            }
            
            if ($production2 == '1') {
                if (!$is_production2) {            
                    $OptionProduct2Id = 7;
                    $OptionProduct2 = $this->productRepository->findWithSortedClassCategories($OptionProduct2Id);
                    $OptionProduct2Class = $OptionProduct2->getProductClasses()[0];
                    $orderItem = new OrderItem();
                    // デフォルト課税規則
                    $TaxRule = $this->taxRuleRepository->getByRule();
                    $OrderItemTypeProduct = $this->orderItemTypeRepository->find(OrderItemType::PRODUCT);
                    $orderItem->setProduct($OptionProduct2)
                        ->setProductClass($OptionProduct2Class)
                        ->setProductName($OptionProduct2->getName())
                        ->setOrderItemType($OrderItemTypeProduct)
                        ->setPrice($OptionProduct2Class->getPrice02())
                        ->setQuantity(1)
                        ->setTaxRuleId($TaxRule->getId())
                        ->setTaxRate($TaxRule->getTaxRate());
                    $this->entityManager->persist($orderItem);
                    $orderItem->setOrder($Order);
                    $Order->addOrderItem($orderItem);
                    $this->entityManager->flush();
                    $Order->setSubtotal($Order->getSubtotal() + 550);
                }
            } else {
                if ($is_production2) {
                    $is_removed = false;
                    foreach ($Order->getOrderItems() as $OrderItem) {
                        if ($OrderItem->isProduct()) {
                            if ($OrderItem->getProduct()->getId() == '7') {
                                $is_removed = $Order->removeOrderItem($OrderItem);
                                $this->entityManager->remove($OrderItem);
                                $this->entityManager->flush();
                                // $this->entityManager->flush();
                                $Order->setSubtotal($Order->getSubtotal() - 550);
                            }
                        }
                    }
                }
            }

            // $Order = $this->orderHelper->getPurchaseProcessingOrder($preOrderId);

            // $Shipping->setShippingDate();
            
            $DeliveryDate = new \DateTime($request->get('delivery_date'));
            // $DeliveryDate = new \DateTime($request->get('_shopping_order')['Shippings'][0]['DeliveryDate']);
            $Shipping->setShippingDeliveryDate($DeliveryDate);

            $event = new EventArgs(
                [
                    'Order' => $Order,
                    'Shipping' => $Shipping,
                ],
                $request
            );
            $this->eventDispatcher->dispatch(EccubeEvents::FRONT_SHOPPING_SHIPPING_COMPLETE, $event);

            log_info('[注文確認] PaymentMethod::verifyを実行します.', [$Order->getPayment()->getMethodClass()]);
            $paymentMethod = $this->createPaymentMethod($Order, $form);
            $PaymentResult = $paymentMethod->verify();

            if ($PaymentResult) {
                if (!$PaymentResult->isSuccess()) {
                    $this->entityManager->rollback();
                    foreach ($PaymentResult->getErrors() as $error) {
                        $this->addError($error);
                    }

                    log_info('[注文確認] PaymentMethod::verifyのエラーのため, 注文手続き画面へ遷移します.', [$PaymentResult->getErrors()]);

                    return $this->redirectToRoute('shopping');
                }

                $response = $PaymentResult->getResponse();
                if ($response instanceof Response && ($response->isRedirection() || $response->isSuccessful())) {
                    $this->entityManager->flush();

                    log_info('[注文確認] PaymentMethod::verifyが指定したレスポンスを表示します.');

                    return $response;
                }
            }

            $this->entityManager->flush();

            log_info('[注文確認] 注文確認画面を表示します.');

            return [
                'form' => $form->createView(),
                'Order' => $Order,
            ];
        }


        log_info('[注文確認] フォームエラーのため, 注文手続画面を表示します.', [$Order->getId()]);

        // FIXME @Templateの差し替え.
        $request->attributes->set('_template', new Template(['template' => 'Shopping/index.twig']));
        
        return [
            'form' => $form->createView(),
            'Order' => $Order,
        ];
    }

    /**
     * 注文処理を行う.
     *
     * 決済プラグインによる決済処理および注文の確定処理を行います.
     *
     * @Route("/shopping/checkout", name="shopping_checkout", methods={"POST"})
     * @Template("Shopping/confirm.twig")
     */
    public function checkout(Request $request)
    {
        // ログイン状態のチェック.
        if ($this->orderHelper->isLoginRequired()) {
            log_info('[注文処理] 未ログインもしくはRememberMeログインのため, ログイン画面に遷移します.');

            return $this->redirectToRoute('shopping_login');
        }

        // 受注の存在チェック
        $preOrderId = $this->cartService->getPreOrderId();

        $Order = $this->orderHelper->getPurchaseProcessingOrder($preOrderId);
        if (!$Order) {
            log_info('[注文処理] 購入処理中の受注が存在しません.', [$preOrderId]);
            return $this->redirectToRoute('shopping_error');
        }
        
        // フォームの生成.
        $form = $this->createForm(OrderType::class, $Order, [
            // 確認画面から注文処理へ遷移する場合は, Orderエンティティで値を引き回すためフォーム項目の定義をスキップする.
            'skip_add_form' => true,
        ]);

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            log_info('[注文処理] 注文処理を開始します.', [$Order->getId()]);

            try {
                /*
                 * 集計処理
                 */
                log_info('[注文処理] 集計処理を開始します.', [$Order->getId()]);
                $response = $this->executePurchaseFlow($Order);

                $this->entityManager->flush();

                if ($response) {
                    return $response;
                }

                log_info('[注文処理] PaymentMethodを取得します.', [$Order->getPayment()->getMethodClass()]);
                $paymentMethod = $this->createPaymentMethod($Order, $form);

                /*
                 * 決済実行(前処理)
                 */
                log_info('[注文処理] PaymentMethod::applyを実行します.');
                if ($response = $this->executeApply($paymentMethod)) {
                    return $response;
                }

                /*
                 * 決済実行
                 *
                 * PaymentMethod::checkoutでは決済処理が行われ, 正常に処理出来た場合はPurchaseFlow::commitがコールされます.
                 */
                log_info('[注文処理] PaymentMethod::checkoutを実行します.');
                if ($response = $this->executeCheckout($paymentMethod)) {
                    return $response;
                }
                
                $this->entityManager->flush();

                log_info('[注文処理] 注文処理が完了しました.', [$Order->getId()]);
            } catch (ShoppingException $e) {
                log_error('[注文処理] 購入エラーが発生しました.', [$e->getMessage()]);

                $this->entityManager->rollback();

                $this->addError($e->getMessage());
                return $this->redirectToRoute('shopping_error');
            } catch (\Exception $e) {
                log_error('[注文処理] 予期しないエラーが発生しました.', [$e->getMessage()]);

                $this->entityManager->rollback();

                $this->addError('front.shopping.system_error');
                return $this->redirectToRoute('shopping_error');
            }

            // カート削除
            log_info('[注文処理] カートをクリアします.', [$Order->getId()]);
            $this->cartService->clear();

            // 受注IDをセッションにセット
            $this->session->set(OrderHelper::SESSION_ORDER_ID, $Order->getId());

            // メール送信
            log_info('[注文処理] 注文メールの送信を行います.', [$Order->getId()]);
            $this->mailService->sendOrderMail($Order);
            $this->entityManager->flush();

            log_info('[注文処理] 注文処理が完了しました. 購入完了画面へ遷移します.', [$Order->getId()]);

            return $this->redirectToRoute('shopping_complete');
        }
        log_info('[注文処理] フォームエラーのため, 購入エラー画面へ遷移します.', [$Order->getId()]);

        return $this->redirectToRoute('shopping_error');
    }

    /**
     * 購入完了画面を表示する.
     *
     * @Route("/shopping/complete", name="shopping_complete", methods={"GET"})
     * @Template("Shopping/complete.twig")
     */
    public function complete(Request $request)
    {
        return[];
        log_info('[注文完了] 注文完了画面を表示します.');

        // 受注IDを取得
        $orderId = $this->session->get(OrderHelper::SESSION_ORDER_ID);

        if (empty($orderId)) {
            log_info('[注文完了] 受注IDを取得できないため, トップページへ遷移します.');

            return $this->redirectToRoute('homepage');
        }

        $Order = $this->orderRepository->find($orderId);

        $event = new EventArgs(
            [
                'Order' => $Order,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_SHOPPING_COMPLETE_INITIALIZE, $event);

        if ($event->getResponse() !== null) {
            return $event->getResponse();
        }

        log_info('[注文完了] 購入フローのセッションをクリアします. ');
        $this->orderHelper->removeSession();
        $this->session->remove('new_cart_item');

        $hasNextCart = !empty($this->cartService->getCarts());

        log_info('[注文完了] 注文完了画面を表示しました. ', [$hasNextCart]);

        return [
            'Order' => $Order,
            'hasNextCart' => $hasNextCart,
        ];
    }

    /**
     * お届け先選択画面.
     *
     * 会員ログイン時, お届け先を選択する画面を表示する
     * 非会員の場合はこの画面は使用しない。
     *
     * @Route("/shopping/shipping/{id}", name="shopping_shipping", requirements={"id" = "\d+"}, methods={"GET", "POST"})
     * @Template("Shopping/shipping.twig")
     */
    public function shipping(Request $request, Shipping $Shipping)
    {
        // ログイン状態のチェック.
        if ($this->orderHelper->isLoginRequired()) {
            return $this->redirectToRoute('shopping_login');
        }

        // 受注の存在チェック
        $preOrderId = $this->cartService->getPreOrderId();
        $Order = $this->orderHelper->getPurchaseProcessingOrder($preOrderId);
        if (!$Order) {
            return $this->redirectToRoute('shopping_error');
        }

        // 受注に紐づくShippingかどうかのチェック.
        if (!$Order->findShipping($Shipping->getId())) {
            return $this->redirectToRoute('shopping_error');
        }

        $builder = $this->formFactory->createBuilder(CustomerAddressType::class, null, [
            'customer' => $this->getUser(),
            'shipping' => $Shipping,
        ]);

        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            log_info('お届先情報更新開始', [$Shipping->getId()]);

            /** @var CustomerAddress $CustomerAddress */
            $CustomerAddress = $form['addresses']->getData();

            // お届け先情報を更新
            $Shipping->setFromCustomerAddress($CustomerAddress);

            // 合計金額の再計算
            $response = $this->executePurchaseFlow($Order);
            $this->entityManager->flush();

            if ($response) {
                return $response;
            }

            $event = new EventArgs(
                [
                    'Order' => $Order,
                    'Shipping' => $Shipping,
                ],
                $request
            );
            $this->eventDispatcher->dispatch(EccubeEvents::FRONT_SHOPPING_SHIPPING_COMPLETE, $event);

            log_info('お届先情報更新完了', [$Shipping->getId()]);

            return $this->redirectToRoute('shopping');
        }

        return [
            'form' => $form->createView(),
            'Customer' => $this->getUser(),
            'shippingId' => $Shipping->getId(),
        ];
    }

    /**
     * お届け先の新規作成または編集画面.
     *
     * 会員時は新しいお届け先を作成し, 作成したお届け先を選択状態にして注文手続き画面へ遷移する.
     * 非会員時は選択されたお届け先の編集を行う.
     *
     * @Route("/shopping/shipping_edit/{id}", name="shopping_shipping_edit", requirements={"id" = "\d+"}, methods={"GET", "POST"})
     * @Template("Shopping/shipping_edit.twig")
     */
    public function shippingEdit(Request $request, Shipping $Shipping)
    {
        // ログイン状態のチェック.
        if ($this->orderHelper->isLoginRequired()) {
            return $this->redirectToRoute('shopping_login');
        }

        // 受注の存在チェック
        $preOrderId = $this->cartService->getPreOrderId();
        $Order = $this->orderHelper->getPurchaseProcessingOrder($preOrderId);
        if (!$Order) {
            return $this->redirectToRoute('shopping_error');
        }

        // 受注に紐づくShippingかどうかのチェック.
        if (!$Order->findShipping($Shipping->getId())) {
            return $this->redirectToRoute('shopping_error');
        }

        $CustomerAddress = new CustomerAddress();
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            // ログイン時は会員と紐付け
            $CustomerAddress->setCustomer($this->getUser());
        } else {
            // 非会員時はお届け先をセット
            $CustomerAddress->setFromShipping($Shipping);
        }
        $builder = $this->formFactory->createBuilder(ShoppingShippingType::class, $CustomerAddress);

        $event = new EventArgs(
            [
                'builder' => $builder,
                'Order' => $Order,
                'Shipping' => $Shipping,
                'CustomerAddress' => $CustomerAddress,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_SHOPPING_SHIPPING_EDIT_INITIALIZE, $event);

        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            log_info('お届け先追加処理開始', ['order_id' => $Order->getId(), 'shipping_id' => $Shipping->getId()]);

            $Shipping->setFromCustomerAddress($CustomerAddress);

            if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
                $this->entityManager->persist($CustomerAddress);
            }

            // 合計金額の再計算
            $response = $this->executePurchaseFlow($Order);
            $this->entityManager->flush();

            if ($response) {
                return $response;
            }

            $event = new EventArgs(
                [
                    'form' => $form,
                    'Shipping' => $Shipping,
                    'CustomerAddress' => $CustomerAddress,
                ],
                $request
            );
            $this->eventDispatcher->dispatch(EccubeEvents::FRONT_SHOPPING_SHIPPING_EDIT_COMPLETE, $event);

            log_info('お届け先追加処理完了', ['order_id' => $Order->getId(), 'shipping_id' => $Shipping->getId()]);

            return $this->redirectToRoute('shopping');
        }

        return [
            'form' => $form->createView(),
            'shippingId' => $Shipping->getId(),
        ];
    }

    /**
     * ログイン画面.
     *
     * @Route("/shopping/login", name="shopping_login", methods={"GET", "POST"})
     * @Template("Shopping/login.twig")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils)
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $Cart = $this->cartService->getCart();
            if ($Cart && $this->orderHelper->verifyCart($Cart)) {
                return $this->redirectToRoute('shopping');
            } else {
                return $this->redirectToRoute('homepage');
            }
            
        }

        /* @var $form \Symfony\Component\Form\FormInterface */
        $builder = $this->formFactory->createNamedBuilder('', CustomerLoginType::class);

        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $Customer = $this->getUser();
            if ($Customer) {
                $builder->get('login_email')->setData($Customer->getEmail());
            }
        }

        $event = new EventArgs(
            [
                'builder' => $builder,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_SHOPPING_LOGIN_INITIALIZE, $event);

        $form = $builder->getForm();

        // カートチェック.
        $Cart = $this->cartService->getCart();
        return [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'form' => $form->createView(),
            'Cart' => $Cart,
        ];
    }

    /**
     * 登録画面.
     *
     * @Route("/shopping/regist_input", name="shopping_regist_input", methods={"GET", "POST"})
     * @Route("/shopping/regist_confirm", name="shopping_regist_confirm", methods={"GET", "POST"})
     * @Template("Shopping/regist_input.twig")
     */
    public function regist_input(Request $request)
    {
        if ($this->isGranted('ROLE_USER')) {
            log_info('認証済のためログイン処理をスキップ');

            return $this->redirectToRoute('shopping_regist_input');
        }
        /** @var $Customer \Eccube\Entity\Customer */
        $Customer = $this->customerRepository->newCustomer();

        /* @var $builder \Symfony\Component\Form\FormBuilderInterface */
        $builder = $this->formFactory->createBuilder(EntryType::class, $Customer);

        $event = new EventArgs(
            [
                'builder' => $builder,
                'Customer' => $Customer,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_ENTRY_INDEX_INITIALIZE, $event);

        /* @var $form \Symfony\Component\Form\FormInterface */
        $form = $builder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($request->get('mode')) {
                case 'confirm':
                    log_info('会員登録確認開始');
                    log_info('会員登録確認完了');

                    return $this->render(
                        'Shopping/regist_confirm.twig',
                        [
                            'form' => $form->createView(),
                            'Page' => $this->pageRepository->getPageByRoute('shopping_regist_confirm'),
                        ]
                    );

                case 'complete':
                    log_info('会員登録開始');

                    $encoder = $this->encoderFactory->getEncoder($Customer);
                    $salt = $encoder->createSalt();
                    $password = $encoder->encodePassword($Customer->getPassword(), $salt);
                    $secretKey = $this->customerRepository->getUniqueSecretKey();

                    $Customer
                        ->setSalt($salt)
                        ->setPassword($password)
                        ->setSecretKey($secretKey)
                        ->setPoint(0);

                    $this->entityManager->persist($Customer);
                    $this->entityManager->flush();

                    log_info('会員登録完了');

                    $event = new EventArgs(
                        [
                            'form' => $form,
                            'Customer' => $Customer,
                        ],
                        $request
                    );
                    $this->eventDispatcher->dispatch(EccubeEvents::FRONT_ENTRY_INDEX_COMPLETE, $event);

                    $activateFlg = $this->BaseInfo->isOptionCustomerActivate();

                    // 仮会員設定が有効な場合は、確認メールを送信し完了画面表示.
                    if ($activateFlg) {
                        $activateUrl = $this->generateUrl('entry_activate', ['secret_key' => $Customer->getSecretKey()], UrlGeneratorInterface::ABSOLUTE_URL);

                        // メール送信
                        $this->mailService->sendCustomerConfirmMail($Customer, $activateUrl);

                        if ($event->hasResponse()) {
                            return $event->getResponse();
                        }

                        log_info('仮会員登録完了画面へリダイレクト');

                        return $this->redirectToRoute('shopping_regist_complete');
                    } else {
                        // 仮会員設定が無効な場合は、会員登録を完了させる.
                        $qtyInCart = $this->entryActivate($request, $Customer->getSecretKey());

                        // URLを変更するため完了画面にリダイレクト
                        return $this->redirectToRoute('entry_activate', [
                            'secret_key' => $Customer->getSecretKey(),
                            'qtyInCart' => $qtyInCart,
                        ]);
                    }
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    // /**
    //  * 登録確認画面.
    //  *
    //  * @Route("/shopping/regist_confirm", name="shopping_regist_confirm", methods={"GET"})
    //  * @Template("Shopping/regist_confirm.twig")
    //  */
    // public function regist_confirm(Request $request, AuthenticationUtils $authenticationUtils)
    // {
    //     return[];
    // }

    /**
     * 登録完了画面.
     *
     * @Route("/shopping/regist_complete", name="shopping_regist_complete", methods={"GET"})
     * @Template("Shopping/regist_complete.twig")
     */
    public function regist_complete(Request $request, AuthenticationUtils $authenticationUtils)
    {
        return[];
    }

    /**
     * 宅配便受け取り画面.
     *
     * @Route("/shopping/receive_delivery", name="shopping_receive_delivery", methods={"GET"})
     * @Template("Shopping/receive_delivery.twig")
     */
    public function receive_delivery(Request $request)
    {
        //
        // ログイン状態のチェック.
        if ($this->orderHelper->isLoginRequired()) {
            return $this->redirectToRoute('shopping_login');
        }

        $Cart = $this->cartService->getCart();

        if (!($Cart && $this->orderHelper->verifyCart($Cart))) {
            return $this->redirectToRoute('homepage');
        }

        $CartItem = $Cart->getCartItems();

        // 受注の初期化.
        log_info('[注文手続] 受注の初期化処理を開始します.');
        $Customer = $this->getUser() ? $this->getUser() : $this->orderHelper->getNonMember();
        $Order = $this->orderHelper->initializeOrder($Cart, $Customer);

        // 集計処理.
        log_info('[注文手続] 集計処理を開始します.', [$Order->getId()]);
        $flowResult = $this->executePurchaseFlow($Order, false);
        $this->entityManager->flush();

        if ($flowResult->hasError()) {
            log_info('[注文手続] Errorが発生したため購入エラー画面へ遷移します.', [$flowResult->getErrors()]);

            return $this->redirectToRoute('shopping_error');
        }

        if ($flowResult->hasWarning()) {
            log_info('[注文手続] Warningが発生しました.', [$flowResult->getWarning()]);

            // 受注明細と同期をとるため, CartPurchaseFlowを実行する
            $cartPurchaseFlow->validate($Cart, new PurchaseContext($Cart, $this->getUser()));

            // 注文フローで取得されるカートの入れ替わりを防止する
            // @see https://github.com/EC-CUBE/ec-cube/issues/4293
            $this->cartService->setPrimary($Cart->getCartKey());
        }

        // マイページで会員情報が更新されていれば, Orderの注文者情報も更新する.
        if ($Customer->getId()) {
            $this->orderHelper->updateCustomerInfo($Order, $Customer);
            $this->entityManager->flush();
        }

        $DeliveryItem = new DeliveryItem();
        $form = $this->createForm(DeliveryType::class, $DeliveryItem);


        // $CartItem = $this->session->get('new_cart_item');
        $CartItem = $CartItem[count($CartItem)-1];
        
        return [
            'form' => $form->createView(),
            // 'form' => $form1->createView(),
            'Customer' => $this->getUser(),
            'Order' => $Order,
            'cartItem' => $CartItem,
        ];
    }

    /**
     * コンビニ受け取る受け取り画面.
     *
     * @Route("/shopping/receive_convenience", name="shopping_receive_convenience", methods={"GET"})
     * @Template("Shopping/receive_convenience.twig")
     */
    public function receive_convenience(Request $request)
    {
        $sid = $request->get('sid');
        $sname = $request->get('sname');
        $saddr = $request->get('saddr');
        $spt = $request->get('spt');
        $stflg = $request->get('stflg');
        $uid = $request->get('uid');
        $sjarea = $request->get('sjarea');
        $szcd = $request->get('szcd');
        $stel = $request->get('stel');
        $scode = $request->get('scode');
        $spare3 = $request->get('spare3');
        $spare4 = $request->get('spare4');
        $spare10 = $request->get('spare10');


        $sname = mb_convert_encoding($sname, "UTF-8", "EUC-JP");
        $spare3 = mb_convert_encoding($spare3, "UTF-8", "EUC-JP");
        $spare4 = mb_convert_encoding($spare4, "UTF-8", "EUC-JP");
        $spare10 = mb_convert_encoding($spare10, "UTF-8", "EUC-JP");
        $saddr = mb_convert_encoding($saddr, "UTF-8", "EUC-JP");

        return [
            'sid' => $sid,
            'sname' => $sname,
            'saddr' => $saddr,
            'spt' => $spt,
            'stflg' => $stflg,
            'uid' => $uid,
            'sjarea' => $sjarea,
            'szcd' => $szcd,
            'stel' => $stel,
            'scode' => $scode,
            'spare3' => $spare3,
            'spare4' => $spare4,
            'spare10' => $spare10
        ];
    }

    /**
     * 店頭受け取リ画面.
     *
     * @Route("/shopping/receive_office", name="shopping_receive_office", methods={"GET"})
     * @Template("Shopping/receive_office.twig")
     */
    public function receive_office(Request $request, AuthenticationUtils $authenticationUtils)
    {
        // ログイン状態のチェック.
        if ($this->orderHelper->isLoginRequired()) {
            return $this->redirectToRoute('shopping_login');
        }

        $Cart = $this->cartService->getCart();

        if (!($Cart && $this->orderHelper->verifyCart($Cart))) {
            return $this->redirectToRoute('homepage');
        }

        $CartItem = $Cart->getCartItems();
        // $CartItem = $this->session->get('new_cart_item');
        $CartItem = $CartItem[count($CartItem)-1];

        return[
            'cartItem' => $CartItem,
        ];
    }

    /**
     * クレジットカードお支払画面.
     *
     * @Route("/shopping/payment_creditcard", name="shopping_payment_creditcard", methods={"GET"})
     * @Template("Shopping/payment_creditcard.twig")
     */
    public function payment_creditcard(Request $request, AuthenticationUtils $authenticationUtils)
    {
        // ログイン状態のチェック.
        if ($this->orderHelper->isLoginRequired()) {
            return $this->redirectToRoute('shopping_login');
        }

        // 受注の存在チェック
        $preOrderId = $this->cartService->getPreOrderId();
        $Order = $this->orderHelper->getPurchaseProcessingOrder($preOrderId);
        if (!$Order) {
            log_info('[注文確認] 購入処理中の受注が存在しません.', [$preOrderId]);

            return $this->redirectToRoute('shopping_error');
        }

        return[
            'Order' => $Order,
        ];
    }

    /**
     * クレジットカード入力画面.
     *
     * @Route("/shopping/payment_creditcard_input", name="shopping_payment_creditcard_input", methods={"GET"})
     * @Template("Shopping/payment_creditcard_input.twig")
     */
    public function payment_creditcard_input(Request $request, AuthenticationUtils $authenticationUtils)
    {
        // ログイン状態のチェック.
        if ($this->orderHelper->isLoginRequired()) {
            return $this->redirectToRoute('shopping_login');
        }

        $Cart = $this->cartService->getCart();

        if (!($Cart && $this->orderHelper->verifyCart($Cart))) {
            return $this->redirectToRoute('homepage');
        }

        $CartItem = $Cart->getCartItems();
        // $CartItem = $this->session->get('new_cart_item');
        $CartItem = $CartItem[count($CartItem)-1];

        return[
            'cartItem' => $CartItem,
        ];
    }

    /**
     * 有料支払い画面.
     *
     * @Route("/shopping/payment_paidy", name="shopping_payment_paidy", methods={"GET"})
     * @Template("Shopping/payment_paidy.twig")
     */
    public function payment_paidy(Request $request, AuthenticationUtils $authenticationUtils)
    {
        // ログイン状態のチェック.
        if ($this->orderHelper->isLoginRequired()) {
            return $this->redirectToRoute('shopping_login');
        }

        $Cart = $this->cartService->getCart();

        if (!($Cart && $this->orderHelper->verifyCart($Cart))) {
            return $this->redirectToRoute('homepage');
        }

        $CartItem = $Cart->getCartItems();
        // $CartItem = $this->session->get('new_cart_item');
        $CartItem = $CartItem[count($CartItem)-1];

        return[
            'cartItem' => $CartItem,
        ];
    }

    /**
     * 注⽂確認画面.
     *
     * @Route("/shopping/confirm_check", name="shopping_confirm_check", methods={"GET"})
     * @Template("Shopping/confirm.twig")
     */
    public function confirm_check(Request $request, AuthenticationUtils $authenticationUtils)
    {
        // ログイン状態のチェック.
        if ($this->orderHelper->isLoginRequired()) {
            return $this->redirectToRoute('shopping_login');
        }

        $Cart = $this->cartService->getCart();

        if (!($Cart && $this->orderHelper->verifyCart($Cart))) {
            return $this->redirectToRoute('homepage');
        }

        $CartItem = $Cart->getCartItems();
        // $CartItem = $this->session->get('new_cart_item');
        $CartItem = $CartItem[count($CartItem)-1];

        return[
            'cartItem' => $CartItem,
        ];
    }

    /**
     * オプション画面.
     *
     * @Route("/shopping/option", name="shopping_option", methods={"GET", "POST"})
     * @Template("Shopping/option.twig")
     */
    public function option(Request $request)
    {
        $Cart = $this->cartService->getCart();
        return[
            'Cart' => $Cart,
        ];
    }

    

    /**
     * 購入エラー画面.
     *
     * @Route("/shopping/error", name="shopping_error", methods={"GET"})
     * @Template("Shopping/shopping_error.twig")
     */
    public function error(Request $request, PurchaseFlow $cartPurchaseFlow)
    {
        // 受注とカートのずれを合わせるため, カートのPurchaseFlowをコールする.
        $Cart = $this->cartService->getCart();
        if (null !== $Cart) {
            $cartPurchaseFlow->validate($Cart, new PurchaseContext($Cart, $this->getUser()));
            $this->cartService->setPreOrderId(null);
            $this->cartService->save();
        }

        $event = new EventArgs(
            [],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_SHOPPING_SHIPPING_ERROR_COMPLETE, $event);

        if ($event->getResponse() !== null) {
            return $event->getResponse();
        }

        return [];
    }

    /**
     * PaymentMethodをコンテナから取得する.
     *
     * @param Order $Order
     * @param FormInterface $form
     *
     * @return PaymentMethodInterface
     */
    private function createPaymentMethod(Order $Order, FormInterface $form)
    {
        $PaymentMethod = $this->container->get($Order->getPayment()->getMethodClass());
        $PaymentMethod->setOrder($Order);
        $PaymentMethod->setFormType($form);

        return $PaymentMethod;
    }

    /**
     * PaymentMethod::applyを実行する.
     *
     * @param PaymentMethodInterface $paymentMethod
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    protected function executeApply(PaymentMethodInterface $paymentMethod)
    {
        $dispatcher = $paymentMethod->apply(); // 決済処理中.

        // リンク式決済のように他のサイトへ遷移する場合などは, dispatcherに処理を移譲する.
        if ($dispatcher instanceof PaymentDispatcher) {
            $response = $dispatcher->getResponse();
            $this->entityManager->flush();

            // dispatcherがresponseを保持している場合はresponseを返す
            if ($response instanceof Response && ($response->isRedirection() || $response->isSuccessful())) {
                log_info('[注文処理] PaymentMethod::applyが指定したレスポンスを表示します.');

                return $response;
            }

            // forwardすることも可能.
            if ($dispatcher->isForward()) {
                log_info('[注文処理] PaymentMethod::applyによりForwardします.',
                    [$dispatcher->getRoute(), $dispatcher->getPathParameters(), $dispatcher->getQueryParameters()]);

                return $this->forwardToRoute($dispatcher->getRoute(), $dispatcher->getPathParameters(),
                    $dispatcher->getQueryParameters());
            } else {
                log_info('[注文処理] PaymentMethod::applyによりリダイレクトします.',
                    [$dispatcher->getRoute(), $dispatcher->getPathParameters(), $dispatcher->getQueryParameters()]);

                return $this->redirectToRoute($dispatcher->getRoute(),
                    array_merge($dispatcher->getPathParameters(), $dispatcher->getQueryParameters()));
            }
        }
    }

    /**
     * PaymentMethod::checkoutを実行する.
     *
     * @param PaymentMethodInterface $paymentMethod
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response|null
     */
    protected function executeCheckout(PaymentMethodInterface $paymentMethod)
    {
        $PaymentResult = $paymentMethod->checkout();
        $response = $PaymentResult->getResponse();
        // PaymentResultがresponseを保持している場合はresponseを返す
        if ($response instanceof Response && ($response->isRedirection() || $response->isSuccessful())) {
            $this->entityManager->flush();
            log_info('[注文処理] PaymentMethod::checkoutが指定したレスポンスを表示します.');

            return $response;
        }

        // エラー時はロールバックして購入エラーとする.
        if (!$PaymentResult->isSuccess()) {
            $this->entityManager->rollback();
            foreach ($PaymentResult->getErrors() as $error) {
                $this->addError($error);
            }

            log_info('[注文処理] PaymentMethod::checkoutのエラーのため, 購入エラー画面へ遷移します.', [$PaymentResult->getErrors()]);

            return $this->redirectToRoute('shopping_error');
        }

        return null;
    }
}
