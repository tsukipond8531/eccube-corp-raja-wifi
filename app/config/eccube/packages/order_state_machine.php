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

use Eccube\Entity\Master\OrderStatus as Status;
use Eccube\Service\OrderStateMachineContext;

$container->loadFromExtension('framework', [
    'workflows' => [
        'order' => [
            'type' => 'state_machine',
            'marking_store' => [
                'type' => 'single_state',
                'arguments' => 'status',
            ],
            'supports' => [
                OrderStateMachineContext::class,
            ],
            'initial_place' => (string) Status::NEW,
            'places' => [
                (string) Status::NEW,
                (string) Status::CANCEL,
                (string) Status::IN_PROGRESS,
                (string) Status::DELIVERED,
                (string) Status::PAID,
                (string) Status::PENDING,
                (string) Status::PROCESSING,
                (string) Status::RETURNED,
                (string) Status::SHOP,
                (string) Status::NORETURNED,
            ],
            'transitions' => [
                'pay' => [
                    'from' => (string) Status::NEW,
                    'to' => (string) Status::PAID,
                ],
                'packing' => [
                    'from' => [(string) Status::NEW, (string) Status::PAID],
                    'to' => (string) Status::IN_PROGRESS,
                ],
                'cancel' => [
                    'from' => [(string) Status::NEW, (string) Status::IN_PROGRESS, (string) Status::PAID],
                    'to' => (string) Status::CANCEL,
                ],
                'back_to_in_progress' => [
                    'from' => (string) Status::CANCEL,
                    'to' => (string) Status::IN_PROGRESS,
                ],
                'ship' => [
                    'from' => [(string) Status::NEW, (string) Status::PAID, (string) Status::IN_PROGRESS],
                    'to' => [(string) Status::DELIVERED],
                ],
                'return' => [
                    'from' => (string) Status::DELIVERED,
                    'to' => (string) Status::RETURNED,
                ],
                'cancel_return' => [
                    'from' => (string) Status::RETURNED,
                    'to' => (string) Status::DELIVERED,
                ],
                'shop' => [
                    'from' => (string) Status::NEW,
                    'to' => (string) Status::SHOP,
                ],
                'no_returned' => [
                    'from' => (string) Status::DELIVERED,
                    'to' => (string) Status::NORETURNED,
                ],
            ],
        ],
    ],
]);
