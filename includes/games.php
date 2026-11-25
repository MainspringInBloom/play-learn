<?php
/**
 * Game definitions stored on a lesson: lessons.game_type + lessons.game_data (JSON).
 *
 * game_data = { "title"?: string, "context"?: string, "instructions"?: string, "pairs": [...] }
 *   match_output  pairs: { "code": "...", "output": "..." }
 *   memory        pairs: { "a": "term", "b": "meaning" }
 *
 * The admin game editor writes this; api/get_game.php reads it. Add a new game type by adding
 * it to PL_GAME_TYPES here, an engine in assets/js/game-*.js, and a label in includes/scores.php.
 */

const PL_GAME_TYPES = [
    'match_output' => [
        'label'        => 'Match the Output',
        'keys'         => ['code', 'output'],
        'headings'     => ['Code', 'Output it prints'],
        'instructions' => 'Pick a code card, then pick the output it prints.',
        'multiline'    => true,
    ],
    'memory' => [
        'label'        => 'Memory Match',
        'keys'         => ['a', 'b'],
        'headings'     => ['Term', 'Meaning'],
        'instructions' => 'Flip two cards to find each term and its meaning.',
        'multiline'    => false,
    ],
];

const PL_GAME_MIN_PAIRS = 2;
const PL_GAME_MAX_PAIRS = 12;

/** Cleans a list of pairs from the editor: trims, drops empty rows, checks both sides are filled. Returns [pairs, errors]. */
function pl_clean_game_pairs(string $type, array $rows): array
{
    $keys = PL_GAME_TYPES[$type]['keys'];
    $pairs = [];
    $errors = [];
    foreach ($rows as $i => $row) {
        $x = trim((string) ($row[$keys[0]] ?? ''));
        $y = trim((string) ($row[$keys[1]] ?? ''));
        if ($x === '' && $y === '') {
            continue;
        }
        if ($x === '' || $y === '') {
            $errors[] = 'Pair ' . ($i + 1) . ' needs both sides filled in (or remove it).';
            continue;
        }
        $pairs[] = [$keys[0] => $x, $keys[1] => $y];
    }
    if (count($pairs) < PL_GAME_MIN_PAIRS && !$errors) {
        $errors[] = 'A game needs at least ' . PL_GAME_MIN_PAIRS . ' pairs.';
    }
    if (count($pairs) > PL_GAME_MAX_PAIRS) {
        $errors[] = 'A game can have at most ' . PL_GAME_MAX_PAIRS . ' pairs.';
    }
    return [$pairs, $errors];
}

/** Decodes lessons.game_data defensively. Returns the pairs array only if the game is playable, else null. */
function pl_playable_game(?string $type, ?string $json): ?array
{
    if (!$type || !isset(PL_GAME_TYPES[$type]) || !$json) {
        return null;
    }
    $data = json_decode($json, true);
    if (!is_array($data) || !isset($data['pairs']) || !is_array($data['pairs'])) {
        return null;
    }
    $keys = PL_GAME_TYPES[$type]['keys'];
    $pairs = [];
    foreach ($data['pairs'] as $p) {
        if (is_array($p) && isset($p[$keys[0]], $p[$keys[1]]) && $p[$keys[0]] !== '' && $p[$keys[1]] !== '') {
            $pairs[] = [$keys[0] => (string) $p[$keys[0]], $keys[1] => (string) $p[$keys[1]]];
        }
    }
    return count($pairs) >= PL_GAME_MIN_PAIRS ? $data + ['pairs' => $pairs] : null;
}
