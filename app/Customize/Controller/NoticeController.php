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

use Eccube\Repository\OrderRepository;
use Eccube\Service\MailService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Eccube\Entity\BaseInfo;
use Symfony\Component\Routing\Annotation\Route;
use Eccube\Controller\AbstractController;
use Eccube\Entity\Product;
use Eccube\Entity\ProductCategory;
use Eccube\Repository\ProductClassRepository;
use Eccube\Entity\ProductClass;
use Eccube\Entity\ProductImage;
use Eccube\Entity\ProductStock;
use Eccube\Entity\ProductTag;
use Eccube\Entity\Master\ProductStatus;
use Eccube\Repository\Master\ProductStatusRepository;
use Eccube\Repository\Master\SaleTypeRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Service\PurchaseFlow\PurchaseContext;
use Eccube\Service\PurchaseFlow\PurchaseFlow;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Eccube\Entity\Master\SaleType;
use Eccube\Service\CartService;

class NoticeController extends AbstractController
{

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    protected $mailService;

    /**
     * ProductController constructor.
     *
     * @param OrderRepository $orderRepository
     * @param MailService $mailService
     */

    public function __construct(
        OrderRepository $orderRepository,
        MailService $mailService
    ) {
        $this->orderRepository = $orderRepository;
        $this->mailService = $mailService;
    }

    /**
     * @Route("/notice/order_complete", name="notice_order_complete", methods={"GET", "POST"})
     */
    public function sendNoticeBeforeComplete()
    {

        $Orders = $this->orderRepository->findAll();

        $date = date_add(date_create(date('Y-m-d')), date_interval_create_from_date_string("1 days"));
        $tomorrow_date = date_format($date,"Y-m-d");
        
        foreach($Orders as $Order){
            $OrderItems = $Order->getOrderItems();

            $complete_date = '';
            $complete_date_string = '';

            foreach($OrderItems as $OrderItem){
                if (!$OrderItem->isProduct()) continue;
                $Product = $OrderItem->getProduct();
                $rental_end_date = $Product->getRentalEnd();
                if (empty($rental_end_date)) continue;
                
                $end_date = date_format($rental_end_date,"Y-m-d");

                if ($end_date > $complete_date || empty($complete_date)){
                    $complete_date = $end_date;
                    $complete_date_string = date_format($rental_end_date,"Y年m月d日");
                } 
            }

            if(empty($complete_date)) continue;

            if($complete_date != $tomorrow_date) continue;

            $this->mailService->sendNoticeOrderComplete($Order, $complete_date_string);
        }
        
        return $this->json(['status' => 'OK']);
    }

}
