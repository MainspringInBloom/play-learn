// Play and Learn — Match the Output game
// PAIRS below simulates lessons.game_data (a JSON column). Once Teammate 2
// exposes an endpoint like api/get_lesson.php?id=4, replace FAKE_LESSON
// with a real fetch and parse its game_data JSON into this same shape.

const FAKE_LESSON = {
  lesson_id: 4,
  title: 'Match the Output',
  course_title: 'Python 101',
  unit_title: 'Loops & Conditionals',
  game_type: 'match',
  game_data: [
    { id: 1, code: 'for i in range(3):\n    print(i)', output: '0\n1\n2' },
    { id: 2, code: 'x = 5\nif x > 3:\n    print("big")', output: 'big' },
    { id: 3, code: 'n = 0\nwhile n < 2:\n    print(n)\n    n += 1', output: '0\n1' },
    { id: 4, code: 'for c in "hi":\n    print(c)', output: 'h\ni' }
  ]
};

let PAIRS = [];
let matched = {};
let pickedCode = null;
let pickedOutput = null;
let score = 0;

function escapeHtml(str) {
  return $('<div>').text(str).html();
}

function render() {
  const $code = $('#codeCards').empty();
  const $output = $('#outputCards').empty();

  PAIRS.forEach(function (p) {
    const isMatched = !!matched[p.id];
    const codeCls = isMatched ? 'matched' : (pickedCode === p.id ? 'picked' : '');
    const outputCls = isMatched ? 'matched' : (pickedOutput === p.id ? 'picked' : '');

    $code.append(
      $('<button>')
        .addClass('pl-game-card pl-mono ' + codeCls)
        .attr('data-pair-id', p.id)
        .attr('disabled', isMatched)
        .css({ whiteSpace: 'pre', fontSize: '14px', lineHeight: '1.5' })
        .text(p.code)
        .on('click', function () { pickCode(p.id); })
    );

    $output.append(
      $('<button>')
        .addClass('pl-game-card pl-mono ' + outputCls)
        .attr('data-pair-id', p.id)
        .attr('disabled', isMatched)
        .css({ whiteSpace: 'pre', fontSize: '14px' })
        .text(p.output)
        .on('click', function () { pickOutput(p.id); })
    );
  });

  $('#scoreValue').text(score);
  $('#matchedValue').text(Object.keys(matched).length + '/' + PAIRS.length);
}

function pickCode(id) {
  if (matched[id]) return;
  pickedCode = id;
  $('#gameMessage').text('Now pick the matching output.');
  render();
  tryMatch();
}

function pickOutput(id) {
  if (matched[id]) return;
  pickedOutput = id;
  render();
  tryMatch();
}

function tryMatch() {
  if (pickedCode === null || pickedOutput === null) return;

  if (pickedCode === pickedOutput) {
    matched[pickedCode] = true;
    score += 50;
    $('#gameMessage').text('Matched! Nice work.');
  } else {
    $('#gameMessage').text('Not quite a match — try again.');
  }
  pickedCode = null;
  pickedOutput = null;
  render();

  if (Object.keys(matched).length === PAIRS.length) {
    $('#gameMessage').text('All matched! Final score ' + score + '. Click "Finish game" to save it.');
  }
}

function resetGame() {
  matched = {};
  pickedCode = null;
  pickedOutput = null;
  score = 0;
  $('#gameMessage').text('Pick a code card to begin.');
  render();
}

function finishGame() {
  // TODO (Teammate 2): POST { lesson_id, student_id, score } to something
  // like api/save_progress.php, which writes a row into lesson_progress
  // (and quiz_attempts if this game is scored like a quiz).
  $('#gameMessage').text('Game finished — final score ' + score + '. (Not yet saved to the server.)');
}

$(function () {
  // TODO (swap once backend is ready):
  // $.getJSON('/api/get_lesson.php', { id: 4 }).done(function (lesson) {
  //   $('#gameTitle').text(lesson.title);
  //   $('#gameSubtitle').text(lesson.course_title + ' · ' + lesson.unit_title + '. ...');
  //   PAIRS = lesson.game_data;
  //   render();
  // });

  $('#gameTitle').text(FAKE_LESSON.title);
  $('#gameSubtitle').text(
    FAKE_LESSON.course_title + ' · ' + FAKE_LESSON.unit_title + '. Pick a code card, then pick the output it prints.'
  );
  PAIRS = FAKE_LESSON.game_data;
  render();

  $('#resetBtn').on('click', resetGame);
  $('#finishBtn').on('click', finishGame);
});
