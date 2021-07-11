<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

use Windwalker\Data\Collection;
use Windwalker\Database\DatabaseFactory;
use Windwalker\ORM\Test\Entity\StubUser;

$db = (new DatabaseFactory())->create('pdo_mysql', []);

$db->from()
    ->leftJoin()
    ->all();

// Get 1
$user = ($orm = $db->orm())->findOne(StubUser::class, 1);
// === entity(...)->findOne()
// === $orm->from(User::class)->get()

$user->name = 'New Name';

$orm->store($user);
$orm->commit();

// Get multiple
/** @var StubUser[] $users */
$users = $orm->from(StubUser::class)
    ->where('id', '>', 50)
    ->all();

// Get with select or join

/** @var Collection|Collection[] $users */
$users = $orm->from(StubUser::class, 'u')
    ->leftJoin(Comment::class, 'c')
    ->selectRaw('count(distinct c.id)', 'count')
    ->all();

$users = $users->mapAs(StubUser::class);
$users[0]->hello();

// Entity create/update/delete with events
foreach ($users as $user) {
    $user = $user->as(StubUser::class);

    $user->id = null;
}

// TODO:
// create() / update() / delete()
// store() commit()
// Events
