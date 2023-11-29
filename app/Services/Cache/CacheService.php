<?php

namespace App\Services\Cache;

use Cache;
use Closure;

class CacheService
{
    private $tags = [];
    public function __construct()
    {
    }

    public function get($key, $default = null): mixed
    {
        return Cache::get($key, $default);
    }

    public function set($key, $value, $ttl = null): bool
    {
        if (!empty($this->tags)) {
            foreach ($this->tags as $tag) {
                $item = Cache::get($tag);
                if (!$item) {
                    Cache::set($tag, [$key], $ttl);
                } else {
                    if (!in_array($key, (array)$item)) {
                        $item[] = $key;
                        Cache::set($tag, $item, $ttl);
                    }
                }
            }
        }
        return Cache::set($key, $value, $ttl);
    }

    public  function remember($key, $ttl, Closure $callback)
    {
        return Cache::remember($key, $ttl, $callback);
    }

    public function forget($key)
    {
        return Cache::forget($key);
    }

    public function flush()
    {
        if (!empty($this->tags)) {
            foreach ($this->tags as $tag) {
                $keys = Cache::get($tag);
                if (!empty($keys)) {
                    foreach ($keys as $key) {
                        Cache::forget($key);
                    }
                    Cache::forget($tag);
                }
            }
        }
    }

    public function tags($tags)
    {
        if (!is_array($tags)) {
            $tags = [$tags];
        }

        $this->tags = array_unique($tags);
        return $this;
    }
}