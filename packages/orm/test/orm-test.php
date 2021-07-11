<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

use Windwalker\Data\Collection;
use Windwalker\ORM\DataMapper;
use Windwalker\ORM\ORM;
use Windwalker\ORM\Test\Entity\StubUser;

$orm = new ORM($db);
$em = $orm->mapper(StubUser::class);
$user = $em->findOne();

/** @psalm-var  DataMapper<StubUser> $dm */
$dm = (new DataMapper(Collection::class));
$u = $dm->se
$u = $dm->create(StubUser::class);
