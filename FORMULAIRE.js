console.log("Fichier FORMULAIRE.js chargé !");
const emojis = document.querySelectorAll('.emoji-option');
const satisfactionInput = document.getElementById('satisfactionInput');

function selectEmoji(emoji) {
  // Supprimer les anciennes sélections et couleurs
  emojis.forEach(e => {
    e.classList.remove('selected', 'tres-satisfaisant', 'satisfaisant', 'peu-satisfaisant', 'pas-satisfaisant');
  });

  // Ajouter la classe selected sur l'emoji cliqué/touché
  emoji.classList.add('selected');

  // Récupérer la valeur (data-value)
  const value = emoji.dataset.value;
  satisfactionInput.value = value;

  // Ajouter la couleur correspondante selon la valeur
  switch (value) {
    case 'Très satisfaisant':
      emoji.classList.add('tres-satisfaisant');
      break;
    case 'Satisfaisant':
      emoji.classList.add('satisfaisant');
      break;
    case 'Peu satisfaisant':
      emoji.classList.add('peu-satisfaisant');
      break;
    case 'Pas du tout satisfaisant':
      emoji.classList.add('pas-satisfaisant');
      break;
  }
}

// Ajouter gestion tactile et clic
emojis.forEach(emoji => {
  emoji.addEventListener('click', () => selectEmoji(emoji));
  emoji.addEventListener('touchstart', (e) => {
    e.preventDefault();  // Empêche le double déclenchement click + touch
    selectEmoji(emoji);
  });
});

document.getElementById('feedbackForm').addEventListener('submit', function (e) {
  if (!satisfactionInput.value) {
    alert("Veuillez sélectionner un niveau de satisfaction.");
    e.preventDefault();
    return;
  }
  alert("Merci pour votre retour !");
});


