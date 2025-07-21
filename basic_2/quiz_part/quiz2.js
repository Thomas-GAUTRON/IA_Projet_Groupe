window.onload = () => {
  const data = {
    courseTitle: "Test cours",
    quiz: [
      {
        question: "Quelle est la capitale de la France ?",
        choices: ["Paris", "Lyon", "Marseille"],
        answer: "Paris",
        explanation: "Paris est la capitale."
      }
    ]
  };

  document.getElementById('course-title').textContent = data.courseTitle;
  const container = document.getElementById('quiz-container');

  data.quiz.forEach((item, index) => {
    const block = document.createElement('div');
    block.className = 'question-block';

    const question = document.createElement('h3');
    question.textContent = `Q${index + 1}. ${item.question}`;
    block.appendChild(question);

    item.choices.forEach(choice => {
      const btn = document.createElement('div');
      btn.className = 'choice';
      btn.textContent = choice;

      btn.addEventListener('click', () => {
        const all = block.querySelectorAll('.choice');
        all.forEach(c => c.style.pointerEvents = 'none');

        if (choice === item.answer) {
          btn.classList.add('correct');
        } else {
          btn.classList.add('incorrect');
          const correct = [...all].find(c => c.textContent === item.answer);
          if (correct) correct.classList.add('correct');
        }

        const exp = document.createElement('div');
        exp.className = 'explanation';
        exp.textContent = `💡 ${item.explanation}`;
        block.appendChild(exp);
      });

      block.appendChild(btn);
    });

    container.appendChild(block);
  });
};
