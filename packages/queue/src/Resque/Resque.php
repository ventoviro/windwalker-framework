<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Queue\Resque;

use Resque as PhpResque;

/**
 * The Resque class.
 *
 * @since  3.2
 */
class Resque extends PhpResque
{
    /**
     * Remove items of the specified queue
     *
     * @param string $queue The name of the queue to fetch an item from.
     * @param array  $items
     *
     * @return integer number of deleted items
     */
    public static function dequeue(string $queue, array $items = []): int
    {
        if (count($items) > 0) {
            return self::removeItems($queue, $items);
        }

        return self::removeList($queue);
    }

    /**
     * Remove Items from the queue
     * Safely moving each item to a temporary queue before processing it
     * If the Job matches, counts otherwise puts it in a requeue_queue
     * which at the end eventually be copied back into the original queue
     *
     * @private
     *
     * @param string $queue The name of the queue
     * @param array  $items
     *
     * @return integer number of deleted items
     */
    protected static function removeItems(string $queue, array $items = []): int
    {
        $counter = 0;
        $originalQueue = 'queue:' . $queue;
        $tempQueue = $originalQueue . ':temp:' . time();
        $requeueQueue = $tempQueue . ':requeue';

        // move each item from original queue to temp queue and process it
        $finished = false;

        while (!$finished) {
            $string = self::redis()->rpoplpush($originalQueue, self::redis()->getPrefix() . $tempQueue);

            if (!empty($string)) {
                if (self::matchItem($string, $items)) {
                    self::redis()->rpop($tempQueue);
                    $counter++;
                } else {
                    self::redis()->rpoplpush($tempQueue, self::redis()->getPrefix() . $requeueQueue);
                }
            } else {
                $finished = true;
            }
        }

        // move back from temp queue to original queue
        $finished = false;

        while (!$finished) {
            $string = self::redis()->rpoplpush($requeueQueue, self::redis()->getPrefix() . $originalQueue);

            if (empty($string)) {
                $finished = true;
            }
        }

        // remove temp queue and requeue queue
        self::redis()->del($requeueQueue);
        self::redis()->del($tempQueue);

        return $counter;
    }

    /**
     * matching item
     * item can be ['class'] or ['class' => 'id'] or ['class' => {:foo => 1, :bar => 2}]
     *
     * @private
     *
     * @params string $string redis result in json
     * @params $items
     *
     * @return (bool)
     */
    protected static function matchItem($string, $items): bool
    {
        $decoded = json_decode($string, true);

        foreach ($items as $key => $val) {
            // class name only  ex: item[0] = ['class']
            if (is_numeric($key)) {
                if ($decoded['class'] == $val) {
                    return true;
                }
                // class name with args , example: item[0] = ['class' => {'foo' => 1, 'bar' => 2}]
            } elseif (is_array($val)) {
                $decodedArgs = (array) $decoded['args'][0];

                if ($decoded['class'] == $key && count($decodedArgs) > 0
                    && count(array_diff($decodedArgs, $val)) == 0) {
                    return true;
                }
                // class name with ID, example: item[0] = ['class' => 'id']
            } else {
                if ($decoded['class'] == $key && $decoded['id'] == $val) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Remove List
     *
     * @private
     *
     * @params string $queue the name of the queue
     *
     * @return integer number of deleted items belongs to this list
     */
    protected static function removeList($queue): int
    {
        $counter = self::size($queue);
        $result = self::redis()->del('queue:' . $queue);

        return ($result == 1) ? $counter : 0;
    }

    /**
     * Generate an identifier to attach to a job for status tracking.
     *
     * @return string
     */
    public static function generateJobId(): string
    {
        return md5(uniqid('', true));
    }
}
