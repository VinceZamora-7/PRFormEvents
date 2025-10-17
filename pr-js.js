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
