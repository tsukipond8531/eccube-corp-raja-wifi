<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation as Eccube;

/**
 * @Eccube\EntityExtension("Eccube\Entity\Product")
 */
trait RentalProductTrait {

    /**
     * @ORM\Column(name="rental_start", type="date", nullable=true)
     * @Eccube\FormAppend(
     *     auto_render=true,
     *     type="Symfony\Component\Form\Extension\Core\Type\DateType",
     *     )
     */
	private $rental_start;

    /**
     * @ORM\Column(name="rental_end", type="date", nullable=true)
     * @Eccube\FormAppend(
     *     auto_render=true,
     *     type="Symfony\Component\Form\Extension\Core\Type\DateType",
     *     )
     */
    private $rental_end;

    /**
     * 商品オプションも商品として登録されているため、商品オプションと区別するためのフラグ
     * @var boolean
     *
     * @ORM\Column(name="basic", type="boolean", options={"default":false})
     */
    private $basic = false;

    /**
     * @return DateTime
     */
    public function getRentalStart() {
        return $this->rental_start;
    }

    /**
     * @param DateTime rental_start
     * @return RentalProductTrait
     */
    public function setRentalStart($rental_start) {
        $this->rental_start = $rental_start;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getRentalEnd() {
        return $this->rental_end;
    }

    /**
     * @param DateTime rental_end
     * @return RentalProductTrait
     */
    public function setRentalEnd($rental_end) {
        $this->rental_end = $rental_end;

        return $this;
    }

    /**
     * @param boolean $basic
     *
     * @return $this
     */
    public function setBasic($basic)
    {
        $this->basic = $basic;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getBasic()
    {
        return $this->basic;
    }
}