// Function to handle toggling the visibility of the fatality section
function toggleFatalityVisibility() {
  // Get the radio buttons for "Applicable" and "Not Applicable"
  const applicableRadio = document.getElementById("applicable");
  const fatalitySection = document.querySelector(".fatality");

  // If "Applicable" is selected, show the fatality section
  if (applicableRadio.checked) {
    fatalitySection.classList.add("show");
  } else {
    fatalitySection.classList.remove("show");
  }
}

// Add event listeners to handle the change event of the radio buttons
document.querySelectorAll('input[name="fav_language"]').forEach((radio) => {
  radio.addEventListener("change", toggleFatalityVisibility);
});

// Call the toggleFatalityVisibility function on load to ensure the initial state is correct
document.addEventListener("DOMContentLoaded", toggleFatalityVisibility);

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
