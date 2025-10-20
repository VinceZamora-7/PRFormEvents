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
    // Find the form element
    var form = document.querySelector("form");

    // Make sure the form is ready for submission
    if (form) {
      // We can manually submit the form here
      form.submit(); // This will trigger the backend (submit_review.php) to save data
    }

    // Close the modal after submitting the form
    var modal = new bootstrap.Modal(
      document.getElementById("confirmationModal")
    );
    modal.hide();
  });

// EMAILING THE LINK - (Open Outlook with a pre-filled email)
function sendEmail(prId) {
  let bodyText =
    `Peer Review Answers:\n\n` +
    `Hope you're doing well!\n\n` +
    `Task Name: Sample task name\n\n` +
    `I've noticed that there are some errors\n` +
    `For reference, here is the PRID: ${prId}\n\n` +
    `Here is the link to the feedback page: http://localhost/EVENTS/EVENT-PR/pr-feedback/pr_feedback.php?pr_id=${prId}\n\n` +
    `Thank you so much!`;

  const subject = encodeURIComponent("Peer Review Submission");
  const body = encodeURIComponent(bodyText);

  // Opens the default email client (Outlook or others)
  window.location.href = `mailto:v-jopastoral@microsoft.com?subject=${subject}&body=${body}`;
}

// // EMAILING THE FEEDBACK ITSELF
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
