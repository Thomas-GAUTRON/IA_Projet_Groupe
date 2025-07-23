async function buildQuiz() {
  try {

    let data = (typeof quizData !== 'undefined') ? quizData : null;
    if (!data) {
      const qtxt = document.getElementById('quiz-input');
      if (qtxt && qtxt.value.trim()) {
        try {
          data = JSON.parse(qtxt.value.trim());
          window.quizData = data; // définir la variable globale pour les prochains appels
        } catch (e) {
          console.error('JSON parse error depuis #quiz-input', e);
        }
      }
    }

    if (!data || !data.quiz) {
      console.error('Aucun quiz valide trouvé.');
      return;
    }

    console.log(data);
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

      // Créer le bouton "Question suivante" ici pour qu'il soit accessible
      // dans l'écouteur d'événement des choix.
      const btnNext = document.createElement('button');
      btnNext.className = 'btn';
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
        if (window.MathJax) MathJax.typesetPromise([container]);
      });

      item.choices.forEach(choice => {
        const btn = document.createElement('div');
        btn.className = 'choice';
        btn.textContent = choice;

        btn.addEventListener('click', () => {
          const all = block.querySelectorAll('.choice');
          all.forEach(c => c.style.pointerEvents = 'none');

          if (choice === item.answer) {
            btn.classList.add('correct');
            score++;
          } else {
            btn.classList.add('incorrect');
            const correct = [...all].find(c => c.textContent === item.answer);
            if (correct) correct.classList.add('correct');
          }

          const exp = document.createElement('div');
          exp.className = 'explanation';
          exp.textContent = `💡 ${item.explanation}`;
          block.appendChild(exp); // L'explication sera ajoutée ici

          // Le bouton "Suivant" est maintenant activé, et l'explication est visible
          btnNext.disabled = false;
          if (window.MathJax) MathJax.typesetPromise([block]);
        });

        block.appendChild(btn);
      });

      // Ajouter le bloc de question et le bouton "suivant" au conteneur principal
      container.appendChild(block);
      block.appendChild(btnNext); // Le bouton est dans le bloc de la question

      if (window.MathJax) MathJax.typesetPromise([container]);
    }

    function showScore() {
      container.innerHTML = `
        <h2>Quiz terminé !</h2>
        <p>Votre score : <strong>${score} / ${data.quiz.length}</strong></p>
      `;
      if (window.MathJax) MathJax.typesetPromise([container]);
    }


    showQuestion(currentIndex);


    const btnDownload = document.getElementById('download-pdf');
    btnDownload.addEventListener('click', () => downloadQuizPdfFromPython(data));

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

function downloadQuizPdfFromPython(quizData) {
  fetch('http://127.0.0.1:5000/json_quiz_to_pdf', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ quiz_json: quizData })
  })
    .then(res => res.json())
    .then(data => {
      if (data.pdf_path) {
        var link = document.createElement('a');
        link.href = data.pdf_path;
        //   link.download = 'quiz.pdf'; // Nom du fichier pour le téléchargement
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      } else {
        alert('Erreur lors de la génération du PDF : ' + data.error);
      }
    })
    .catch(err => alert('Erreur réseau : ' + err));
}

// Conversion automatique au chargement
window.onload = function () {
  // Vérifier que MathJax est chargé
  function checkMathJax() {
    if (window.MathJax && window.MathJax.typesetPromise) {
      console.log('MathJax loaded successfully');
    } else {
      console.log('Waiting for MathJax to load...');
      setTimeout(checkMathJax, 500);
    }
  }
  checkMathJax();
};

// ----- NOUVELLE GESTION MULTI-COURS -----

window.courses = []; // Un seul tableau pour tout gérer

// Fonction pour parser et nettoyer le JSON du quiz
function parseQuizJson(q) {
  if (typeof q !== 'string') return q;
  let cleaned = q;
  const firstBrace = cleaned.indexOf('{');
  const lastBrace = cleaned.lastIndexOf('}');
  if (firstBrace !== -1 && lastBrace !== -1) {
    cleaned = cleaned.substring(firstBrace, lastBrace + 1);
  }
  cleaned = cleaned.replace(/\r?\n/g, "\\n").replace(/\\(?!["\\\/bfnrtu0-9])/g, "\\\\$&").replace(/\\\\/g, "\\");
  try {
    const obj = JSON.parse(cleaned);
    return (obj && obj.quiz) ? obj : null;
  } catch (e) {
    console.error('Erreur de parsing JSON', e, "sur la chaîne:", q);
    return null;
  }
}

// Si des données initiales sont injectées depuis PHP
if (typeof window.initialQuizArray !== 'undefined' && Array.isArray(window.initialQuizArray)) {
  let initialQuizzes = window.initialQuizArray.map(parseQuizJson).filter(q => q);
  let initialResumes = Array.isArray(window.initialResumeArray) ? window.initialResumeArray : [];

  // Créer une structure de cours unifiée
  const numCourses = Math.max(initialQuizzes.length, initialResumes.length);
  for (let i = 0; i < numCourses; i++) {
    const quiz = initialQuizzes[i] || null;
    const resume = initialResumes[i] || null;
    if (quiz || resume) {
      let title = `Cours ${i + 1}`;
      if (quiz && quiz.courseTitle) {
        title = quiz.courseTitle;
      } else if (resume) {
        const match = resume.match(/section\*\{([^}]+)\}/);
        if (match) title = match[1].replace(/\\[a-zA-Z]+/g, '').trim();
      }
      window.courses.push({ title: title, quizData: quiz, resumeData: resume });
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    populateCourseSelector();
    loadCourse(0);
  });
}


function populateCourseSelector() {
  const sel = document.getElementById('course-select');
  if (!sel) return;
  sel.innerHTML = '';
  window.courses.forEach((course, idx) => {
    const opt = document.createElement('option');
    opt.value = idx;
    opt.textContent = course.title;
    sel.appendChild(opt);
  });
  sel.style.display = window.courses.length > 1 ? 'block' : 'none';
  sel.addEventListener('change', () => loadCourse(parseInt(sel.value)));
}

function loadCourse(index) {
    
  if (index < 0 || index >= window.courses.length) return;;
  const course = window.courses[index];

  // Mettre à jour le titre
  document.getElementById('course-title').textContent = course.title;

  // Gérer le quiz - charger les données sans gérer l'affichage du pane
  if (course.quizData && course.quizData.quiz && course.quizData.quiz.length > 0) {
    window.quizData = course.quizData;
    if (typeof buildQuiz === 'function') buildQuiz();
  } else {
    document.getElementById('quiz-container').innerHTML = '<p>Aucun quiz disponible pour ce cours.</p>';
  }

  try{
  // Gérer le résumé - charger les données sans gérer l'affichage du pane
  if (course.resumeData) {
    document.getElementById('latex-input').value = course.resumeData;
    if (typeof generatePdfFromLatex === 'function') generatePdfFromLatex();
  } else {
    // Hiding the iframe container is fine, as it's not a main pane
    document.getElementById('pdf-frame').style.display = 'none';
    document.getElementById('pdf-container').innerHTML = '<p>Aucun résumé disponible pour ce cours.</p>';
  }
  }
  catch(e){
    console.error("Erreur lors de la génération du résumé : ", e);
  }
      
  // Gérer l'état des onglets et des panneaux
  updateTabsAndPanesForCourse(course);
}


function updateTabsAndPanesForCourse(course) {
    const hasQuiz = course && course.quizData && course.quizData.quiz.length > 0;
    const hasResume = course && course.resumeData;

    const quizTab = document.querySelector('.tab-button[data-view="quiz"]');
    const resumeTab = document.querySelector('.tab-button[data-view="resume"]');
    const bothTab = document.querySelector('.tab-button[data-view="both"]');

    // Mettre à jour la visibilité des boutons d'onglets
    if (quizTab) quizTab.style.display = (hasQuiz && hasResume) ? 'inline-block' : 'none';
    if (resumeTab) resumeTab.style.display = (hasQuiz && hasResume) ? 'inline-block' : 'none';
    if (bothTab) bothTab.style.display = (hasQuiz && hasResume) ? 'inline-block' : 'none';

    // Définir l'onglet actif par défaut
    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));

    let activeTab;
    if (hasQuiz && hasResume) {
        activeTab = bothTab;
    } else if (hasQuiz) {
        activeTab = quizTab;
    } else if (hasResume) {
        activeTab = resumeTab;
    }

    if (activeTab) {
        activeTab.classList.add('active');
        // Mettre à jour les panneaux en fonction de l'onglet actif
        updatePanesVisibility({ target: activeTab });
    }
}

function updatePanesVisibility(event) {
  // Récupérer le bouton cliqué
  const clickedButton = event.target;

  // Retirer la classe 'active' de tous les boutons
  document.querySelectorAll('.tab-button').forEach(button => {
    button.classList.remove('active');
  });

  // Ajouter la classe 'active' au bouton cliqué
  clickedButton.classList.add('active');

  const activeView = clickedButton.dataset.view;

  const courseIndex = parseInt(document.getElementById('course-select').value) || 0;
  if (courseIndex >= window.courses.length) return;
  const currentCourse = window.courses[courseIndex];
  const hasQuiz = currentCourse && currentCourse.quizData && currentCourse.quizData.quiz.length > 0;
  const hasResume = currentCourse && currentCourse.resumeData;

  const quizPane = document.getElementById('quiz-pane');
  const resumePane = document.getElementById('resume-pane');

  if (quizPane) quizPane.style.display = (activeView === 'quiz' || activeView === 'both') && hasQuiz ? 'block' : 'none';
  if (resumePane) resumePane.style.display = (activeView === 'resume' || activeView === 'both') && hasResume ? 'block' : 'none';
}

// Génère et affiche le PDF du résumé dans l’iframe
function generatePdfFromLatex() {
  const latexCode = document.getElementById('latex-input').value;
  if (!latexCode.trim()) return;
  try {
    title = quizData?.courseTitle || "Résumé";
  } catch (e) {
    title = "Résumé";
  }
  fetch('http://127.0.0.1:5000/latex_to_pdf', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      latex: latexCode,
      title: title
    })
  })
    .then(res => res.json())
    .then(data => {
      if (data.pdf_path) {
        const pdfFrame = document.getElementById('pdf-frame');
        if (pdfFrame) {
          pdfFrame.src = data.pdf_path + '?inline=1';
          document.getElementById('pdf-container').style.display = 'block';
        }
      }
    })
    .catch(err => console.error('Erreur de génération du PDF : ', err));
}

// Nouvelle fonction : convertir le résumé (HTML) en PDF avec jsPDF
function downloadResumePdf() {
  const resumeElement = document.getElementById('latex-input').value;
  fetch('http://127.0.0.1:5000/latex_to_pdf', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      latex: resumeElement,
      title: document.getElementById('course-title').textContent,
      filename: document.getElementById('course-title').textContent + '.pdf'
    })

  })
    .then(res => res.json())
    .then(data => {
      if (data.pdf_path) {
        var link = document.createElement('a');
        link.href = data.pdf_path;
        //   link.download = 'quiz.pdf'; // Nom du fichier pour le téléchargement
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      } else {
        alert('Erreur lors de la génération du PDF : ' + data.error);
      }
    })
    .catch(err => alert('Erreur réseau : ' + err));
}
