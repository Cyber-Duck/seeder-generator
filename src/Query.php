<?php

namespace CyberDuck\Seeder;

use Illuminate\Support\Collection;

class Query
{
    public Entry $entry;

    public string $type;

    public string $table;

    public Collection $fields;

    public Collection $conditions;

    public function __construct(array $attributes)
    {
        $this->entry = $attributes['entry'];
        $this->table = $attributes['table'];
        $this->type = $attributes['type'];
        $this->fields = $attributes['fields'] ?? collect();
        $this->conditions = $attributes['conditions'] ?? collect();
    }
}
