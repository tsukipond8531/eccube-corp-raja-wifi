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

namespace Customize\Doctrine\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Eccube\Entity\Master\OrderStatus;

class OrderStatusProcessingShowFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        // 購入処理中ステータスの受注だけを表示にする.
        if ($targetEntity->reflClass->getName() === 'Eccube\Entity\Order') {
            return $targetTableAlias.'.order_status_id = '.OrderStatus::PROCESSING;
        }

        // 購入処理中ステータスの受注だけを表示にする.
        if ($targetEntity->reflClass->getName() === 'Eccube\Entity\Master\OrderStatus') {
            return $targetTableAlias.'.id = '.OrderStatus::PROCESSING;
        }

        return '';
    }
}