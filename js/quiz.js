// ===== GSAC PMES Quiz System =====

const quizQuestions = [
  {
    question: "What does GSAC stand for?",
    options: [
      "General Savings and Credit Association",
      "Gubat Saint Anthony Cooperative",
      "Gubat Savings and Credit Cooperative",
      "General Saint Anthony Cooperative"
    ],
    answer: 1,
    explanation: "GSAC stands for Gubat Saint Anthony Cooperative, a credit and savings cooperative serving the community of Gubat, Sorsogon."
  },
  {
    question: "What type of cooperative is GSAC?",
    options: [
      "Agricultural Cooperative",
      "Consumer Cooperative",
      "Credit and Savings Cooperative",
      "Worker Cooperative"
    ],
    answer: 2,
    explanation: "GSAC is a Credit and Savings Cooperative, providing savings accounts, share capital, and various loan products to its members."
  },
  {
    question: "What is a cooperative?",
    options: [
      "A private company owned by investors",
      "A government-run financial institution",
      "An autonomous association of persons united voluntarily to meet their common needs through a jointly owned enterprise",
      "A nonprofit charity organization"
    ],
    answer: 2,
    explanation: "A cooperative is an autonomous association of persons who voluntarily cooperate for their mutual social, economic, and cultural benefit."
  },
  {
    question: "What government agency regulates cooperatives in the Philippines?",
    options: [
      "Securities and Exchange Commission (SEC)",
      "Bangko Sentral ng Pilipinas (BSP)",
      "Cooperative Development Authority (CDA)",
      "Department of Trade and Industry (DTI)"
    ],
    answer: 2,
    explanation: "The Cooperative Development Authority (CDA) is the government agency responsible for registering and regulating cooperatives in the Philippines."
  },
  {
    question: "What is the meaning of PMES?",
    options: [
      "Primary Member Education Summary",
      "Pre-Membership Education Seminar",
      "Post-Membership Evaluation Seminar",
      "Pre-Member Enrollment System"
    ],
    answer: 1,
    explanation: "PMES stands for Pre-Membership Education Seminar, a required orientation that all prospective cooperative members must complete."
  },
  {
    question: "In a cooperative, how many votes does each member have?",
    options: [
      "Votes depend on the amount of savings",
      "Votes depend on the number of shares owned",
      "One member, one vote regardless of capital",
      "Only senior members can vote"
    ],
    answer: 2,
    explanation: "Cooperatives follow the democratic principle of 'one member, one vote' — each member has equal voting rights regardless of their share capital contribution."
  },
  {
    question: "What is Share Capital in a cooperative?",
    options: [
      "A loan given to new members",
      "A member's ownership contribution to the cooperative",
      "The cooperative's total monthly income",
      "A savings account with high interest"
    ],
    answer: 1,
    explanation: "Share Capital is a member's financial contribution that represents their ownership stake in the cooperative. It entitles them to dividends and voting rights."
  },
  {
    question: "Which of the following is a right of a cooperative member?",
    options: [
      "To demand profit distribution at any time",
      "To vote and be elected as an officer of the cooperative",
      "To withdraw share capital anytime without approval",
      "To hire and fire cooperative employees"
    ],
    answer: 1,
    explanation: "Members have the right to vote and be elected as officers during the General Assembly, following the democratic governance of cooperatives."
  },
  {
    question: "What is a Patronage Refund in a cooperative?",
    options: [
      "A penalty for late loan payments",
      "Interest paid on savings accounts",
      "A refund given to members based on their transactions with the cooperative",
      "A government subsidy for cooperatives"
    ],
    answer: 2,
    explanation: "Patronage Refund is a portion of the cooperative's net surplus returned to members based on the volume of their transactions or business with the cooperative."
  },
  {
    question: "What is the responsibility of a cooperative member?",
    options: [
      "To only deposit savings and not participate in activities",
      "To actively participate, pay obligations, and uphold cooperative principles",
      "To recruit as many new members as possible each month",
      "To monitor and audit all financial records of the cooperative"
    ],
    answer: 1,
    explanation: "Members are responsible for active participation in cooperative activities, paying their financial obligations, and upholding the cooperative's mission, vision, and values."
  }
];

let userAnswers = new Array(quizQuestions.length).fill(null);
let quizSubmitted = false;

// ===== Proceed to Quiz =====
function proceedToQuiz() {
  const checkbox = document.getElementById('videoWatched');
  if (!checkbox.checked) {
    alert('Please confirm that you have watched the PMES video before proceeding to the quiz.');
    return;
  }
  showPanel('panelQuiz');
  setStep(2);
  renderQuiz();
}

// ===== Render Quiz =====
function renderQuiz() {
  const container = document.getElementById('quizContainer');
  container.innerHTML = '';

  quizQuestions.forEach((q, index) => {
    const block = document.createElement('div');
    block.className = 'question-block';
    block.id = 'qblock-' + index;

    const optionsHTML = q.options.map((opt, oi) => `
      <label class="option" id="opt-${index}-${oi}" onclick="selectAnswer(${index}, ${oi})">
        <input type="radio" name="q${index}" value="${oi}" />
        ${opt}
      </label>
    `).join('');

    block.innerHTML = `
      <p class="question-num">Question ${index + 1} of ${quizQuestions.length}</p>
      <p class="question-text">${q.question}</p>
      <div class="options">${optionsHTML}</div>
      <div id="feedback-${index}" style="display:none;"></div>
    `;
    container.appendChild(block);
  });
}

// ===== Select Answer =====
function selectAnswer(qIndex, optIndex) {
  if (quizSubmitted) return;
  userAnswers[qIndex] = optIndex;

  document.querySelectorAll(`[id^="opt-${qIndex}-"]`).forEach(el => el.classList.remove('selected'));
  document.getElementById(`opt-${qIndex}-${optIndex}`).classList.add('selected');
  document.getElementById(`qblock-${qIndex}`).classList.add('answered');

  updateQuizProgress();
}

// ===== Update Progress Counter =====
function updateQuizProgress() {
  const answered = userAnswers.filter(a => a !== null).length;
  const el = document.getElementById('quizProgress');
  if (el) el.textContent = answered + ' / ' + quizQuestions.length + ' answered';

  const submitBtn = document.getElementById('btnSubmitQuiz');
  if (answered === quizQuestions.length) {
    submitBtn.style.display = 'block';
  }
}

// ===== Submit Quiz =====
function submitQuiz() {
  const unanswered = userAnswers.filter(a => a === null).length;
  if (unanswered > 0) {
    alert(`Please answer all questions. You have ${unanswered} unanswered question(s).`);
    return;
  }

  quizSubmitted = true;
  let score = 0;

  quizQuestions.forEach((q, index) => {
    const userAns = userAnswers[index];
    const correct = q.answer;
    const block = document.getElementById('qblock-' + index);
    const feedback = document.getElementById('feedback-' + index);

    document.querySelectorAll(`[id^="opt-${index}-"]`).forEach(el => {
      el.style.pointerEvents = 'none';
    });

    if (userAns === correct) {
      score++;
      block.classList.add('correct');
      block.classList.remove('answered');
      document.getElementById(`opt-${index}-${userAns}`).classList.add('correct-ans');
      feedback.style.display = 'block';
      feedback.innerHTML = `<span class="feedback-tag correct">✓ Correct!</span><p class="feedback-explanation">${q.explanation}</p>`;
    } else {
      block.classList.add('wrong');
      block.classList.remove('answered');
      document.getElementById(`opt-${index}-${userAns}`).classList.add('wrong-ans');
      document.getElementById(`opt-${index}-${correct}`).classList.add('correct-ans');
      feedback.style.display = 'block';
      feedback.innerHTML = `<span class="feedback-tag wrong">✗ Incorrect</span><p class="feedback-explanation">${q.explanation}</p>`;
    }
  });

  document.getElementById('btnSubmitQuiz').style.display = 'none';

  setTimeout(() => showResult(score), 800);
}

// ===== Show Result =====
function showResult(score) {
  showPanel('panelResult');
  setStep(3);

  const total = quizQuestions.length;
  const passed = score >= 7;
  const pct = Math.round((score / total) * 100);

  const box = document.getElementById('resultBox');
  const icon = document.getElementById('resultIcon');
  const title = document.getElementById('resultTitle');
  const scoreEl = document.getElementById('resultScore');
  const msg = document.getElementById('resultMessage');
  const certBox = document.getElementById('certificateBox');
  const actions = document.getElementById('resultActions');
  const certDate = document.getElementById('certDate');

  box.className = 'result-box ' + (passed ? 'pass' : 'fail');
  icon.textContent = passed ? '🎉' : '😔';
  title.textContent = passed ? 'Congratulations! You Passed!' : 'You Did Not Pass';
  scoreEl.textContent = score + ' / ' + total + ' (' + pct + '%)';

  if (passed) {
    msg.textContent = 'You passed the GSAC PMES Quiz! Please fill in your personal information to proceed with your membership application.';
    certBox.style.display = 'block';
    certDate.textContent = new Date().toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' });
    actions.innerHTML = `
      <button class="btn btn-primary" onclick="showPanel('panelInfo'); setStep(3);">Fill in Your Information &rarr;</button>
    `;
  } else {
    msg.textContent = 'You need at least 7 out of 10 correct answers to pass. Please review the material and take the quiz again.';
    actions.innerHTML = `
      <button class="btn btn-blue" onclick="retakeQuiz()">Retake Quiz</button>
      <button class="btn btn-outline" style="background:var(--primary);color:white;" onclick="showPanel('panelVideo'); setStep(1);">Re-watch Video</button>
    `;
  }
}

// ===== Retake Quiz =====
function retakeQuiz() {
  userAnswers = new Array(quizQuestions.length).fill(null);
  quizSubmitted = false;
  showPanel('panelQuiz');
  setStep(2);
  renderQuiz();
  document.getElementById('btnSubmitQuiz').style.display = 'none';
  document.getElementById('quizProgress').textContent = '0 / 10 answered';
}

// ===== Panel & Step Helpers =====
function showPanel(id) {
  document.querySelectorAll('.pmes-panel').forEach(p => p.classList.remove('active'));
  document.getElementById(id).classList.add('active');
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function setStep(num) {
  document.querySelectorAll('.step').forEach((el, i) => {
    el.classList.remove('active', 'done');
    if (i + 1 < num) el.classList.add('done');
    if (i + 1 === num) el.classList.add('active');
  });
}

// expose setStep globally so inline onclick in pmes.html can call it
window.setStep = setStep;
window.showPanel = showPanel;
