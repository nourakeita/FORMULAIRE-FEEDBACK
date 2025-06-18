// On attend que le DOM soit chargé
document.addEventListener("DOMContentLoaded", () => {
  // Récupérer tous les boutons de satisfaction
  const buttons = document.querySelectorAll(".satisfaction-btn");
  // Input caché pour stocker la valeur sélectionnée
  const inputSatisfaction = document.getElementById("niveau-satisfaction");

  buttons.forEach(button => {
    button.addEventListener("click", () => {
      // On retire la classe 'active' de tous les boutons
      buttons.forEach(btn => btn.classList.remove("active"));
      // On active celui qui a été cliqué
      button.classList.add("active");
      // On met à jour la valeur cachée du formulaire
      inputSatisfaction.value = button.getAttribute("data-value");
    });
  });

  // Gestion de l'envoi du formulaire
  const form = document.getElementById("feedback-form");
  form.addEventListener("submit", (e) => {
    // On empêche l'envoi si aucune satisfaction sélectionnée
    if (!inputSatisfaction.value) {
      e.preventDefault();
      alert("Veuillez sélectionner votre niveau de satisfaction avant d'envoyer.");
      return false;
    }

    // Ici, tu peux ajouter un code pour envoyer via fetch/AJAX ou autre, sinon le formulaire envoie normalement
  });
});
