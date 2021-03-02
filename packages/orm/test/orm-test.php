<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

use Windwalker\ORM\Test\Entity\StubUser;

$orm  = new \Windwalker\ORM\ORM($db);
$em   = $orm->mapper(StubUser::class);
$user = $em->findOne();

/** @psalm-var  \Windwalker\ORM\DataMapper<StubUser> $dm */
$dm = (new \Windwalker\ORM\DataMapper(\Windwalker\Data\Collection::class));
$u  = $dm->se
$u  = $dm->create(StubUser::class);
