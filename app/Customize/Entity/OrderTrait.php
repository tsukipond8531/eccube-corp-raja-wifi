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
    private $terminal_no;

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
     * @param string $terminal_no
     *
     * @return $this
     */
    public function setTerminalNo(string $terminal_no)
    {
        $this->terminal_no = $terminal_no;

        return $this;
    }

    /**
     * @return string
     */
    public function getTerminalNo()
    {
        return $this->terminal_no;
    }

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
}