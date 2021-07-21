<?php

namespace CyberDuck\Seeder;

use Illuminate\Support\Collection;

class Query
{
    /**
     * @var Entry
     */
    public $entry;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $table;

    /**
     * @var Collection
     */
    public $fields;

    /**
     * @var Collection
     */
    public $conditions;

    public function __construct(array $attributes)
    {
        $this->entry = $attributes['entry'];
        $this->table = $attributes['table'];
        $this->type = $attributes['type'];
        $this->fields = $attributes['fields'] ?? collect();
        $this->conditions = $attributes['conditions'] ?? collect();
    }
}
