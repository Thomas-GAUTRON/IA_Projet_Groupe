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
            btn.classList.add('incorrect'); // Ajout : couleur rouge sur la mauvaise réponse
            const correct = [...all].find(c => c.textContent === item.answer);
            if (correct) correct.classList.add('correct');
          }

          const exp = document.createElement('div');
          exp.className = 'explanation';
          exp.textContent = `💡 ${item.explanation}`;
          block.appendChild(exp);

          btnNext.disabled = false;
          if (window.MathJax) MathJax.typesetPromise([block]);
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
        if (window.MathJax) MathJax.typesetPromise([container]);
      });
      container.appendChild(btnNext);
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

function compileLaTeX() {
  const latexCode = document.getElementById('latex-input').value;
  const outputDiv = document.getElementById('output');

  try {
    // Parser basique du LaTeX
    let htmlContent = latexCode;

    // Nettoyer le début et la fin
    htmlContent = htmlContent.replace(/\\begin{document}/, '');
    htmlContent = htmlContent.replace(/\\end{document}/, '');

    // Convertir les sections
    htmlContent = htmlContent.replace(/\\section\*{([^}]+)}/g, '<h2 class="latex-section">$1</h2>');
    htmlContent = htmlContent.replace(/\\subsection\*{([^}]+)}/g, '<h3 class="latex-subsection">$1</h2>');

    // Convertir les listes
    htmlContent = htmlContent.replace(/\\begin{itemize}/g, '<ul class="latex-itemize">');
    htmlContent = htmlContent.replace(/\\end{itemize}/g, '</ul>');
    htmlContent = htmlContent.replace(/\\item\s+/g, '<li class="latex-item">');

    // Convertir le texte gras
    htmlContent = htmlContent.replace(/\\textbf{([^}]+)}/g, '<span class="latex-textbf">$1</span>');

    // Nettoyer les paragraphes vides et ajouter des balises
    htmlContent = htmlContent.replace(/\n\s*\n/g, '</p><p class="latex-paragraph">');
    htmlContent = '<p class="latex-paragraph">' + htmlContent + '</p>';

    // Nettoyer les balises vides
    htmlContent = htmlContent.replace(/<p class="latex-paragraph">\s*<\/p>/g, '');
    htmlContent = htmlContent.replace(/<p class="latex-paragraph">\s*<div/g, '<div');
    htmlContent = htmlContent.replace(/<\/div>\s*<\/p>/g, '</div>');
    htmlContent = htmlContent.replace(/<p class="latex-paragraph">\s*<ul/g, '<ul');
    htmlContent = htmlContent.replace(/<\/ul>\s*<\/p>/g, '</ul>');

    // Fermer les éléments de liste
    htmlContent = htmlContent.replace(/<li class="latex-item">([^<]*?)(?=<li|<\/ul>)/g, '<li class="latex-item">$1</li>');
    htmlContent = htmlContent.replace(/<li class="latex-item">([^<]*?)<\/ul>/g, '<li class="latex-item">$1</li></ul>');

    // Afficher le résultat
    outputDiv.innerHTML = '<div class="latex-document">' + htmlContent + '</div>';
    outputDiv.style.display = 'block';

    // Afficher un message de succès
    const successMsg = document.createElement('div');
    successMsg.className = 'success';
    // successMsg.innerHTML = '<strong>✓ Conversion réussie !</strong> Le LaTeX a été converti en HTML. Les formules mathématiques sont rendues par MathJax.';
    outputDiv.insertBefore(successMsg, outputDiv.firstChild);

    // Re-traiter les mathématiques avec MathJax
    if (window.MathJax) {
      MathJax.typesetPromise([outputDiv]).catch(function (err) {
        console.log('Erreur MathJax: ' + err.message);
      });
    }

  } catch (error) {
    outputDiv.innerHTML = `<div class="error">
                    <strong>Erreur de conversion:</strong><br>
                    ${error.message}
                    <br><br>
                    <em>Cette conversion basique supporte les commandes LaTeX de base. 
                    Pour une conversion plus complète, utilisez Pandoc en ligne de commande.</em>
                </div>`;
    console.error('Erreur de conversion:', error);
  }
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

// ----- Multi-cours -----

window.courseQuizzes = [];
window.courseResumes = [];

// Si des données initiales sont injectées depuis PHP (cas dashboard)
if (typeof window.initialQuizArray !== 'undefined' && Array.isArray(window.initialQuizArray)) {
  window.courseQuizzes = window.initialQuizArray.map(q => {
    if (typeof q === 'string') {
      // Nettoyer la chaîne JSON : extraire la partie entre la première { et la dernière }
      let cleaned = q;
      const firstBrace = cleaned.indexOf('{');
      const lastBrace = cleaned.lastIndexOf('}');
      if (firstBrace !== -1 && lastBrace !== -1) {
        cleaned = cleaned.substring(firstBrace, lastBrace + 1);
      }
      // Dupliquer tout backslash qui n'est pas déjà une séquence d'échappement JSON valide (ex: \d -> \\d)
      cleaned = cleaned.replace(/\\(?!["\\\/bfnrtu0-9])/g, "\\\\$&");
      // Échapper les retours à la ligne en \n
      cleaned = cleaned.replace(/\r?\n/g, "\\n");
      cleaned = cleaned.replace(/\\\\/g, "\\");
      try {
        const obj = JSON.parse(cleaned);
        if (obj && obj.quiz) return obj;
        console.warn('Objet quiz invalide', obj);
        return null;
      } catch (e) {
        console.error('JSON parse error', e);
        return null;
      }
    }
    return q;
  });
  // Filtrer les null
  window.courseQuizzes = window.courseQuizzes.filter(q => q && q.quiz && Array.isArray(q.quiz));
  window.courseResumes = Array.isArray(window.initialResumeArray) ? window.initialResumeArray.map(r => r) : [];
  // Cas où il n’y a que des résumés et aucun quiz : créer des objets placeholder
  if (courseQuizzes.length === 0 && courseResumes.length > 0) {
    window.courseQuizzes = courseResumes.map((_, idx) => ({
      courseTitle: (() => {
        try {
          const match = courseResumes[idx].match(/section\*\{([^}]+)\}/);
          return match ? match[1].replace(/\\[a-zA-Z]+/g, '') : `Résumé ${idx + 1}`;
        } catch (error) {
          return `Résumé ${idx + 1}`;
        }
      })(),
      quiz: []
    }));
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
  courseQuizzes.forEach((_, idx) => {
    const opt = document.createElement('option');
    opt.value = idx;
    opt.textContent = `Cours ${idx + 1}`;
    sel.appendChild(opt);
  });
  if (courseQuizzes.length > 1) {
    sel.style.display = 'block';
  }
  sel.addEventListener('change', () => loadCourse(parseInt(sel.value)));
}

function loadCourse(index) {
  if ((index < 0 || index >= courseQuizzes.length)) return;
  window.quizData = courseQuizzes[index];
  const hasQuiz = window.quizData && Array.isArray(window.quizData.quiz) && window.quizData.quiz.length;

  document.getElementById('latex-input').value = courseResumes[index] || '';

  // Reconstruire l'interface
  if (hasQuiz && typeof buildQuiz === 'function') {
    document.getElementById('quiz-pane').classList.remove('hidden');
    buildQuiz();
  }

  if (typeof generatePdfFromLatex === 'function') generatePdfFromLatex();
  // if (typeof compileLaTeX === 'function') compileLaTeX();

  document.getElementById('course-title').textContent = quizData?.courseTitle || `Cours ${index + 1}`;
}

// Génère et affiche le PDF du résumé dans l’iframe
function generatePdfFromLatex() {
  const latexCode = document.getElementById('latex-input').value;
  if (!latexCode.trim()) return;

  fetch('http://127.0.0.1:5000/latex_to_pdf', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      latex: latexCode,
      title: quizData?.courseTitle || "Résumé"
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
