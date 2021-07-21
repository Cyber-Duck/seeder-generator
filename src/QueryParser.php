<?php

namespace CyberDuck\Seeder;

use BadMethodCallException;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use PhpMyAdmin\SqlParser\Components\SetOperation;
use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Statements\InsertStatement;
use Throwable;

class QueryParser
{
    public function parse(Entry $entry): Query
    {
        try {
            return $this->parseEntry($entry);
        } catch (Throwable $throwable) {
            throw new InvalidArgumentException(
                "Cannot parse query: {$entry->sql}\n"
                .$throwable->getMessage()
            );
        }
    }

    private function parseEntry(Entry $entry): Query
    {
        switch ($this->getQueryType($entry->sql)) {
            case 'insert':
                return $this->mapInsertQuery($entry);
            case 'update':
                return $this->mapUpdateQuery($entry);
            case 'delete':
                return $this->mapDeleteQuery($entry);
            default:
                throw new BadMethodCallException("Unsupported query type: {$entry->sql}");
        }
    }

    private function mapInsertQuery(Entry $entry): Query
    {
        $insert = (new Parser($entry->sql))->statements[0];

        return new Query([
            'entry' => $entry,
            'type' => $this->getQueryType($entry->sql),
            'table' => $insert->into->dest->table,
            'fields' => $this->getInsertFields($insert),
        ]);
    }

    private function mapUpdateQuery(Entry $entry)
    {
        $update = (new Parser($entry->sql))->statements[0];

        return new Query([
            'entry' => $entry,
            'type' => $this->getQueryType($entry->sql),
            'table' => $update->tables[0]->table,
            'fields' => $this->getUpdateFields($update),
            'conditions' => $this->getWheres($update->where),
        ]);
    }

    private function mapDeleteQuery(Entry $entry)
    {
        $delete = (new Parser($entry->sql))->statements[0];

        return new Query([
            'entry' => $entry,
            'type' => $this->getQueryType($entry->sql),
            'table' => $delete->from[0]->table,
            'conditions' => $this->getWheres($delete->where),
        ]);
    }

    private function getUpdateFields($update)
    {
        return collect($update->set)
            ->mapWithKeys(function (SetOperation $set) {
                return [$this->getColumnWithoutTable($set->column) => $set->value];
            })
            ->forget(['created_at', 'updated_at']);
    }

    private function getColumnWithoutTable($column)
    {
        if (strpos($column, '.')) {
            list($table, $column) = explode('.', $column);
        }

        return trim($column, '` ');
    }

    private function getInsertFields(InsertStatement $insert): Collection
    {
        return collect($insert->into->columns)
            ->combine($insert->values[0]->raw)
            ->forget(['created_at', 'updated_at']);
    }

    private function getQueryType(string $query): string
    {
        if (Str::startsWith($query, 'insert into')) {
            return 'insert';
        }

        if (Str::startsWith($query, 'update')) {
            return 'update';
        }

        if (Str::startsWith($query, 'delete')) {
            return 'delete';
        }

        throw new BadMethodCallException("Unsupported query type: {$query}");
    }

    private function getWheres($wheres)
    {
        return collect($wheres)
            ->map(function($where) {
                return $where->expr;
            })
            ->map(function($expr) {
                if ($expr == 'AND') {
                    return null;
                }

                if (strpos($expr, '=')) {
                    return $this->parseWhereWithSimpleExpr($expr, '=', '=');
                }

                if (strpos($expr, ' is not ')) {
                    return $this->parseWhereWithSimpleExpr($expr, 'is not', '!=');
                }

                if (strpos($expr, ' is ')) {
                    return $this->parseWhereWithSimpleExpr($expr, 'is', '=');
                }

                throw new Exception("Unsupported where expression: {$expr}");
            })
            ->filter();
    }

    private function parseWhereWithSimpleExpr($expr, $sqlSymbol, $phpSymbol)
    {
        [$field, $value] = explode($sqlSymbol, $expr);

        $column = trim($this->getColumnWithoutTable($field), ' `');

        return new Where($column, $phpSymbol, $value);
    }
}
