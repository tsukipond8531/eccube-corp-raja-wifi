<?php

// src/Entity/Task.php
namespace Customize\Entity;

use Doctrine\Common\Collections\Criteria;

class DeliveryItem
{
    private $shipping_method = true;
    private $address_choice = true;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name01", type="string", length=255)
     */
    private $name01;

    /**
     * @var string
     *
     * @ORM\Column(name="name02", type="string", length=255)
     */
    private $name02;

    /**
     * @var string|null
     *
     * @ORM\Column(name="kana01", type="string", length=255, nullable=true)
     */
    private $kana01;

    /**
     * @var string|null
     *
     * @ORM\Column(name="kana02", type="string", length=255, nullable=true)
     */
    private $kana02;

    /**
     * @var string|null
     *
     * @ORM\Column(name="company_name", type="string", length=255, nullable=true)
     */
    private $company_name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="postal_code", type="string", length=8, nullable=true)
     */
    private $postal_code;

    /**
     * @var string|null
     *
     * @ORM\Column(name="addr01", type="string", length=255, nullable=true)
     */
    private $addr01;

    /**
     * @var string|null
     *
     * @ORM\Column(name="addr02", type="string", length=255, nullable=true)
     */
    private $addr02;
    /**
     * @var string|null
     *
     * @ORM\Column(name="phone_number", type="string", length=14, nullable=true)
     */
    private $phone_number;
    /**
     * @var \Eccube\Entity\Master\Pref
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Master\Pref")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pref_id", referencedColumnName="id")
     * })
     */
    private $Pref;

    /**
     * @var \Doctrine\Common\Collections\Collection|Shipping[]
     *
     * @ORM\OneToMany(targetEntity="Eccube\Entity\Shipping", mappedBy="Order", cascade={"persist","remove"})
     */
    private $Shippings;
    private $deliver_date = '';  // new \DateTime('@'.strtotime('tomorrow'));
    private $deliver_time = '';  // new \DateTime('@'.strtotime('tomorrow'));

    public function __construct()
    {
        $this->Shippings = new \Doctrine\Common\Collections\ArrayCollection();
    }
    

    public function getShippingMethod(): bool
    {
        return $this->shipping_method;
    }

    public function setShippingMethod(bool $shipping_method): void
    {
        $this->shipping_method = $shipping_method;
    }

    public function setAddressChoice(bool $address_choice): void
    {
        $this->address_choice = $address_choice;
    }

    public function getAddressChoice(): bool
    {
        return $this->address_choice;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name01.
     *
     * @param string $name01
     *
     * @return Customer
     */
    public function setName01($name01)
    {
        $this->name01 = $name01;

        return $this;
    }

    /**
     * Get name01.
     *
     * @return string
     */
    public function getName01()
    {
        return $this->name01;
    }

    /**
     * Set name02.
     *
     * @param string $name02
     *
     * @return Customer
     */
    public function setName02($name02)
    {
        $this->name02 = $name02;

        return $this;
    }

    /**
     * Get name02.
     *
     * @return string
     */
    public function getName02()
    {
        return $this->name02;
    }

    /**
     * Set kana01.
     *
     * @param string|null $kana01
     *
     * @return Customer
     */
    public function setKana01($kana01 = null)
    {
        $this->kana01 = $kana01;

        return $this;
    }

    /**
     * Get kana01.
     *
     * @return string|null
     */
    public function getKana01()
    {
        return $this->kana01;
    }

    /**
     * Set kana02.
     *
     * @param string|null $kana02
     *
     * @return Customer
     */
    public function setKana02($kana02 = null)
    {
        $this->kana02 = $kana02;

        return $this;
    }

    /**
     * Get kana02.
     *
     * @return string|null
     */
    public function getKana02()
    {
        return $this->kana02;
    }

    /**
     * Set companyName.
     *
     * @param string|null $companyName
     *
     * @return Customer
     */
    public function setCompanyName($companyName = null)
    {
        $this->company_name = $companyName;

        return $this;
    }

    /**
     * Get companyName.
     *
     * @return string|null
     */
    public function getCompanyName()
    {
        return $this->company_name;
    }

    /**
     * Set postal_code.
     *
     * @param string|null $postal_code
     *
     * @return Customer
     */
    public function setPostalCode($postal_code = null)
    {
        $this->postal_code = $postal_code;

        return $this;
    }

    /**
     * Get postal_code.
     *
     * @return string|null
     */
    public function getPostalCode()
    {
        return $this->postal_code;
    }

    /**
     * Set addr01.
     *
     * @param string|null $addr01
     *
     * @return Customer
     */
    public function setAddr01($addr01 = null)
    {
        $this->addr01 = $addr01;

        return $this;
    }

    /**
     * Get addr01.
     *
     * @return string|null
     */
    public function getAddr01()
    {
        return $this->addr01;
    }

    /**
     * Set addr02.
     *
     * @param string|null $addr02
     *
     * @return Customer
     */
    public function setAddr02($addr02 = null)
    {
        $this->addr02 = $addr02;

        return $this;
    }

    /**
     * Get addr02.
     *
     * @return string|null
     */
    public function getAddr02()
    {
        return $this->addr02;
    }

    /**
     * Set phone_number.
     *
     * @param string|null $phone_number
     *
     * @return Customer
     */
    public function setPhoneNumber($phone_number = null)
    {
        $this->phone_number = $phone_number;

        return $this;
    }

    /**
     * Get phone_number.
     *
     * @return string|null
     */
    public function getPhoneNumber()
    {
        return $this->phone_number;
    }
    
    /**
     * Set pref.
     *
     * @param \Eccube\Entity\Master\Pref|null $pref
     *
     * @return CustomerAddress
     */
    public function setPref(Master\Pref $pref = null)
    {
        $this->Pref = $pref;

        return $this;
    }

    /**
     * Get pref.
     *
     * @return \Eccube\Entity\Master\Pref|null
     */
    public function getPref()
    {
        return $this->Pref;
    }

    /**
     * Get shippings.
     *
     * @return \Doctrine\Common\Collections\Collection|\Eccube\Entity\Shipping[]
     */
    public function getShippings()
    {
        $criteria = Criteria::create()
        ->orderBy(['name01' => Criteria::ASC, 'name02' => Criteria::ASC, 'id' => Criteria::ASC]);

        return $this->Shippings->matching($criteria);
    }

    // public function getOtherPhone(): string
    // {
    //     return $this->other_phone;
    // }

    // public function setOtherPhone(string $other_phone): void
    // {
    //     $this->other_phone = $other_phone;
    // }

    public function getDeliverDate(): string
    {
        return $this->deliver_date;
    }

    public function setDeliverDate(string $deliver_date): void
    {
        $this->deliver_date = $deliver_date;
    }

    public function getDeliverTime(): string
    {
        return $this->deliver_time;
    }

    public function setDeliverTime(string $deliver_time): void
    {
        $this->deliver_time = $deliver_time;
    }
}