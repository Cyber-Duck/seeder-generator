<?php

namespace CyberDuck\Seeder;

use Illuminate\Support\Str;
use Laravel\Telescope\Storage\EntryModel;

class TelescopeExporter
{
    public function getDMSAfter($sequence = null)
    {
        return $this->getDataManipulationStatementsAfter($sequence);
    }

    public function getDataManipulationStatementsAfter($sequence = null)
    {
        if (! is_numeric($sequence)) {
            $sequence = $this->getSequenceOfEntry($sequence);
        }

        return $this->getDataManipulationEntries($sequence)
            ->map(function (EntryModel $entry) {
                return $this->mapEntry($entry);
            })
            ->values();
    }

    private function mapEntry(EntryModel $entry)
    {
        return new Entry($entry, $this->getRelatedModel($entry));
    }

    private function getRelatedModel(EntryModel $entry): ?EntryModel
    {
        if (! Str::startsWith($entry->content['sql'], ['insert into', 'update '])) {
            return null;
        }

        return EntryModel::query()
            ->where('batch_id', $entry->batch_id)
            ->where('type', 'model')
            ->where('sequence', '>', $entry->sequence)
            ->first();
    }

    private function getSequenceOfEntry(string $uuid)
    {
        $entry = EntryModel::where('uuid', $uuid)->firstOrFail();
        return $entry->sequence;
    }

    private function getDataManipulationEntries($sequence = null)
    {
        return $this->getSqlEntries($sequence)
            ->filter(function (EntryModel $entry) {
                return Str::startsWith($entry->content['sql'], ['insert into', 'update ', 'delete from']);
            })
            ->reject(function (EntryModel $entry) {
                return Str::startsWith($entry->content['sql'], [
                    'insert into `sessions`',
                    'insert into `migrations`',
                    'insert into `jobs`',
                    'update `sessions`',
                    'delete from `sessions`',
                    'delete from `jobs`',
                ]);
            });
    }

    private function getSqlEntries($sequence = null)
    {
        return EntryModel::query()
            ->where('type', 'query')
            ->when($sequence, function ($q, $sequence) {
                return $q->where('sequence', '>', $sequence);
            })
            ->orderBy('sequence', 'ASC')
            ->get();
    }
}
