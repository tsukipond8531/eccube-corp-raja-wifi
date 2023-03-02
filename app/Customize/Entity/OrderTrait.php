<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;

/**
  * @EntityExtension("Eccube\Entity\Order")
 */
trait OrderTrait
{
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Eccube\Annotation\FormAppend(
     *     auto_render=false,
     *     type="Symfony\Component\Form\Extension\Core\Type\TextType",
     *     options={
     *          "required": false,
     *     })
     * )
     */
    private $shipping_slip_no;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Eccube\Annotation\FormAppend(
     *     auto_render=false,
     *     type="Symfony\Component\Form\Extension\Core\Type\TextType",
     *     options={
     *          "required": false,
     *     })
     * )
     */
    private $return_slip_no;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Eccube\Annotation\FormAppend(
     *     auto_render=false,
     *     type="Symfony\Component\Form\Extension\Core\Type\TextType",
     *     options={
     *          "required": false,
     *     })
     * )
     */
    private $imei;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="returned_date", type="datetimetz", nullable=true)
     * @Eccube\Annotation\FormAppend(
     *     auto_render=false,
     *     type="Symfony\Component\Form\Extension\Core\Type\DateType",
     *     options={
     *          "required": false,
     *     })
     * )
     */
    private $returned_date = null;

    /**
     * @param string $shipping_slip_no
     *
     * @return $this
     */
    public function setShippingSlipNo(string $shipping_slip_no)
    {
        $this->shipping_slip_no = $shipping_slip_no;

        return $this;
    }

    /**
     * @return string
     */
    public function getShippingSlipNo()
    {
        return $this->shipping_slip_no;
    }

    /**
     * @param string $return_slip_no
     *
     * @return $this
     */
    public function setReturnSlipNo(string $return_slip_no)
    {
        $this->return_slip_no = $return_slip_no;

        return $this;
    }

    /**
     * @return string
     */
    public function getReturnSlipNo()
    {
        return $this->return_slip_no;
    }

    /**
     * @param string $imei
     *
     * @return $this
     */
    public function setImei(string $imei)
    {
        $this->imei = $imei;

        return $this;
    }

    /**
     * @return string
     */
    public function getImei()
    {
        return $this->imei;
    }

    /**
     * Set returnedDate.
     *
     * @param \DateTime $returnedDate
     *
     * @return $this
     */
    public function setReturnedDate($returnedDate)
    {
        $this->returned_date = $returnedDate;

        return $this;
    }

    /**
     * Get returnedDate.
     *
     * @return \DateTime
     */
    public function getReturnedDate()
    {
        return $this->returned_date;
    }
}