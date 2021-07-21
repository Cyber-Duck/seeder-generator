<?php

namespace CyberDuck\Seeder;

class Where
{
    /**
     * @var string|void
     */
    public $field;

    /**
     * @var string|void
     */
    public $expr;

    /**
     * @var string|void
     */
    public $value;

    public function __construct(string $field, string $expr, string $value)
    {
        $this->field = trim($field);
        $this->expr = trim($expr);
        $this->value = trim($value);
    }
}
