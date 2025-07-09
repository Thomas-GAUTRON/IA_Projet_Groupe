function corriger() {
  let score = 0;
  const questions = document.querySelectorAll('.question-block');

  questions.forEach(block => {
    const correct = block.dataset.correct;
    const name = block.dataset.question;
    const reponseChoisie = document.querySelector(`input[name="${name}"]:checked`);

    const reponseDiv = block.querySelector('.reponse');
    const explicationDiv = block.querySelector('.explication');

    if (reponseChoisie) {
      if (reponseChoisie.value === correct) {
        score++;
        block.style.border = "2px solid green";
      } else {
        block.style.border = "2px solid red";
      }
    } else {
      block.style.border = "2px dashed orange";
    }

    reponseDiv.style.display = "block";
    explicationDiv.style.display = "block";
  });

  document.getElementById("score").innerHTML = `<h3>Score : ${score} / ${questions.length}</h3>`;
}