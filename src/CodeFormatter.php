<?php

namespace CyberDuck\Seeder;

use Illuminate\Support\Collection;
use InvalidArgumentException;

class CodeFormatter
{
    public static function normalizeBlock($codeBlock)
    {
        if (is_array($codeBlock)) {
            $codeBlock = collect($codeBlock);
        }

        if ($codeBlock instanceof Collection) {
            return $codeBlock->filter()->join("\n");
        }

        if (is_string($codeBlock)) {
            return $codeBlock;
        }

        throw new InvalidArgumentException('Invalid code block');
    }

    public static function indent(int $level, $codeBlock): string
    {
        $indentation = str_repeat(" ", $level * 4);

        return $indentation.str_replace(
            PHP_EOL,
            PHP_EOL. $indentation,
            static::normalizeBlock($codeBlock)
        );
    }
}
