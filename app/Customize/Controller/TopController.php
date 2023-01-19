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

class TopController extends AbstractController
{

	/**
     * @var ProductStatusRepository
     */
    protected $productStatusRepository;

    /**
     * @var ProductClassRepository
     */
    protected $productClassRepository;
    
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var DeliveryTimeRepository
     */
    protected $saleTypeRepository;
    /**
     * @var CartService
     */
    protected $cartService;

    /**
     * @var BaseInfo
     */
    protected $BaseInfo;

    /**
     * ProductController constructor.
     *
     * @param ProductRepository $productRepository
     * @param ProductStatusRepository $productStatusRepository
     * @param ProductClassRepository $productClassRepository
     * @param DeliveryTimeRepository $saleTypeRepository
     * @param BaseInfoRepository $baseInfoRepository
     * @param CartService $cartService
     * @param PurchaseFlow $cartPurchaseFlow
     */

    public function __construct(
    	PurchaseFlow $cartPurchaseFlow,
        ProductRepository $productRepository,
        ProductClassRepository $productClassRepository,
        ProductStatusRepository $productStatusRepository,
        BaseInfoRepository $baseInfoRepository,
        SaleTypeRepository $saleTypeRepository,
        CartService $cartService
    ) {
    	$this->purchaseFlow = $cartPurchaseFlow;
        $this->productClassRepository = $productClassRepository;
        $this->productRepository = $productRepository;
        $this->productStatusRepository = $productStatusRepository;
        $this->saleTypeRepository = $saleTypeRepository;
        $this->BaseInfo = $baseInfoRepository->get();
        $this->cartService = $cartService;
    }

	/**
     * @Route("/", name="homepage", methods={"GET", "POST"})
     * @Template("index.twig")
     */
    public function index()
    {
        $Cart = $this->cartService->getCart();
        return [
        	'Cart' => $Cart,
        ];
    }

	/**
     * @Route("/", name="user_data", methods={"GET", "POST"})
     * @Template("index.twig")
     */
    public function userData()
    {
        $Cart = $this->cartService->getCart();
        return [
        	'Cart' => $Cart,
        ];
    }

    /**
     * Move rank with ajax.
     *
     * @param Request     $request
     *
     * @throws \Exception
     *
     * @return JsonResponse
     *
     * @Route("/get_cart", name="get_cart")
     */
    public function get_cart()
    {
        $Cart = $this->cartService->getCart();
        $res = [];
        if (null !== $Cart) {
            foreach($Cart->getCartItems() as $CartItem) {
                $ProductClass = $CartItem->getProductClass();
                $Product = $ProductClass->getProduct();
                if ($Product->getId() > 7) {
                    $item = array(
                        'id' => $ProductClass->getId(),
                        'name' => $Product->getName(),
                        'rental' => round($ProductClass->getPrice01() * 1.1, 0),
                        'total' => round($ProductClass->getPrice02() * 1.1, 0)
                    );
                    array_push($res, $item);
                }
            }
        }
        
        return new JsonResponse($res);
        
    }

    /**
     * Move rank with ajax.
     *
     * @param Request     $request
     *
     * @throws \Exception
     *
     * @return Response
     *
     * @Route("/add_product", name="add_product")
     */
    public function add_product(Request $request)
    {

    	if ($request->isXmlHttpRequest() && $request->getMethod() == 'POST') {
    		$product_id = $request->get('product_id');
            $index = $request->get('index');
            $from = $request->get('from');
            $from_date = date('y/m/d', $from / 1000);
            $to = $request->get('to');
            $to_date = date('y/m/d', $to / 1000);
            $country_name_jp = $request->get('country');
            $country_code = $request->get('country_code');
            $des_price = round($request->get('price') / 1.1, 2);
            $days = round(($to - $from) / (1000 * 60 * 60 * 24));
            $daily_price = $days * 200;
            $total_price = $daily_price + $des_price;

    		
    		if (!empty($product_id) && !is_null($product_id)) {
    			//update Product
    			$Product = $this->productRepository->findWithSortedClassCategories($product_id);
    			$Product->setName($country_name_jp.'_'.$index.'_'.$from_date.'_'.$to_date.'_'.$country_code);
                $Product->setRentalStart(new \DateTime('20'.$from_date));
                $Product->setRentalEnd(new \DateTime('20'.$to_date));
    			$this->entityManager->persist($Product);
	            $this->entityManager->flush();

	            $ProductClass = null;
	            $ProductStock = null;
	            if (!$Product) {
	                throw new NotFoundHttpException();
	            }
	            // 規格無しの商品の場合は、デフォルト規格を表示用に取得する
	            $has_class = $Product->hasProductClass();
	            if (!$has_class) {
	                $ProductClasses = $Product->getProductClasses();
	                foreach ($ProductClasses as $pc) {
	                    if (!is_null($pc->getClassCategory1())) {
	                        continue;
	                    }
	                    if ($pc->isVisible()) {
	                        $ProductClass = $pc;
	                        break;
	                    }
	                }
	                if ($this->BaseInfo->isOptionProductTaxRule() && $ProductClass->getTaxRule()) {
	                    $ProductClass->setTaxRate($ProductClass->getTaxRule()->getTaxRate());
	                }
	                $ProductStock = $ProductClass->getProductStock();
	            }
	            $ProductClass
	                ->setPrice01($des_price)
	                ->setPrice02($total_price)
	                ->setProduct($Product);
	            $this->entityManager->persist($ProductClass);
	            $this->entityManager->flush();

	            $ProductStock->setProductClass($ProductClass);
	            $this->entityManager->persist($ProductStock);
	            $this->entityManager->flush();
    		} else {
    			//register Product
	            $Product = new Product();
	            $Product->setStatus($this->productStatusRepository->find(ProductStatus::DISPLAY_SHOW));
	            $Product->setName($country_name_jp.'_'.$index.'_'.$from_date.'_'.$to_date.'_'.$country_code);
                $Product->setRentalStart(new \DateTime('20'.$from_date));
                $Product->setRentalEnd(new \DateTime('20'.$to_date));
				$this->entityManager->persist($Product);
                $this->entityManager->flush();

	            //register ProductStock
	            $ProductStock = new ProductStock();
	            // // 在庫無制限時はnullを設定
	            $ProductStock->setStock(null);
	            $this->entityManager->persist($ProductStock);
	            $this->entityManager->flush();

	            //register ProductClass
	            $ProductClass = new ProductClass();
	            // $ProductStatus = $this->productStatusRepository->find(ProductStatus::DISPLAY_HIDE);
	            $SaleType = $this->saleTypeRepository->findOneBy([], ['sort_no' => 'ASC']);
	            $ProductClass
	                ->setVisible(true)
	                ->setStockUnlimited(true)
	                ->setSaleType($SaleType)
	                ->setPrice01($des_price)
	                ->setPrice02($total_price)
	                ->setProduct($Product);

	            $ProductClass->setProductStock($ProductStock);
	            $this->entityManager->persist($ProductClass);
	            $this->entityManager->flush();

	            $ProductStock->setProductClass($ProductClass);
	            $this->entityManager->persist($ProductStock);
	            $this->entityManager->flush();

	            // log_info('商品登録完了', [$id]);

	            //add Product into Cart
	            if (!$ProductClass instanceof ProductClass) {
		            $ProductClassId = $ProductClass;
		            $ProductClass = $this->entityManager
		                ->getRepository(ProductClass::class)
		                ->find($ProductClassId);
		            if (is_null($ProductClass)) {
		                return new Response('fail');
		            }
		        }

		        $quantity = 1;
		        // カートへ追加
		        $this->cartService->addProduct($ProductClass, $quantity);
    		}
            

	        // 明細の正規化
	        $Carts = $this->cartService->getCarts();
	        foreach ($Carts as $Cart) {
	            $result = $this->purchaseFlow->validate($Cart, new PurchaseContext($Cart, $this->getUser()));
	            // 復旧不可のエラーが発生した場合は追加した明細を削除.
	            if ($result->hasError()) {
	                $this->cartService->removeProduct($ProductClassId);
	                foreach ($result->getErrors() as $error) {
	                    $errorMessages[] = $error->getMessage();
	                }
	            }
	            foreach ($result->getWarning() as $warning) {
	                $errorMessages[] = $warning->getMessage();
	            }
	        }

	        $this->cartService->save();

            return new Response($ProductClass->getId());
        }
        
        return new Response('fail');
    }

    /**
     * Move rank with ajax.
     *
     * @param Request     $request
     *
     * @throws \Exception
     *
     * @return JsonResponse
     *
     * @Route("/add_products", name="add_products")
     */
    public function add_products(Request $request)
    {
        $pre_order_id = $this->cartService->getPreOrderId();
        $res = 'fail';
        if ($request->isXmlHttpRequest() && $request->getMethod() == 'POST') {
            $cart_data = $request->get('cart_data');
            if (!empty($cart_data)) {
                $json = json_decode($cart_data);
                foreach ($json as $item) {
                    if (!empty($item->productclass_id) && !is_null($item->productclass_id)) {

                        //update Product
                        $ProductClass = $this->productClassRepository->find($item->productclass_id);
                        $ProductStock = $ProductClass->getProductStock();
                        $Product = $ProductClass->getProduct();

                        $Product->setName($item->country_name.'_'.substr($item->from_date, 2).'_'.substr($item->to_date, 2).'_'.$item->country_code);
                        $Product->setRentalStart(new \DateTime($item->from_date));
                        $Product->setRentalEnd(new \DateTime($item->to_date));
                        $this->entityManager->persist($Product);
                        $this->entityManager->flush();

                        $ProductClass
                            ->setPrice01(round($item->country_price * $item->date_count / 1.1, 2))
                            ->setPrice02((round($item->country_price / 1.1, 2) + 200) * $item->date_count)
                            ->setProduct($Product);
                        $this->entityManager->persist($ProductClass);
                        $this->entityManager->flush();

                        $ProductStock->setProductClass($ProductClass);
                        $this->entityManager->persist($ProductStock);
                        $this->entityManager->flush();
                    } else {
                        //register Product
                        $Product = new Product();
                        $Product->setStatus($this->productStatusRepository->find(ProductStatus::DISPLAY_SHOW));
                        $Product->setName($item->country_name.'_'.substr($item->from_date, 2).'_'.substr($item->to_date, 2).'_'.$item->country_code);
                        $Product->setRentalStart(new \DateTime($item->from_date));
                        $Product->setRentalEnd(new \DateTime($item->to_date));

                        // 商品オプションと区別するためのフラグを設定
                        $Product->setBasic(true);
                        
                        $this->entityManager->persist($Product);
                        $this->entityManager->flush();

                        //register ProductStock
                        $ProductStock = new ProductStock();
                        // // 在庫無制限時はnullを設定
                        $ProductStock->setStock(null);
                        $this->entityManager->persist($ProductStock);
                        $this->entityManager->flush();

                        //register ProductClass
                        $ProductClass = new ProductClass();
                        // $ProductStatus = $this->productStatusRepository->find(ProductStatus::DISPLAY_HIDE);
                        $SaleType = $this->saleTypeRepository->findOneBy([], ['sort_no' => 'ASC']);
                        $ProductClass
                            ->setVisible(true)
                            ->setStockUnlimited(true)
                            ->setSaleType($SaleType)
                            ->setPrice01(round($item->country_price * $item->date_count / 1.1, 2))
                            ->setPrice02((round($item->country_price / 1.1, 2) + 200) * $item->date_count)
                            ->setProduct($Product);

                        $ProductClass->setProductStock($ProductStock);
                        $this->entityManager->persist($ProductClass);
                        $this->entityManager->flush();

                        $ProductStock->setProductClass($ProductClass);
                        $this->entityManager->persist($ProductStock);
                        $this->entityManager->flush();

                        // log_info('商品登録完了', [$id]);

                        //add Product into Cart
                        if (!$ProductClass instanceof ProductClass) {
                            $ProductClassId = $ProductClass;
                            $ProductClass = $this->entityManager
                                ->getRepository(ProductClass::class)
                                ->find($ProductClassId);
                            if (is_null($ProductClass)) {
                                return new JsonResponse($res);
                            }
                        }

                        $quantity = 1;
                        // カートへ追加
                        $this->cartService->addProduct($ProductClass, $quantity);
                    }
                }

                // 明細の正規化
                $Carts = $this->cartService->getCarts();
                foreach ($Carts as $Cart) {
                    $result = $this->purchaseFlow->validate($Cart, new PurchaseContext($Cart, $this->getUser()));
                    // 復旧不可のエラーが発生した場合は追加した明細を削除.
                    if ($result->hasError()) {
                        $this->cartService->removeProduct($ProductClassId);
                        foreach ($result->getErrors() as $error) {
                            $errorMessages[] = $error->getMessage();
                        }
                    }
                    foreach ($result->getWarning() as $warning) {
                        $errorMessages[] = $warning->getMessage();
                    }
                }
                $this->cartService->setPreOrderId($pre_order_id);
                $this->cartService->save();
            }
            $res = 'success';
            return new JsonResponse($res);
        }
        
        return new JsonResponse($res);
    }

    /**
     * Move rank with ajax.
     *
     * @param Request     $request
     *
     * @throws \Exception
     *
     * @return Response
     *
     * @Route("/delete_cartitem", name="delete_cartitem")
     */
    public function delete_cartitem(Request $request)
    {

       if ($request->isXmlHttpRequest() && $request->getMethod() == 'POST') {
            // $cartitem_id = $request->get('cartitem_id');
            // $product_id = $request->get('product_id');
            $productclass_id = $request->get('productclass_id');
            if (!empty($productclass_id) && !is_null($productclass_id)) {
                /** @var ProductClass $ProductClass */
                $ProductClass = $this->productClassRepository->find($productclass_id);


                $this->cartService->removeProduct($ProductClass);

                // カートを取得して明細の正規化を実行
                $Carts = $this->cartService->getCarts();
                $this->execPurchaseFlow($Carts);

                log_info('カート演算処理終了', ['operation' => 'remove', 'product_class_id' => $productclass_id]);

                return new Response("success");
            } else {
                return new Response('fail');
            }
       }
        
        return new Response('fail');
    }


    /**
     * @param $Carts
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|null
     */
    protected function execPurchaseFlow($Carts)
    {
        /** @var PurchaseFlowResult[] $flowResults */
        $flowResults = array_map(function ($Cart) {
            $purchaseContext = new PurchaseContext($Cart, $this->getUser());

            return $this->purchaseFlow->validate($Cart, $purchaseContext);
        }, $Carts);

        // 復旧不可のエラーが発生した場合はカートをクリアして再描画
        $hasError = false;
        foreach ($flowResults as $result) {
            if ($result->hasError()) {
                $hasError = true;
                foreach ($result->getErrors() as $error) {
                    $this->addRequestError($error->getMessage());
                }
            }
        }
        if ($hasError) {
            $this->cartService->clear();

            return $this->redirectToRoute('cart');
        }

        $this->cartService->save();

        foreach ($flowResults as $index => $result) {
            foreach ($result->getWarning() as $warning) {
                if ($Carts[$index]->getItems()->count() > 0) {
                    $cart_key = $Carts[$index]->getCartKey();
                    $this->addRequestError($warning->getMessage(), "front.cart.${cart_key}");
                } else {
                    // キーが存在しない場合はグローバルにエラーを表示する
                    $this->addRequestError($warning->getMessage());
                }
            }
        }

        return null;
    }

    /**
     * Move rank with ajax.
     *
     * @param Request     $request
     *
     * @throws \Exception
     *
     * @return Response
     *
     * @Route("/add_option_product", name="add_option_product")
     */
    public function add_option_product(Request $request)
    {
        $pre_order_id = $this->cartService->getPreOrderId();
        
        if ($request->isXmlHttpRequest() && $request->getMethod() == 'POST') {

            $security_option = $request->get('security_option');
            $option_product_1 = $request->get('option_product_1');
            $option_product_2 = $request->get('option_product_2');
            $pre_order_id = $request->get('pre_order_id');
            $SecurityProductId = 3;

            switch ($security_option) {
                case '1':
                    $SecurityProductId = 3;
                    break;
                case '2':
                    $SecurityProductId = 4;
                    break;
                case '3':
                    $SecurityProductId = 5;
                    break;
                default:
                    break;
            }

            $SecurityProduct = $this->productRepository->findWithSortedClassCategories($SecurityProductId);

            $SecurityProductClass = null;
            // 規格無しの商品の場合は、デフォルト規格を表示用に取得する
            $has_class = $SecurityProduct->hasProductClass();
            if (!$has_class) {
                $SecurityProductClasses = $SecurityProduct->getProductClasses();
                foreach ($SecurityProductClasses as $pc) {
                    if (!is_null($pc->getClassCategory1())) {
                        continue;
                    }
                    if ($pc->isVisible()) {
                        $SecurityProductClass = $pc;
                        break;
                    }
                }
                if ($this->BaseInfo->isOptionProductTaxRule() && $SecurityProductClass->getTaxRule()) {
                    $SecurityProductClass->setTaxRate($SecurityProductClass->getTaxRule()->getTaxRate());
                }
            }

            $Cart = $this->cartService->getCart();
            if ($pre_order_id != null) {
                $Cart->setPreOrderId($pre_order_id);
            }
            
            $total_days = 0;
            foreach ($Cart->getCartItems() as $CartItem) {
                $ProductClass = $CartItem->getProductClass();
                $Product = $ProductClass->getProduct();
                $str_name = $Product->getName();
                if (str_contains($str_name, '_')) {
                    $contents = explode("_", $str_name);
                    $from = $contents[1];
                    $to = $contents[2];
                    $days = round((strtotime('20'.$to) - strtotime('20'.$from)) / (60 * 60 * 24));
                    $total_days += $days;
                }
            }

            $quantity = strval($total_days);

            if (!$SecurityProductClass instanceof ProductClass) {
                $SecurityProductClassId = $SecurityProductClass;
                $SecurityProductClass = $this->entityManager
                    ->getRepository(ProductClass::class)
                    ->find($SecurityProductClassId);
                if (is_null($SecurityProductClass)) {
                    return new Response('fail');
                }
            }
            if ($pre_order_id != null) {
                $this->cartService->setPreOrderId($pre_order_id);
            }
            // カートへ追加
            $this->cartService->addProduct($SecurityProductClass, $quantity);

            // 明細の正規化

            $Carts = $this->cartService->getCarts();
            foreach ($Carts as $Cart) {
                $result = $this->purchaseFlow->validate($Cart, new PurchaseContext($Cart, $this->getUser()));
                // 復旧不可のエラーが発生した場合は追加した明細を削除.
                if ($result->hasError()) {
                    $this->cartService->removeProduct($ProductClassId);
                    foreach ($result->getErrors() as $error) {
                        $errorMessages[] = $error->getMessage();
                    }
                }
                foreach ($result->getWarning() as $warning) {
                    $errorMessages[] = $warning->getMessage();
                }
            }
            
            
            $this->cartService->save();

            if ($option_product_1 == '1') {
                $OptionProduct1Id = 6;
                $OptionProduct1 = $this->productRepository->findWithSortedClassCategories($OptionProduct1Id);

                $OptionProduct1Class = null;
                // 規格無しの商品の場合は、デフォルト規格を表示用に取得する
                $has_class = $SecurityProduct->hasProductClass();
                if (!$has_class) {
                    $OptionProduct1Classes = $OptionProduct1->getProductClasses();
                    foreach ($OptionProduct1Classes as $pc) {
                        if (!is_null($pc->getClassCategory1())) {
                            continue;
                        }
                        if ($pc->isVisible()) {
                            $OptionProduct1Class = $pc;
                            break;
                        }
                    }
                    if ($this->BaseInfo->isOptionProductTaxRule() && $OptionProduct1Class->getTaxRule()) {
                        $OptionProduct1Class->setTaxRate($OptionProduct1Class->getTaxRule()->getTaxRate());
                    }
                }

                if (!$OptionProduct1Class instanceof ProductClass) {
                    $OptionProduct1ClassId = $OptionProduct1Class;
                    $OptionProduct1Class = $this->entityManager
                        ->getRepository(ProductClass::class)
                        ->find($OptionProduct1ClassId);
                    if (is_null($OptionProduct1Class)) {
                        return new Response('fail');
                    }
                }

                // カートへ追加
                $this->cartService->addProduct($OptionProduct1Class, $quantity);

                // 明細の正規化
                $Carts = $this->cartService->getCarts();
                foreach ($Carts as $Cart) {
                    $result = $this->purchaseFlow->validate($Cart, new PurchaseContext($Cart, $this->getUser()));
                    // 復旧不可のエラーが発生した場合は追加した明細を削除.
                    if ($result->hasError()) {
                        $this->cartService->removeProduct($ProductClassId);
                        foreach ($result->getErrors() as $error) {
                            $errorMessages[] = $error->getMessage();
                        }
                    }
                    foreach ($result->getWarning() as $warning) {
                        $errorMessages[] = $warning->getMessage();
                    }
                }
                if ($pre_order_id != null) {
                    $this->cartService->setPreOrderId($pre_order_id);
                }
                $this->cartService->save();
            }            

            if ($option_product_2 == '1') {
                $OptionProduct2Id = 7;
                $OptionProduct2 = $this->productRepository->findWithSortedClassCategories($OptionProduct2Id);

                $OptionProduct2Class = null;
                // 規格無しの商品の場合は、デフォルト規格を表示用に取得する
                $has_class = $SecurityProduct->hasProductClass();
                if (!$has_class) {
                    $OptionProduct2Classes = $OptionProduct2->getProductClasses();
                    foreach ($OptionProduct2Classes as $pc) {
                        if (!is_null($pc->getClassCategory1())) {
                            continue;
                        }
                        if ($pc->isVisible()) {
                            $OptionProduct2Class = $pc;
                            break;
                        }
                    }
                    if ($this->BaseInfo->isOptionProductTaxRule() && $OptionProduct2Class->getTaxRule()) {
                        $OptionProduct2Class->setTaxRate($OptionProduct2Class->getTaxRule()->getTaxRate());
                    }
                }

                if (!$OptionProduct2Class instanceof ProductClass) {
                    $OptionProduct2ClassId = $OptionProduct2Class;
                    $OptionProduct2Class = $this->entityManager
                        ->getRepository(ProductClass::class)
                        ->find($OptionProduct2ClassId);
                    if (is_null($OptionProduct2Class)) {
                        return new Response('fail');
                    }
                }

                // カートへ追加
                $this->cartService->addProduct($OptionProduct2Class, '1');

                // 明細の正規化
                $Carts = $this->cartService->getCarts();
                foreach ($Carts as $Cart) {
                    $result = $this->purchaseFlow->validate($Cart, new PurchaseContext($Cart, $this->getUser()));
                    // 復旧不可のエラーが発生した場合は追加した明細を削除.
                    if ($result->hasError()) {
                        $this->cartService->removeProduct($ProductClassId);
                        foreach ($result->getErrors() as $error) {
                            $errorMessages[] = $error->getMessage();
                        }
                    }
                    foreach ($result->getWarning() as $warning) {
                        $errorMessages[] = $warning->getMessage();
                    }
                }
                if ($pre_order_id != null) {
                    $this->cartService->setPreOrderId($pre_order_id);
                }
                $this->cartService->save();
            }    

            return new Response('success');
        }
        return new Response('fail');
    }
}
