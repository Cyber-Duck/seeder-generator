<?php

namespace CyberDuck\Seeder;

class Where
{
    public string $field;
    public string $expr;
    public string $value;

    public function __construct(string $field, string $expr, string $value)
    {
        $this->field = trim($field);
        $this->expr = trim($expr);
        $this->value = trim($value);
    }
}
