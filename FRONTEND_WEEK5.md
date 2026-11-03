# Week 5 frontend: quiz page + two matching games

Everything runs today on sample data. Every server call goes through one file, `assets/js/pl-api.js`, so connecting the API means editing that file only.

## Files (paths are repo-relative)
- `student/quizzes.php` quiz list (real DB query), `student/quiz.php?quiz_id=N` the quiz
- `student/games.php` game hub, `student/game.php?type=match_output|memory[&lesson_id=N]`
- `assets/js/`: `pl-api.js` (API seam), `pl-sample-data.js` (temporary), `pl-util.js`, `quiz.js`, `game-runner.js`, `game-match-output.js`, `game-memory.js`
- `assets/css/play.css` (uses week 3 `style.css` variables)

## Connecting the API
In `pl-api.js`, each method has a `TODO(API)` comment holding the real call. For each: replace the body with that call, then set `placeholder = false`.

| PL.api method | Endpoint |
|---|---|
| `getQuiz(quizId)` | `GET /api/get_quiz.php?quiz_id=N` |
| `saveQuizScore(payload)` | `POST /api/save_quiz_score.php` `{quiz_id, time_spent_seconds, answers:[{question_id, option_id}]}`. Skipped questions are omitted; the server grades. |
| `saveGameScore(payload)` | `POST /api/save_game_score.php` `{lesson_id, score, max_score, time_spent_seconds}` |
| `getGameData(type, lessonId)` | **No endpoint exists yet** (see gaps) |

Then delete `pl-sample-data.js` and its `<script>` tags in `quiz.php` and `game.php`. Responses are `{ok:true,...}` or `{ok:false,error}`; the helper `request()` already rejects with the server's error message.

## Game data shapes
`getGameData` resolves to `{ title, context, instructions, pairs }`.
- `match_output`: `pairs: [{ code: "print(1+1)", output: "2" }, ...]`
- `memory`: `pairs: [{ a: "int", b: "A whole number" }, ...]` (a = term, b = meaning)

Match the Output suits `lessons.game_data` JSON as-is; use `game_type` for the type.

## Scoring (game-runner.js)
50 points per matched pair, minus 10 per wrong pick, never below 0. Max score = 50 x pairs. Constants `POINTS_PER_PAIR` and `MISTAKE_PENALTY` at the top of the file.

## Dev switches (sample mode only)
Add `?demo=empty`, `?demo=fail-load` or `?demo=fail-save` to a quiz or game URL. A "Sample data" badge shows while `placeholder` is true.

## Adding a game
Create `assets/js/game-<type>.js` registering `PL.games.<type> = { mount($board, data, ui) }`, call `ui.match()` / `ui.miss()`, add the type to the whitelist in `game.php`, add a card in `games.php`.

## Known gaps
1. No endpoint loads game data. Suggest `/api/get_game.php?lesson_id=N` returning `lessons.game_type` and `game_data`.
2. `save_game_score` requires `lesson_id >= 1`; sample mode and hub links use `lesson_id` 0. Hub links need real lesson ids once games are tied to lessons.
3. `play.css` is linked from the page body after the header include. It works; move it into `<head>` via a header hook if wanted.

## Testing done
93 headless-browser checks (quiz flow, load/save failures, both games, scoring, routing, role gating, text-not-HTML, 390px width) using Chromium with stand-in Bootstrap/jQuery 3.6.1, because the CDN was unreachable. Not tested: the real CDN files and any live PHP/MySQL/API calls.
