const emojis = document.querySelectorAll('.emoji-option');
const satisfactionInput = document.getElementById('satisfactionInput');

emojis.forEach(emoji => {
  emoji.addEventListener('click', () => {
    // Supprimer les anciennes sélections et couleurs
    emojis.forEach(e => {
      e.classList.remove('selected', 'tres-satisfaisant', 'satisfaisant', 'peu-satisfaisant', 'pas-satisfaisant');
    });

    emoji.classList.add('selected');

    // Ajouter la bonne couleur selon l'emoji choisi
    const value = emoji.dataset.value;
    satisfactionInput.value = value;

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
