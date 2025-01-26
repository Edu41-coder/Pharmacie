// chequeUtils.js

// Fonction pour mettre à jour le tableau avec les données PHP
export function updateTableWithPHPData(cheques) {
  const tbody = document.querySelector("#cheques-table tbody");
  if (!tbody) {
    console.error("Tbody non trouvé");
    return;
  }
  console.log("Mise à jour du tableau avec les chèques:", cheques); // Log des données reçues
  tbody.innerHTML =
    cheques && cheques.length
      ? generateTableRowsJS(cheques)
      : '<tr><td colspan="8">Aucun chèque trouvé.</td></tr>';
  console.log("HTML généré pour le tableau :", tbody.innerHTML);
}

// Fonction pour générer les lignes du tableau à partir des chèques
export function generateTableRowsJS(cheques) {
  return cheques
    .map((cheque) => {
      console.log("Données du chèque:", cheque); // Ajout de ce log
      return `
        <tr>
          <td>${cheque.cheque_id}</td>
          <td>${cheque.numero_cheque}</td>
          <td>${cheque.client_nom || "N/A"} ${
        cheque.client_prenom || "N/A"
      }</td>
          <td>${cheque.montant} €</td>
          <td>${cheque.etat}</td>
          <td>${cheque.vente_id || "N/A"}</td>
          <td>${cheque.vente_is_deleted ? "Oui" : "Non"}</td>
          <td>
            <a href="/Pharmacie_S/Views/comptabilité/edit_cheque.php?id=${
              cheque.cheque_id
            }" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i> Modifier</a>
          </td>
        </tr>
      `;
    })
    .join("");
}

// Fonction pour effectuer la recherche
// chequeUtils.js

// Fonction pour effectuer la recherche
export function performSearch(
  searchInput,
  searchCriteria,
  etatFilter,
  includeDeletedCheckbox,
  dateDebutInput,
  dateFinInput
) {
  const searchTerm = searchInput.value;
  const criteria = searchCriteria.value;

  const params = new URLSearchParams({
    term: searchTerm,
    criteria: criteria,
    etat: etatFilter.value,
    include_deleted: includeDeletedCheckbox.checked ? "1" : "0",
    date_debut: dateDebutInput.value,
    date_fin: dateFinInput.value,
  });

  console.log("Paramètres de recherche :", params.toString()); // Log des paramètres

  return fetch(
    `/Pharmacie_S/PHP/comptabilité/autocomplete_cheque.php?${params.toString()}`
  )
    .then((response) => {
      if (!response.ok) {
        throw new Error("Erreur lors de la récupération des données");
      }
      return response.json();
    })
    .then((data) => {
      console.log("Données reçues :", data); // Log des données reçues
      console.log(
        "Premier chèque reçu :",
        data.length > 0 ? data[0] : "Aucun chèque"
      );
      return data.length ? data : []; // Retourner un tableau vide si aucune donnée n'est reçue
    })
    .catch((error) => {
      console.error("Erreur lors de la recherche :", error);
      return []; // Retourner un tableau vide en cas d'erreur
    });
}
