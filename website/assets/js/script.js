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

