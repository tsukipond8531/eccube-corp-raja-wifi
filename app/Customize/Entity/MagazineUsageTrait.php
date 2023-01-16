<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation as Eccube;

/**
 * @Eccube\EntityExtension("Eccube\Entity\Customer")
 */
trait MagazineUsageTrait {

    /**
     * @ORM\Column(name="magazine_usage_id", type="smallint", length=5, nullable=true, options={"default":1})
     * @Eccube\FormAppend(
     *     auto_render=true,
     *     type="Symfony\Component\Form\Extension\Core\Type\ChoiceType",
     *     options={
     *          "required": true,
     *          "label": "メールマガジン送付について",
     *          "multiple" : false,
     *          "expanded" : true,
     *          "choices"  : {"HTMLメールを希望":1,"テキストメールを希望":2,"希望しない":3}
     *     })
     */
    //"choices"  : {"HTMLメールを希望":1,"テキストメールを希望":2,"希望しない":3},
    //"choices"  : {1:"HTMLメールを希望",2:"テキストメールを希望",3:"希望しない"},
	private $magazine_usage_id;

    /**
     * @return integer
     */
    public function getMagazineUsageId() {
        return $this->magazine_usage_id;
    }

    /**
     * @param integer magazine_usage_id
     * @return MagazineUsageTrait
     */
    public function setMagazineUsageId($magazine_usage_id) {
        $this->magazine_usage_id = $magazine_usage_id;

        return $this;
    }
}