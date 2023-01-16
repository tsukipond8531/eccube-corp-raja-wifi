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

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation as Eccube;

if (!class_exists(CreditCard::class, false)) {
    
    /**
     * CreditCard
     *
     * @ORM\Table(name="dtb_credit_card")
     * @ORM\InheritanceType("SINGLE_TABLE")
     * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
     * @ORM\HasLifecycleCallbacks()
     * @ORM\Entity(repositoryClass="Customize\Repository\CreditCardRepository")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
     */
    class CreditCard extends \Eccube\Entity\AbstractEntity
    {
        /**
         * @var int
         *
         * @ORM\Column(name="id", type="integer", options={"unsigned":true})
         * @ORM\Id
         * @ORM\GeneratedValue(strategy="IDENTITY")
         */
        private $id;

        /**
         * @var int
         *
         * @ORM\Column(name="customer_id", type="integer", options={"unsigned":true})
         */
        private $customer_id;

        /**
         * @var int
         *
         * @ORM\Column(name="credit_card_type", type="smallint", length=5, options={"default":0, "unsigned": true})
         *
         * {1:"VISA",2:"MASTER",3:"DINERS",4:"JCB",5:"AMEX"}
         */
        private $credit_card_type;

        /**
         * @var string
         *
         * @ORM\Column(name="credit_card_number", type="string", length=16)
         */
        private $credit_card_number;

        /**
         * @var string
         *
         * @ORM\Column(name="holder_name", type="string", length=255)
         */
        private $holder_name;

        /**
         * @var string|null
         *
         * @ORM\Column(name="expiration_month", type="string", length=2)
         */
        private $expiration_month;

        /**
         * @var string|null
         *
         * @ORM\Column(name="expiration_year", type="string", length=4)
         */
        private $expiration_year;

        /**
         * @var string|null
         *
         * @ORM\Column(name="security_code", type="string", length=4)
         */
        private $security_code;

        /**
         * @var string|null
         *
         * @ORM\Column(name="stripe_cardkey", type="string", length=255)
         */
        private $stripe_cardkey;





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
         * Get customer_id.
         *
         * @return int
         */
        public function getCustomerId()
        {
            return $this->customer_id;
        }

        /**
         * Set customer_id.
         *
         * @param int $customer_id
         *
         * @return CreditCard
         */
        public function setCustomerId($customer_id)
        {
            $this->customer_id = $customer_id;

            return $this;
        }

        /**
         * Get credit_card_type.
         *
         * @return int
         */
        public function getCreditCardType()
        {
            return $this->credit_card_type;
        }

        /**
         * Set credit_card_type.
         *
         * @param int $credit_card_type
         *
         * @return CreditCard
         */
        public function setCreditCardType($credit_card_type)
        {
            $this->credit_card_type = $credit_card_type;

            return $this;
        }

        /**
         * Get credit_card_number.
         *
         * @return string
         */
        public function getCreditCardNumber()
        {
            return $this->credit_card_number;
        }

		/**
         * Set credit_card_number.
         *
         * @param string $credit_card_number
         *
         * @return CreditCard
         */
        public function setCreditCardNumber($credit_card_number)
        {
            $this->credit_card_number = $credit_card_number;

            return $this;
        }

        /**
         * Set holder_name.
         *
         * @param string $holder_name
         *
         * @return CreditCard
         */
        public function setHolderName($holder_name)
        {
            $this->holder_name = $holder_name;

            return $this;
        }

        /**
         * Get holder_name.
         *
         * @return string
         */
        public function getHolderName()
        {
            return $this->holder_name;
        }

        /**
         * Set expiration_month.
         *
         * @param String
         *
         * @return CreditCard
         */
        public function setExpirationMonth($expiration_month)
        {
            $this->expiration_month = $expiration_month;

            return $this;
        }

        /**
         * Get expiration_month.
         *
         * @return String
         */
        public function getExpirationMonth()
        {
            return $this->expiration_month;
        }

        /**
         * Set expiration_year.
         *
         * @param String
         *
         * @return CreditCard
         */
        public function setExpirationYear($expiration_year)
        {
            $this->expiration_year = $expiration_year;

            return $this;
        }

        /**
         * Get expiration_year.
         *
         * @return String
         */
        public function getExpirationYear()
        {
            return $this->expiration_year;
        }

        /**
         * Set security_code.
         *
         * @param string $security_code
         *
         * @return CreditCard
         */
        public function setSecurityCode($security_code)
        {
            $this->security_code = $security_code;

            return $this;
        }

        /**
         * Get security_code.
         *
         * @return string
         */
        public function getSecurityCode()
        {
            return $this->security_code;
        }

        /**
         * Set stripe_cardkey.
         *
         * @param string $stripe_cardkey
         *
         * @return CreditCard
         */
        public function setStripeCardkey($stripe_cardkey)
        {
            $this->stripe_cardkey = $stripe_cardkey;

            return $this;
        }

        /**
         * Get stripe_cardkey.
         *
         * @return string
         */
        public function getStripeCardkey()
        {
            return $this->stripe_cardkey;
        }

        /**
         * Assign entity properties using an array
         *
         * @param array $attributes assoc array of values to assign
         * @return null
         */
        public function fromArray(array $attributes)
        {
            foreach ($attributes as $name => $value) {
                if (property_exists($this, $name)) {
                    $methodName = $this->_getSetterName($name);
                    if ($methodName) {
                        $this->{$methodName}($value);
                    } else {
                        $this->$name = $value;
                    }
                }
            }
        }

        /**
         * Get property setter method name (if exists)
         *
         * @param string $propertyName entity property name
         * @return false|string
         */
        protected function _getSetterName($propertyName)
        {
            $prefixes = array('add', 'set');

            foreach ($prefixes as $prefix) {
                $methodName = sprintf('%s%s', $prefix, ucfirst(strtolower($propertyName)));

                if (method_exists($this, $methodName)) {
                    return $methodName;
                }
            }
            return false;
        }
    }
}
