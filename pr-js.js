// Function to handle toggling the visibility of the fatality section
function toggleFatalityVisibility(event) {
  const questionId = event.target.name.replace("q", "fatality"); // Get question number dynamically
  const fatalitySection = document.getElementById(questionId); // Get fatality section based on question

  if (fatalitySection) {
    if (event.target.value === "Applicable") {
      fatalitySection.classList.add("show");
    } else {
      fatalitySection.classList.remove("show");
    }
  }
}

// Add event listeners for all the applicable and not applicable radio buttons dynamically
document.querySelectorAll('input[name^="q"]').forEach((radio) => {
  radio.addEventListener("change", toggleFatalityVisibility);
});

// Call the toggleFatalityVisibility function on load to ensure the initial state is correct
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll('input[name^="q"]').forEach((radio) => {
    toggleFatalityVisibility({ target: radio });
  });
});

// Set up the toggling functionality for each section
function setupToggle(sectionNum) {
  const toggleHeader = document.getElementById(`toggleHeader${sectionNum}`);
  const prCard = document.getElementById(`prCard${sectionNum}`);

  function toggleCard() {
    const isHidden = prCard.classList.toggle("hidden");
    toggleHeader.setAttribute("aria-expanded", !isHidden);
    toggleHeader.classList.toggle("collapsed", isHidden);
  }

  toggleHeader.addEventListener("click", () => {
    toggleCard();
  });

  toggleHeader.addEventListener("keydown", (e) => {
    if (e.key === "Enter" || e.key === " ") {
      e.preventDefault();
      toggleCard();
    }
  });
}

setupToggle(1);
setupToggle(2);
setupToggle(3);
setupToggle(4);
setupToggle(5);
setupToggle(6);

document
  .getElementById("confirmProceedBtn")
  .addEventListener("click", function () {
    // Open the pr_feedback.php in a new tab
    window.open("pr-feedback/pr_feedback.php", "_blank");
  });

// function sendEmail() {
//   const maxQuestions = 80; // total questions
//   let bodyText = "Peer Review Answers:\n\n";

//   for (let i = 1; i <= maxQuestions; i++) {
//     const questionElem = document.querySelector(
//       `label[for="applicable${i}"], label[for="notApplicable${i}"]`
//     );

//     let questionText = `Q${i}: Question text not available`;

//     const q = document.querySelector(`input[name="q${i}"]:checked`);
//     const qAnswer = q ? q.value : "Not answered";

//     bodyText += `*${questionText}*\nAnswer: ${qAnswer}\n`;

//     if (qAnswer !== "Not Applicable") {
//       const f = document.querySelector(`input[name="fatality${i}"]:checked`);
//       const fAnswer = f ? f.value : "Not answered";
//       bodyText += `Fatality ${i}: ${fAnswer}\n`;
//     }

//     bodyText += "\n"; // extra line break between questions
//   }

//   const subject = encodeURIComponent("Peer Review Submission");
//   const body = encodeURIComponent(bodyText);

//   window.location.href = `mailto:v-jopastoral@microsoft.com?subject=${subject}&body=${body}`;
// }
