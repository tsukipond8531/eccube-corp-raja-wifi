<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation as Eccube;

/**
 * @Eccube\EntityExtension("Eccube\Entity\Customer")
 */
trait OverSeasAddressTrait {

    /**
     * @ORM\Column(name="overseas_address", type="string", length=255, nullable=true)
     * @Eccube\FormAppend(
     *     auto_render=true,
     *     type="Symfony\Component\Form\Extension\Core\Type\TextType",
     *     )
     */
	private $overseas_address;

    /**
     * @return string
     */
    public function getOverSeasAddress() {
        return $this->overseas_address;
    }

    /**
     * @param string overseas_address
     * @return OverSeasAddressTrait
     */
    public function setOverSeasAddress($overseas_address) {
        $this->overseas_address = $overseas_address;

        return $this;
    }
}