<?php

namespace CyberDuck\Seeder;

use Illuminate\Support\Str;
use Laravel\Telescope\Storage\EntryModel;

class Entry
{
    /**
     * @var string
     */
    public $sql;

    /**
     * @var string|null
     */
    public $relatedModel = null;
    /**
     * @var string|null
     */
    public $relatedAction = null;

    public function __construct($entry, ?EntryModel $relatedModel = null)
    {
        $this->sql = $entry instanceof EntryModel ? $entry->content['sql'] : $entry;

        if ($relatedModel) {
            $this->relatedModel = $relatedModel->content['model'];
            $this->relatedAction = $relatedModel->content['action'];
        }
    }

    public function relatedModelDescription(): string
    {
        if (! $this->relatedModel) {
            return '';
        }

        return "{$this->relatedAction}: {$this->relatedModel}";
    }

    public function generateVariableName(): string
    {
        if (! $this->relatedModel) {
            return '$'.Str::camel(Str::random(8));
        }

        [$modelName, $modelId] = explode(':', $this->relatedModel);

        return '$'.Str::camel(class_basename($modelName)).$modelId;
    }
}
