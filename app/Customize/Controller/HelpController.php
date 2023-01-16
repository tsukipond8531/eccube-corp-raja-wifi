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

use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

class HelpController extends AbstractController
{
    /**
     * HelpController constructor.
     */
    public function __construct()
    {
    }

    /**
     * 特定商取引法.
     *
     * @Route("/help/tradelaw", name="help_tradelaw", methods={"GET"})
     * @Template("Help/tradelaw.twig")
     */
    public function tradelaw()
    {
        return [];
    }

    /**
     * ご利用ガイド.
     *
     * @Route("/introduction", name="introduction", methods={"GET"})
     * @Template("Help/guide.twig")
     */
    public function guide()
    {
        return [];
    }

    /**
     * 当サイトについて.
     *
     * @Route("/profile", name="profile", methods={"GET"})
     * @Template("Help/about.twig")
     */
    public function about()
    {
        return [];
    }

    /**
     * プライバシーポリシー.
     *
     * @Route("/privacy", name="privacy", methods={"GET"})
     * @Template("Help/privacy.twig")
     */
    public function privacy()
    {
        return [];
    }

    /**
     * 利用規約.
     *
     * @Route("/help/agreement", name="help_agreement", methods={"GET"})
     * @Template("Help/agreement.twig")
     */
    public function agreement()
    {
        return [];
    }
	

    /**
     * 特定商取引法の表記.
     *
     * @Route("/help/commerce", name="help_commerce", methods={"GET"})
     * @Template("Help/commerce.twig")
     */
    public function commerce()
    {
        return [];
    }
}
