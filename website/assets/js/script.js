function corriger() {
  let score = 0;
  const questions = document.querySelectorAll('.question-block');

  questions.forEach(block => {
    const correct = block.dataset.correct;
    const name = block.dataset.question;
    const reponseChoisie = document.querySelector(`input[name="${name}"]:checked`);

    const reponseBonDiv = block.querySelector('.reponse_bon');
    const reponseMauvaisDiv = block.querySelector('.reponse_mauvais');
    const explicationDiv = block.querySelector('.explication');

    // Réinitialisation
    block.style.border = "none";
    reponseBonDiv.style.display = "none";
    reponseMauvaisDiv.style.display = "none";
    explicationDiv.style.display = "none";

    if (reponseChoisie) {
      if (reponseChoisie.value === correct) {
        score++;
        block.style.border = "2px solid green";
        reponseBonDiv.style.display = "block";
      } else {
        block.style.border = "2px solid red";
        reponseMauvaisDiv.style.display = "block";
      }
      explicationDiv.style.display = "block";
    } else {
      block.style.border = "2px dashed orange";
    }
  });

  document.getElementById("score").innerHTML = `<h3>Score : ${score} / ${questions.length}</h3>`;
}

async function loadQuiz() {
  try {
 
    const data = quizData;

    document.getElementById('course-title').textContent = data.courseTitle;

    let currentIndex = 0;
	let score = 0; // score total
    const container = document.getElementById('quiz-container');

    function showQuestion(index) {
      container.innerHTML = '';

      const item = data.quiz[index];
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
			score++; // 🎯 bonne réponse = +1
          } else {
            btn.classList.add('incorrect');
            const correct = [...all].find(c => c.textContent === item.answer);
            if (correct) correct.classList.add('correct');
          }

          const exp = document.createElement('div');
          exp.className = 'explanation';
          exp.textContent = `💡 ${item.explanation}`;
          block.appendChild(exp);

          btnNext.disabled = false;
        });

        block.appendChild(btn);
      });

      container.appendChild(block);

      const btnNext = document.createElement('button');
      btnNext.textContent = index < data.quiz.length - 1 ? 'Question suivante' : 'Fin du quiz';
      btnNext.disabled = true;
      btnNext.style.marginTop = '15px';
      btnNext.addEventListener('click', () => {
        if (index < data.quiz.length - 1) {
          currentIndex++;
          showQuestion(currentIndex);
        } else {
			
          container.innerHTML = `<h2>Quiz terminé !</h2>`;
		  showScore();
        }
      });
      container.appendChild(btnNext);
    }
	
	function showScore() {
      container.innerHTML = `
        <h2>Quiz terminé !</h2>
        <p>Votre score : <strong>${score} / ${data.quiz.length}</strong></p>
      `;
	}
	
	
    showQuestion(currentIndex);

   
    const btnDownload = document.getElementById('download-pdf');
    btnDownload.addEventListener('click', () => generatePdf(data));

  } catch (error) {
    document.getElementById('course-title').textContent = "Erreur lors du chargement du quiz.";
    console.error("Erreur : ", error);
  }
}


function generatePdf(quizData) {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF();

  let y = 10;
  doc.setFontSize(14);
  doc.text(quizData.courseTitle, 10, y);
  y += 10;

  quizData.quiz.forEach((item, index) => {
    doc.setFontSize(12);
    doc.text(`Q${index + 1}. ${item.question}`, 10, y);
    y += 8;

    item.choices.forEach(choice => {
      doc.text(`- ${choice}`, 15, y);
      y += 6;
    });

    y += 4;

    if (y > 270) {
      doc.addPage();
      y = 10;
    }
  });

  doc.addPage();
  y = 10;
  doc.setFontSize(14);
  doc.text('Réponses et explications', 10, y);
  y += 10;

  quizData.quiz.forEach((item, index) => {
    doc.setFontSize(12);
    doc.text(`Q${index + 1} : ${item.answer}`, 10, y);
    y += 8;

    doc.setFontSize(10);
    doc.text(`💡 ${item.explanation}`, 12, y);
    y += 10;

    if (y > 270) {
      doc.addPage();
      y = 10;
    }
  });

  doc.save('quiz.pdf');
}


window.onload = loadQuiz;