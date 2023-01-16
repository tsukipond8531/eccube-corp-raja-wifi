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
use Eccube\Entity\BaseInfo;
use Eccube\Entity\Customer;
use Eccube\Entity\Order;
use Eccube\Entity\Product;
use Eccube\Entity\ProductClass;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Exception\CartException;
use Eccube\Form\Type\Shopping\OrderItemType;
use Eccube\Entity\OrderItem;
use Eccube\Form\Type\Front\CustomerLoginType;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\CustomerFavoriteProductRepository;
use Eccube\Repository\OrderRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Repository\ProductClassRepository;
use Eccube\Service\CartService;
use Eccube\Service\PurchaseFlow\PurchaseContext;
use Eccube\Service\PurchaseFlow\PurchaseFlow;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class RouterController extends AbstractController
{
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var CustomerFavoriteProductRepository
     */
    protected $customerFavoriteProductRepository;


    /**
     * @var ProductClassRepository
     */
    protected $productClassRepository;

    /**
     * @var BaseInfo
     */
    protected $BaseInfo;

    /**
     * @var CartService
     */
    protected $cartService;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var PurchaseFlow
     */
    protected $purchaseFlow;

    /**
     * MypageController constructor.
     *
     * @param OrderRepository $orderRepository
     * @param ProductRepository $productRepository
     * @param ProductClassRepository $productClassRepository
     * @param CustomerFavoriteProductRepository $customerFavoriteProductRepository
     * @param CartService $cartService
     * @param BaseInfoRepository $baseInfoRepository
     * @param PurchaseFlow $purchaseFlow
     */
    public function __construct(
        OrderRepository $orderRepository,
        ProductRepository $productRepository,
        ProductClassRepository $productClassRepository,
        CustomerFavoriteProductRepository $customerFavoriteProductRepository,
        CartService $cartService,
        BaseInfoRepository $baseInfoRepository,
        PurchaseFlow $purchaseFlow
    ) {
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->productClassRepository = $productClassRepository;
        $this->customerFavoriteProductRepository = $customerFavoriteProductRepository;
        $this->BaseInfo = $baseInfoRepository->get();
        $this->cartService = $cartService;
        $this->purchaseFlow = $purchaseFlow;
    }

    /**
     * レンタル中のWiFiルーターを編集する.
     *
     * @Route("/mypage/router/detail/{order_id}", name="mypage_router_detail", methods={"GET"})
     * @Template("Mypage/router_detail.twig")
     */
    public function detail(Request $request, $order_id)
    {
        $this->entityManager->getFilters()
            ->enable('incomplete_order_status_delivered_hidden');
        $Order = $this->orderRepository->findOneBy(
            [
                'id' => $order_id,
                'Customer' => $this->getUser(),
            ]
        );

        $event = new EventArgs(
            [
                'Order' => $Order,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_MYPAGE_MYPAGE_HISTORY_INITIALIZE, $event);

        /** @var Order $Order */
        $Order = $event->getArgument('Order');

        if (!$Order) {
            throw new NotFoundHttpException();
        }
        
        return [
            'Order' => $Order,
        ];
    }

    /**
     * レンタル中のWiFiルーター編集内容を確認する.
     *
     * @Route("/mypage/router/change/{order_id}", name="mypage_router_change", methods={"GET"})
     * @Template("Mypage/router_change.twig")
     */
    public function change(Request $request, $order_id)
    {
        $this->entityManager->getFilters()
            ->enable('incomplete_order_status_delivered_hidden');

        $Order = $this->orderRepository->findOneBy(
            [
                'id' => $order_id,
                'Customer' => $this->getUser(),
            ]
        );

        $event = new EventArgs(
            [
                'Order' => $Order,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_MYPAGE_MYPAGE_HISTORY_INITIALIZE, $event);

        /** @var Order $Order */
        $Order = $event->getArgument('Order');

        if (!$Order) {
            throw new NotFoundHttpException();
        }
        
        // オプション商品のProductClassを取得
        $ProductList = $this->productRepository->findBy(['id'=>array(3,4,5,6,7)]);
        $ProductClasses = array();
        foreach ($ProductList as $product) {
            $has_class = $product->hasProductClass();
            if (!$has_class) {
                $ProductClasses[] = $product->getProductClasses()[0];
            }
        }
        return [
            'Order' => $Order,
            'ProductClasses' => $ProductClasses,
        ];
    }

    /**
     * レンタル中のWiFiルーター編集内容を確認する.
     *
     * @Route("/mypage/router/confirm/{order_id}", name="mypage_router_confirm", methods={"GET", "POST"})
     * @Template("Mypage/router_confirm.twig")
     */
    public function conform(Request $request, $order_id)
    {
        $this->entityManager->getFilters()
            ->enable('incomplete_order_status_delivered_hidden');

        $Order = $this->orderRepository->findOneBy(
            [
                'id' => $order_id,
                'Customer' => $this->getUser(),
            ]
        );

        if ($request->getMethod() == 'POST') {
            $index = 1;
            $total_days = 0;
            $security_option = $request->get('option_order_product_id');
			$order_sub_total = $request->get('order_sub_total');
            foreach ($Order->getOrderItems() as $OrderItem) {
                if ($OrderItem->isProduct()) {
                    $Product = $OrderItem->getProduct();
                    $ProductClass = $OrderItem->getProductClass();
                    if ($Product->getId() >  7) {
                        $country = $request->get('order_country_'.$index);
                        $country_code = $request->get('order_country_code_'.$index);
                        $startdate = $request->get('order_startdate_'.$index);
                        $enddate = $request->get('order_enddate_'.$index);
                        $price = round($request->get('order_price_'.$index) / 1.1, 2);
                        $quantity = $request->get('order_quantity_'.$index);

                        $product_name = $country.'_'.substr($startdate, 2).'_'.substr($enddate, 2).'_'.$country_code;

                        // update Product
                        $Product->setName($product_name);
                        $this->entityManager->persist($Product);
                        $this->entityManager->flush();

                        // update ProductClass
                        $ProductClass->setPrice01($price);
                        $ProductClass->setPrice02($price + $quantity * 200);
                        $this->entityManager->persist($ProductClass);
                        $this->entityManager->flush();

                        // update OrderItem
                        $OrderItem->setProductName($product_name);
                        $OrderItem->setPrice($price + $quantity * 200);
                        $OrderItem->setTax(($price + $quantity * 200) / 10);
                        $this->entityManager->persist($OrderItem);
                        $this->entityManager->flush();
                        $total_days += $quantity;
                        
                        $index++;
                    }

                    if ($Product->getId() >=3 && $Product->getId() <= 5) {
                        $SecurityProduct = $this->productRepository->findOneBy(['id' => $security_option]);
                        if (!$SecurityProduct) {
                            throw new NotFoundHttpException();
                        }
                        $OrderItem->setProduct($SecurityProduct);

                        $has_class = $SecurityProduct->hasProductClass();
                        if (!$has_class) {
                            $SecurityProductClass = $SecurityProduct->getProductClasses()[0];
                        }
                        $OrderItem->setProductClass($SecurityProductClass);
                        $OrderItem->setPrice($SecurityProductClass->getPrice02());
                        $OrderItem->setTax($SecurityProductClass->getPrice02() / 10);
                        $OrderItem->setQuantity($total_days);
                        $OrderItem->setProductName($SecurityProduct->getName());
                        $this->entityManager->persist($OrderItem);
                        $this->entityManager->flush();
                    }
                }
            }
			$Order->setSubtotal($order_sub_total);
            $Order->setTax($order_sub_total / 10);
            $Order->setPaymentTotal($order_sub_total + $Order->getDeliveryFeeTotal());
			$this->entityManager->persist($Order);
            $this->entityManager->flush();
        }
        

        return [
            'Order' => $Order
        ];
    }

    /**
     * レンタル中のWiFiルーター編集を完了する.
     *
     * @Route("/mypage/router/complete", name="mypage_router_complete", methods={"GET"})
     * @Template("Mypage/router_complete.twig")
     */
    public function complete()
    {
        return [];
    }

    /**
     * 購⼊履歴を表示する.
     *
     * @Route("/mypage/router/history", name="mypage_router_history", methods={"GET"})
     * @Template("Mypage/router_history.twig")
     */
    public function history(Request $request, PaginatorInterface $paginator)
    {
        $Customer = $this->getUser();

        // 発送済みステータスの受注だけを表示にする.
        $this->entityManager
            ->getFilters()
            ->enable('incomplete_order_status_delivered_show');

        // paginator
        $qb = $this->orderRepository->getQueryBuilderByCustomer($Customer);

        $event = new EventArgs(
            [
                'qb' => $qb,
                'Customer' => $Customer,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_MYPAGE_MYPAGE_INDEX_SEARCH, $event);

        $pagination = $paginator->paginate(
            $qb,
            $request->get('pageno', 1),
            $this->eccubeConfig['eccube_search_pmax']
        );

        return [
            'pagination' => $pagination,
        ];
    }
}
