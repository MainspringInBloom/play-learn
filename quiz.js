// Play and Learn — quiz page
// FAKE_QUIZ simulates what api/get_quiz.php?id=1 will return once Teammate 2
// builds it: quizzes.title, then a list of questions each with their
// answer_options (matching the questions / answer_options tables).

const FAKE_QUIZ = {
  quiz_id: 1,
  title: 'Python 101: Variables & Data Types',
  questions: [
    {
      question_id: 3,
      question_text: 'x = 7\ny = "7"\nprint(type(x) == type(y))',
      prompt: 'What does this code print?',
      options: [
        { option_id: 'a', option_text: 'True', is_correct: 0 },
        { option_id: 'b', option_text: 'False', is_correct: 1 },
        { option_id: 'c', option_text: 'TypeError', is_correct: 0 },
        { option_id: 'd', option_text: '7', is_correct: 0 }
      ]
    }
  ]
};

let questions = [];
let currentIndex = 0;
let selectedOptionId = null;
let checked = false;
let attemptScore = 0;
// Each answered question gets pushed here, matching quiz_responses shape.
const responses = [];

function renderQuestion() {
  const q = questions[currentIndex];

  $('#quizProgressLabel').text('Question ' + (currentIndex + 1) + ' of ' + questions.length);
  $('#quizProgressFill').css('width', (((currentIndex + 1) / questions.length) * 100) + '%');
  $('#questionCode').text(q.question_text);
  $('#questionPrompt').text(q.prompt || 'What does this code print?');

  const $opts = $('#optionsList').empty();
  q.options.forEach(function (opt) {
    $opts.append(
      $('<button>')
        .addClass('pl-option')
        .attr('data-option-id', opt.option_id)
        .html('<span class="pl-dot"></span><span class="pl-mono">' + $('<div>').text(opt.option_text).html() + '</span>')
        .on('click', function () { pickOption(opt.option_id); })
    );
  });

  $('#quizFeedback').text('Choose an answer to check it.');
  $('#nextBtn').prop('disabled', true);
  selectedOptionId = null;
  checked = false;
}

function pickOption(optionId) {
  if (checked) return;
  const q = questions[currentIndex];
  const opt = q.options.find(function (o) { return o.option_id === optionId; });

  selectedOptionId = optionId;
  checked = true;

  $('#optionsList .pl-option').each(function () {
    const $el = $(this);
    const id = $el.attr('data-option-id');
    $el.removeClass('selected correct incorrect');
    if (id === optionId) {
      $el.addClass(opt.is_correct ? 'correct' : 'incorrect');
    }
  });

  $('#quizFeedback').text(opt.is_correct ? 'Correct!' : 'Not quite — check it again next time.');
  $('#nextBtn').prop('disabled', false);

  if (opt.is_correct) attemptScore += 1;

  // Records the response the same shape quiz_responses expects.
  responses.push({
    question_id: q.question_id,
    option_id: optionId,
    is_correct: opt.is_correct ? 1 : 0
  });
}

function nextQuestion() {
  if (currentIndex < questions.length - 1) {
    currentIndex += 1;
    renderQuestion();
  } else {
    finishQuiz();
  }
}

function finishQuiz() {
  const pct = Math.round((attemptScore / questions.length) * 100);
  $('#quizFeedback').text('Quiz finished — score ' + pct + '%. (Not yet saved to the server.)');
  $('#nextBtn').prop('disabled', true);

  // TODO (Teammate 2): POST this to something like api/submit_quiz.php,
  // which inserts one row into quiz_attempts (with the score) and one row
  // per answer into quiz_responses:
  // { quiz_id, student_id, score: pct, responses }
  console.log('Quiz submission payload:', {
    quiz_id: FAKE_QUIZ.quiz_id,
    score: pct,
    responses: responses
  });
}

$(function () {
  // TODO (swap once backend is ready):
  // $.getJSON('/api/get_quiz.php', { id: 1 }).done(function (quiz) {
  //   $('#quizTitle').text(quiz.title);
  //   questions = quiz.questions;
  //   renderQuestion();
  // });

  $('#quizTitle').text(FAKE_QUIZ.title);
  questions = FAKE_QUIZ.questions;
  renderQuestion();

  $('#nextBtn').on('click', nextQuestion);
  $('#skipBtn').on('click', nextQuestion);
});
