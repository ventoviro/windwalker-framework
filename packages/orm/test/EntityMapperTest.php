<?php declare(strict_types=1);
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\ORM\Test;

use Windwalker\Data\Collection;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\ORM\EntityMapper;
use Windwalker\ORM\Test\Entity\StubComment;
use Windwalker\ORM\Test\Entity\StubFlower;
use Windwalker\ORM\Test\Entity\StubUser;
use Windwalker\Utilities\Arr;

/**
 * Test class of DataMapper
 *
 * @since 2.0
 */
class EntityMapperTest extends AbstractORMTestCase
{
    /**
     * Test instance.
     *
     * @var EntityMapper
     */
    protected EntityMapper $instance;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     * @throws \Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = self::$orm->mapper(StubFlower::class);
    }

    /**
     * Method to test find().
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testFind()
    {
        $dataset = $this->instance->select()
            ->limit(3)
            ->offset(0)
            ->all();

        self::assertEquals([1, 2, 3], $dataset->column('id')->dump());
        self::assertEquals(['Alstroemeria', 'Amaryllis', 'Anemone'], $dataset->column('title')->dump());

        $dataset = $this->instance->select()
            ->where('id', null)
            ->limit(3)
            ->all();

        self::assertEquals([], $dataset->column('id')->dump());

        $dataset = $this->instance->select()
            ->where('id', 0)
            ->limit(3)
            ->all();

        self::assertEquals([], $dataset->column('id')->dump());

        $dataset = $this->instance->select()
            ->where(['state' => 1])
            ->order('ordering', 'DESC')
            ->limit(3)
            ->offset(2)
            ->all();

        self::assertEquals([1, 1, 1], $dataset->column('state')->dump());
        self::assertEquals([82, 79, 77], $dataset->column('ordering')->dump());
        self::assertEquals(['Violet', 'red', 'pink'], $dataset->column('title')->dump());

        $dataset = $this->instance
            ->select('id', 'state')
            ->where(['state' => 1])
            ->order('ordering', 'DESC')
            ->offset(2)
            ->limit(3)
            ->all();

        self::assertEquals([], $dataset->column('catid')->dump());

        // Test find with no conditions
        $dataset = $this->instance->select()->all();

        self::assertFalse($dataset->isNull());

        $dataset = $this->instance->findOne(null);

        self::assertNull($dataset);
    }

    /**
     * Method to test findOne().
     *
     * @return void
     */
    public function testFindOne()
    {
        // Find by primary key
        /** @var StubFlower $data */
        $data = $this->instance->findOne(7);

        self::assertInstanceOf(
            StubFlower::class,
            $data
        );
        self::assertEquals('Baby\'s Breath', $data->getTitle());

        // Find by conditions
        $data = $this->instance->findOne(['title' => 'Cosmos']);

        self::assertEquals('peaceful', $data->getMeaning());

        $data = $this->instance->findOne(['title' => 'Freesia', 'state' => 1]);

        self::assertNull($data);
    }

    /**
     * testFindColumn
     *
     * @return  void
     */
    public function testFindColumn()
    {
        $columns = $this->instance->select('id')
            ->order(['catid', 'ordering'])
            ->limit(3)
            ->loadColumn();

        self::assertEquals([3, 4, 7], $columns->dump());
    }

    /**
     * Method to test create().
     *
     * @return void
     */
    public function testCreateMultiple()
    {
        // Create from array
        $items = [
            ['title' => 'Sakura', 'meaning' => '', 'params' => ''],
            ['title' => 'Peony', 'meaning' => '', 'params' => ''],

            // DataMapper should remove non-necessary field
            ['title' => 'Sunflower', 'anim' => 'bird', 'meaning' => '', 'params' => ''],
        ];

        /** @var StubFlower[] $returns */
        $returns = $this->instance->createMultiple($items);

        $newItems = self::$db->prepare(
            'SELECT * FROM ww_flower ORDER BY id DESC LIMIT 3'
        )
            ->all();

        self::assertEquals(['Sunflower', 'Peony', 'Sakura'], $newItems->column('title')->dump());

        self::assertEquals(86, $returns[0]->id, 'Inserted id not matched.');

        self::assertInstanceOf(StubFlower::class, $returns[0]);

        // Create from Collection
        $items = new Collection(
            [
                new Collection(['title' => 'Sakura2', 'meaning' => '', 'params' => '']),
                new Collection(['title' => 'Peony2', 'meaning' => '', 'params' => '']),
                new Collection(['title' => 'Sunflower2', 'meaning' => '', 'params' => '']),
            ]
        );

        $returns = $this->instance->createMultiple($items);

        $newItems = static::$db->prepare('SELECT * FROM ww_flower ORDER BY id DESC LIMIT 3')->all();

        self::assertEquals(['Sunflower2', 'Peony2', 'Sakura2'], $newItems->column('title')->dump());

        self::assertEquals(89, $returns[0]->id, 'Inserted id not matched.');

        self::assertInstanceOf(Collection::class, $returns[0]);
    }

    /**
     * Method to test createOne().
     *
     * @return void
     */
    public function testCreateOne()
    {
        // Create from array
        $data = [
            'title' => 'Foo flower',
            'state' => 1,
            'meaning' => '',
            'params' => '',
        ];

        $newData = $this->instance->createOne($data);

        self::assertEquals(92, $newData->id);
        self::assertEquals(
            92,
            static::$db->prepare('SELECT * FROM ww_flower ORDER BY id DESC LIMIT 1')->get()->id
        );

        self::assertInstanceOf(StubFlower::class, $newData);

        // Create from Collection
        $data = new Collection(
            [
                'title' => 'Foo flower',
                'state' => 1,
                'meaning' => '',
                'params' => '',
            ]
        );

        $newData = $this->instance->createOne($data);

        self::assertEquals(93, $newData->id);
        self::assertEquals(93, static::$db->prepare('SELECT * FROM ww_flower ORDER BY id DESC LIMIT 1')->get()->id);

        self::assertInstanceOf(Collection::class, $newData);
    }

    /**
     * Method to test update().
     *
     * @return void
     */
    public function testUpdate()
    {
        // Update from array
        $items = [
            ['id' => 1, 'state' => 1],
            ['id' => 2, 'state' => 1],
            ['id' => 3, 'state' => 1],
        ];

        $returns = $this->instance->updateMultiple($items, 'id');

        $updated = static::$db->prepare('SELECT * FROM ww_flower LIMIT 3')->all();

        self::assertEquals([1, 1, 1], $updated->column('state')->dump());

        self::assertInstanceOf(StatementInterface::class, $returns[0]);

        // Use from DataSet
        $items = new Collection(
            [
                new Collection(['id' => 1, 'state' => 0]),
                new Collection(['id' => 2, 'state' => 0]),
                new Collection(['id' => 3, 'state' => 0]),
            ]
        );

        $returns = $this->instance->updateMultiple($items, 'id');

        $updated = static::$db->prepare('SELECT * FROM ww_flower LIMIT 3')->all();

        self::assertEquals([0, 0, 0], $updated->column('state')->dump());

        self::assertInstanceOf(StatementInterface::class, $returns[0]);
        // TODO: Test Update Nulls
    }

    /**
     * Method to test updateOne().
     *
     * @return void
     */
    public function testUpdateOne()
    {
        // Update from array
        $data = ['id' => 10, 'params' => '{}'];

        $this->instance->updateOne($data);

        self::assertEquals(
            '{}',
            static::$db->prepare('SELECT * FROM ww_flower WHERE id = 10 LIMIT 1')
                ->get()
                ->params
        );

        // Update from Data
        $data = new Collection(['id' => 11, 'params' => '{}']);

        $updateData = $this->instance->updateOne($data);

        self::assertEquals(
            '{}',
            static::$db->prepare('SELECT * FROM ww_flower WHERE id = 11 LIMIT 1')
                ->get()
                ->params
        );
        // TODO: Test Update Nulls
    }

    public function testUpdateCurrentTime()
    {
        $mapper = static::$orm->mapper(StubComment::class);

        /** @var StubComment $comment */
        /** @var StubComment $comment2 */
        $comment = $mapper->findOne(10);

        $mapper->updateOne($comment);

        $comment2 = $mapper->findOne(10);

        self::assertNotEquals(
            $comment->getCreated()->getTimestamp(),
            $comment2->getCreated()->getTimestamp()
        );

        self::assertNotEquals(
            self::$db->getNullDate(),
            $comment2->getCreated()->format(self::$db->getDateFormat())
        );
    }

    /**
     * Method to test updateAll().
     *
     * @return void
     */
    public function testUpdateWhere()
    {
        $data = ['state' => 0];

        $this->instance->updateWhere($data, ['id' => [4, 5, 6]]);

        $dataset = static::$db->prepare('SELECT * FROM ww_flower WHERE id IN(4, 5, 6)')->all();

        self::assertEquals([0, 0, 0], $dataset->column('state')->dump());
    }

    public function testUpdateBatch()
    {
        $data = ['state' => 2];

        $this->instance->updateWhere($data, ['id' => [4, 5, 6]]);

        $dataset = static::$db->prepare('SELECT * FROM ww_flower WHERE id IN(4, 5, 6)')->all();

        self::assertEquals([2, 2, 2], $dataset->column('state')->dump());
    }

    /**
     * Method to test flush().
     *
     * @return void
     */
    public function testFlush()
    {
        // Prepare test data
        static::$db->execute('UPDATE ww_flower SET catid = 3 WHERE id IN (6, 7, 8)');

        $dataset = [
            ['title' => 'Baby\'s Breath2', 'catid' => 3, 'meaning' => '', 'params' => ''],
            ['title' => 'Bachelor Button2', 'catid' => 3, 'meaning' => '', 'params' => ''],
            ['title' => 'Begonia2', 'catid' => 3, 'meaning' => '', 'params' => ''],
        ];

        // Delete all catid = 3 and re insert them.
        $returns = $this->instance->flush($dataset, ['catid' => 3]);

        $newDataset = static::$db->prepare('SELECT * FROM ww_flower WHERE catid = 3')->all();

        self::assertEquals(['Baby\'s Breath2', 'Bachelor Button2', 'Begonia2'], $newDataset->column('title')->dump());
        self::assertEquals([94, 95, 96], $newDataset->column('id')->dump());
    }

    /**
     * Method to test save().
     *
     * @return void
     */
    public function testSaveMultiple()
    {
        $items = [
            ['title' => 'Sunflower', 'catid' => 5, 'meaning' => '', 'params' => ''],
            ['id' => 15, 'title' => 'striped2', 'catid' => 5, 'meaning' => '', 'params' => ''],
        ];

        $returns = $this->instance->saveMultiple($items, 'id');

        $returns = new Collection($returns);

        $newDataset = static::$db->prepare('SELECT * FROM ww_flower WHERE catid = 5')->all();

        self::assertEquals(97, $returns[0]->id, 'Should return insert ID');
        self::assertEquals([97, 15], $returns->column('id')->dump(), 'Inserted ID not matched');
        self::assertEquals([5, 5], $newDataset->column('catid')->dump(), 'New catid should be 5');
    }

    /**
     * Method to test saveOne().
     *
     * @return void
     */
    public function testSaveOne()
    {
        $data = ['title' => 'Sakura', 'catid' => 6, 'meaning' => '', 'params' => ''];

        $return = $this->instance->saveOne($data, 'id');

        self::assertEquals('Sakura', static::$db->prepare('SELECT title FROM ww_flower WHERE catid = 6')->result());
        self::assertEquals(98, $return->id);

        $data = ['id' => 15, 'title' => 'striped3', 'catid' => 6, 'meaning' => '', 'params' => ''];

        $return = $this->instance->saveOne($data, 'id');

        self::assertEquals('striped3', static::$db->prepare('SELECT title FROM ww_flower WHERE id = 15')->result());
        self::assertEquals(15, $return->id);
    }

    /**
     * Method to test delete().
     *
     * @return void
     */
    public function testDelete()
    {
        $this->instance->deleteWhere(['id' => 16]);

        self::assertNull(static::$db->prepare('SELECT * FROM ww_flower WHERE id = 16')->get());

        $flower = new StubFlower();
        $flower->id = 17;
        $this->instance->deleteWhere($flower);

        self::assertNull(static::$db->prepare('SELECT * FROM ww_flower WHERE id = 17')->get());
    }

    public function testSync()
    {
        $comments = static::$orm->from(StubComment::class)
            ->where('type', 'article')
            ->where('user_id', 1)
            ->where('target_id', 1)
            ->all();

        unset($comments[1]);

        $comments[0]->user_id = 10;

        $comment = new StubComment();
        $comment->setType('article');
        $comment->setUserId(1);
        $comment->setTargetId(1);
        $comment->setContent('A');

        $comments[] = $comment;

        $comment = new StubComment();
        $comment->setType('article');
        $comment->setUserId(1);
        $comment->setTargetId(1);
        $comment->setContent('B');

        $comments[] = $comment;

        $comments = $comments->values();

        $syncResult = self::$orm->mapper(StubComment::class)
            ->sync(
                $comments,
                [
                    'type' => 'article',
                    'user_id' => 1,
                    'target_id' => 1
                ],
                ['id']
            );

        $syncResult = Arr::collapse($syncResult);

        self::assertContainsOnlyInstancesOf(
            StubComment::class,
            $syncResult
        );

        $newComments = static::$orm->from(StubComment::class)
            ->where('type', 'article')
            ->where('user_id', 1)
            ->where('target_id', 1)
            ->all();

        self::assertEquals(
            [1, 3, 46, 47],
            $newComments->column('id')->dump()
        );
    }

    public function testFindOneOrCreate()
    {
        /** @var StubFlower $flower */
        $flower = $this->instance->findOneOrCreate(
            ['title' => 'Fire Flower'],
            [
                'catid' => 1,
                'meaning' => 'Hot',
                'ordering' => 123,
                'state' => 1,
            ]
        );

        self::assertInstanceOf(StubFlower::class, $flower);
        self::assertEquals(99, $flower->id);
        self::assertEquals('Fire Flower', $flower->getTitle());
        self::assertEquals('Hot', $flower->getMeaning());

        /** @var StubFlower $flower */
        $flower = $this->instance->findOneOrCreate(
            ['title' => 'Ice Flower'],
            fn(array $item) => $item += [
                'catid' => 1,
                'meaning' => 'Cold',
                'ordering' => 124,
                'state' => 1,
            ]
        );

        self::assertInstanceOf(StubFlower::class, $flower);
        self::assertEquals(100, $flower->id);
        self::assertEquals('Ice Flower', $flower->getTitle());
        self::assertEquals('Cold', $flower->getMeaning());
    }

    public function testUpdateOneOrCreate()
    {
        /** @var StubFlower $flower */
        $flower = $this->instance->updateOneOrCreate(
            ['title' => 'Stone Flower', 'meaning' => 'Strong'],
            [
                'catid' => 1,
                'ordering' => 125,
                'state' => 1,
            ],
            ['meaning'],
        );

        self::assertInstanceOf(StubFlower::class, $flower);
        self::assertEquals(101, $flower->id);
        self::assertEquals('Stone Flower', $flower->getTitle());
        self::assertEquals('Strong', $flower->getMeaning());

        /** @var StubFlower $flower */
        $flower = $this->instance->updateOneOrCreate(
            ['title' => 'Lisianthus', 'meaning' => 'calming2'],
            [
                'catid' => 1,
                'ordering' => 2,
                'state' => 1,
            ],
            ['title'],
        );

        self::assertInstanceOf(StubFlower::class, $flower);
        self::assertEquals(50, $flower->id);
        self::assertEquals('Lisianthus', $flower->getTitle());
        self::assertEquals('calming2', $flower->getMeaning());

        $flower = $this->instance->select()->where('id', 50)
            ->get(StubFlower::class);

        self::assertEquals(50, $flower->id);
        self::assertEquals('Lisianthus', $flower->getTitle());
        self::assertEquals('calming2', $flower->getMeaning());
    }

    /**
     * Method to test getPrimaryKey().
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testGetMainKey()
    {
        self::assertEquals('id', $this->instance->getMainKey());
    }

    /**
     * Method to test getTable().
     *
     * @return void
     */
    public function testGetTableName()
    {
        self::assertEquals('ww_flower', $this->instance->getTableName());
    }

    /**
     * @inheritDoc
     */
    protected static function setupDatabase(): void
    {
        self::importFromFile(__DIR__ . '/Stub/data.sql');
    }
}
