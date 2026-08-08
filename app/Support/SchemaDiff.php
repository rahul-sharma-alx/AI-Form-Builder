<?php

namespace App\Support;

class SchemaDiff
{
    public static function between(array $before, array $after): array
    {
        $changes = [];

        self::diffLists(
            self::indexBy($before['steps'] ?? []),
            self::indexBy($after['steps'] ?? []),
            'step',
            $changes
        );

        return [
            'summary' => self::summary($changes),
            'changes' => $changes,
        ];
    }

    protected static function diffLists(array $before, array $after, string $entity, array &$changes): void
    {
        foreach ($after as $id => $item) {
            $title = $item['title'] ?? $item['label'] ?? $entity;

            if (! isset($before[$id])) {
                $changes[] = ['change' => 'added', 'entity' => $entity, 'label' => $title];

                continue;
            }

            $old = $before[$id];

            if ($entity === 'step') {
                self::diffLists(self::indexBy($old['sections'] ?? []), self::indexBy($item['sections'] ?? []), 'section', $changes);
            } elseif ($entity === 'section') {
                self::diffLists(self::indexBy($old['fields'] ?? []), self::indexBy($item['fields'] ?? []), 'field', $changes);
            } else {
                self::diffField($old, $item, $changes);
            }
        }

        foreach ($before as $id => $item) {
            if (! isset($after[$id])) {
                $changes[] = ['change' => 'removed', 'entity' => $entity, 'label' => $item['title'] ?? $item['label'] ?? $entity];
            }
        }
    }

    protected static function diffField(array $old, array $new, array &$changes): void
    {
        $modified = [];

        foreach (array_unique(array_merge(array_keys($old), array_keys($new))) as $key) {
            if (in_array($key, ['id', 'key', 'type'], true)) {
                continue;
            }

            if (self::normalize($old[$key] ?? null) === self::normalize($new[$key] ?? null)) {
                continue;
            }

            $modified[$key] = [
                'from' => $old[$key] ?? null,
                'to' => $new[$key] ?? null,
            ];
        }

        if ($modified) {
            $changes[] = [
                'change' => 'modified',
                'entity' => 'field',
                'label' => $new['label'] ?? $old['label'] ?? 'field',
                'key' => $new['key'] ?? $old['key'] ?? null,
                'fields' => $modified,
            ];
        }
    }

    protected static function normalize(mixed $value): mixed
    {
        return ($value === '' || $value === []) ? null : $value;
    }

    protected static function indexBy(array $items): array
    {
        $byId = [];

        foreach ($items as $item) {
            if (is_array($item) && isset($item['id'])) {
                $byId[$item['id']] = $item;
            }
        }

        return $byId;
    }

    protected static function summary(array $changes): string
    {
        $counts = [];

        foreach ($changes as $change) {
            $key = $change['change'].' '.$change['entity'].'s';
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        $parts = [];
        foreach ($counts as $key => $count) {
            $parts[] = $count.' '.$key;
        }

        return $parts ? implode(', ', $parts) : 'No changes detected';
    }
}
