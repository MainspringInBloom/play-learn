<?php
/**
 * Small UTF-8 string helpers that work without the mbstring extension
 * (it is switched off by default in a fresh Windows PHP install).
 */

/** Length in characters (not bytes). */
function pl_strlen(string $s): int
{
    $n = preg_match_all('/./su', $s);
    return $n === false ? strlen($s) : $n;
}

/** The first $max characters of $s, with an ellipsis added only if something was cut. */
function pl_truncate(string $s, int $max): string
{
    if (pl_strlen($s) <= $max) {
        return $s;
    }
    return (preg_match('/^.{0,' . $max . '}/su', $s, $m) ? $m[0] : substr($s, 0, $max)) . '…';
}
