<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation as Eccube;
/**
 * @Eccube\EntityExtension("Eccube\Entity\Customer")
 */
trait RentalUsageTrait {

    /**
     * @ORM\Column(name="rental_usage_id", type="smallint", length=5, nullable=true)
     * @Eccube\FormAppend(
     *     auto_render=true,
     *     type="Symfony\Component\Form\Extension\Core\Type\ChoiceType",
     *     options={
     *          "required": false,
     *          "label": "レンタル⽤途",
     *          "multiple" : false,
     *          "expanded" : false,
     *          "choices"  : {"おひとり様旅行": 1,"友だち・家族旅行":2,"出張":3,"駐在":4,"その他":5}
     *     })
     */
    //"choices"  : {"おひとり様旅行": 1,"友だち・家族旅行":2,"出張":3,"駐在":4,"その他":5},
    //"choices"  : {1: "おひとり様旅行",2:"友だち・家族旅行",3:"出張",4:"駐在",5:"その他"},
    private $rental_usage_id;

    /**
     * @return integer
     */
    public function getRentalUsageId() {
        return $this->rental_usage_id;
    }

    /**
     * @param integer rental_usage_id
     * @return RentalUsageTrait
     */
    public function setRentalUsageId($rental_usage_id) {
        $this->rental_usage_id = $rental_usage_id;

        return $this;
    }
}