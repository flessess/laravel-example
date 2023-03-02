<?php

namespace App\Services\BaseSpanner\Debugbar\Storage;

use DebugBar\Storage\StorageInterface;

/**
 * Stores collected data into Redis
 *
 * Implements FIFO list with automatic deletion of oldest records
 */
class RedisStorageFixedFifo implements StorageInterface
{
    protected $redis;

    protected $hash;

    /**
     * @var int items to keep in fifo memory
     */
    protected $fifoMaxLength;

    /**
     * @param  mixed $redis Redis Client
     * @param  int $fifoMaxLength
     * @param  string $hash
     */
    public function __construct($redis = null, $fifoMaxLength = null, $hash = 'phpdebugbar')
    {
        if (!$redis) {
            $redis = app('redis')->connection()->client();
        }
        $this->redis = $redis;
        $this->hash = $hash;
        $this->fifoMaxLength = $fifoMaxLength ?: config('debugbar.storage.fifo_length', 1000);
    }

    /**
     * Saves collected data
     *
     * @param string $id
     * @param string|array $data
     */
    public function save($id, $data)
    {
        if (is_array($data) && isset($data['__meta'])) {
            $this->redis->hset("{$this->hash}:meta", $id, serialize($data['__meta']));
            unset($data['__meta']);
        }
        $this->redis->hset("{$this->hash}:data", $id, serialize($data));
        $listLen = $this->redis->lpush("{$this->hash}:list", $id);

        // remove oldest items
        if ($listLen > $this->fifoMaxLength) {
            $id = $this->redis->rpop("{$this->hash}:list");
            $this->redis->hdel("{$this->hash}:meta", $id);
            $this->redis->hdel("{$this->hash}:data", $id);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        return array_merge(
            unserialize($this->redis->hget("{$this->hash}:data", $id)),
            ['__meta' => unserialize($this->redis->hget("{$this->hash}:meta", $id))]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function find(array $filters = [], $max = 20, $offset = 0)
    {
        $results = [];
        $cursor = "0";
        do {
            $list = $this->redis->hscan("{$this->hash}:meta", $cursor);
            if (isset($list[0]) && isset($list[1])) {
                list($cursor, $data) = $list;
            } else {
                // redis extension changes cursor in memory
                $data = $list;
            }
            foreach ($data as $meta) {
                if ($meta = unserialize($meta)) {
                    if ($this->filter($meta, $filters)) {
                        $results[] = $meta;
                    }
                }
            }
        } while ($cursor);

        usort($results, function ($left, $right) {
            return $right['utime'] <=>  $left['utime'];
        });

        return array_slice($results, $offset, $max);
    }

    /**
     * Filter the metadata for matches.
     *
     * @param array $meta
     * @param array $filters
     */
    protected function filter($meta, $filters)
    {
        foreach ($filters as $key => $value) {
            if (!isset($meta[$key]) || fnmatch($value, $meta[$key]) === false) {
                return false;
            }
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->redis->del("{$this->hash}:list");
        $this->redis->del("{$this->hash}:meta");
        $this->redis->del("{$this->hash}:data");
    }
}
