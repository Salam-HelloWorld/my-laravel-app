<?php

namespace App\Support;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

/**
 * Simple storage with NO database.
 *
 * PHP forgets everything after each request, so items are kept in Laravel's cache
 * (the "file" store, i.e. storage/framework/cache). On Render the disk is temporary,
 * so data disappears on every redeploy/restart - exactly like the in-memory FastAPI/NestJS versions.
 */
class ItemStore
{
    private const KEY = 'template.items';

    private function cache(): Repository
    {
        return Cache::store();
    }

    /** @return array{items: array<int, array<string, mixed>>, next_id: int} */
    private function load(): array
    {
        return $this->cache()->get(self::KEY, ['items' => [], 'next_id' => 1]);
    }

    private function save(array $state): void
    {
        $this->cache()->forever(self::KEY, $state);
    }

    /** @return list<array<string, mixed>> */
    public function all(): array
    {
        return array_values($this->load()['items']);
    }

    public function find(int $id): ?array
    {
        return $this->load()['items'][$id] ?? null;
    }

    public function create(array $data): array
    {
        return $this->cache()->lock(self::KEY.'.lock', 5)->block(5, function () use ($data) {
            $state = $this->load();
            $item = [
                'id' => $state['next_id'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'price' => (float) $data['price'],
                'in_stock' => (bool) ($data['in_stock'] ?? true),
            ];
            $state['items'][$item['id']] = $item;
            $state['next_id']++;
            $this->save($state);

            return $item;
        });
    }

    public function update(int $id, array $data): ?array
    {
        return $this->cache()->lock(self::KEY.'.lock', 5)->block(5, function () use ($id, $data) {
            $state = $this->load();
            if (! isset($state['items'][$id])) {
                return null;
            }
            if (array_key_exists('price', $data)) {
                $data['price'] = (float) $data['price'];
            }
            $state['items'][$id] = array_merge($state['items'][$id], $data);
            $this->save($state);

            return $state['items'][$id];
        });
    }

    public function delete(int $id): bool
    {
        return $this->cache()->lock(self::KEY.'.lock', 5)->block(5, function () use ($id) {
            $state = $this->load();
            if (! isset($state['items'][$id])) {
                return false;
            }
            unset($state['items'][$id]);
            $this->save($state);

            return true;
        });
    }

    public function clear(): void
    {
        $this->cache()->forget(self::KEY);
    }
}
