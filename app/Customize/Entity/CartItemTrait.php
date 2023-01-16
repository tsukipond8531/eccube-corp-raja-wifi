<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;

/**
  * @EntityExtension("Eccube\Entity\CartItem")
 */
trait CartItemTrait
{
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    public $option_no;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    public $country_name_jp;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    public $rent_from;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    public $rent_to;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    public $des_price;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    public $daily_price;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    public $option_price;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    public $product_1_price;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    public $product_2_price;

    /**
     * @param string $option_no
     *
     * @return $this
     */
    public function setOptionNo(string $option_no)
    {
        $this->option_no = $option_no;

        return $this;
    }

    /**
     * @return string
     */
    public function getOptionNo()
    {
        return $this->option_no;
    }

    /**
     * @param string $country_name_jp
     *
     * @return $this
     */
    public function setCountryNameJP(string $country_name_jp)
    {
        $this->country_name_jp = $country_name_jp;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountryNameJP()
    {
        return $this->country_name_jp;
    }

    /**
     * @param string $rent_from
     *
     * @return $this
     */
    public function setRentFrom(string $rent_from)
    {
        $this->rent_from = $rent_from;

        return $this;
    }

    /**
     * @return string
     */
    public function getRentFrom()
    {
        return $this->rent_from;
    }

    /**
     * @param string $rent_to
     *
     * @return $this
     */
    public function setRentTo(string $rent_to)
    {
        $this->rent_to = $rent_to;

        return $this;
    }

    /**
     * @return string
     */
    public function getRentTo()
    {
        return $this->rent_to;
    }

    /**
     * @param string $des_price
     *
     * @return $this
     */
    public function setDesPrice(string $des_price)
    {
        $this->des_price = $des_price;

        return $this;
    }

    /**
     * @return string
     */
    public function getDesPrice()
    {
        return $this->des_price;
    }

    /**
     * @param string $daily_price
     *
     * @return $this
     */
    public function setDailyPrice(string $daily_price)
    {
        $this->daily_price = $daily_price;

        return $this;
    }

    /**
     * @return string
     */
    public function getDailyPrice()
    {
        return $this->daily_price;
    }

    /**
     * @param string $option_price
     *
     * @return $this
     */
    public function setOptionPrice(string $option_price)
    {
        $this->option_price = $option_price;

        return $this;
    }

    /**
     * @return string
     */
    public function getOptionPrice()
    {
        return $this->option_price;
    }

    /**
     * @param string $product_1_price
     *
     * @return $this
     */
    public function setProduct1Price(string $product_1_price)
    {
        $this->product_1_price = $product_1_price;

        return $this;
    }

    /**
     * @return string
     */
    public function getProduct1Price()
    {
        return $this->product_1_price;
    }

    /**
     * @param string $product_2_price
     *
     * @return $this
     */
    public function setProduct2Price(string $product_2_price)
    {
        $this->product_2_price = $product_2_price;

        return $this;
    }

    /**
     * @return string
     */
    public function getProduct2Price()
    {
        return $this->product_2_price;
    }
}