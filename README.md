# Documentation API – Projet Kilya

## 1. Présentation
Ce projet comprend plusieurs applications backend et frontend, exposant des API pour la gestion de joueurs, commentaires et participations.

- **URL globale :** [https://r301.kilya.coop/](https://r301.kilya.coop/)  
- **Technologies utilisées :** PHP, MySQL, PDO, HTML, CSS  
- **Authentification :** Token JWT

---

## 2. Authentification

### EndpointAuth 
Permet la connexion d’un utilisateur et la récupération d’un JWT pour les requêtes suivantes.

- **Méthode : POST**  
  - **Description :** Connexion utilisateur  
  - **Paramètres JSON :**
    - `login` (string, obligatoire)  
    - `password` (string, obligatoire)  
  - **Exemple de requête :**
    ```bash
    curl -X POST https://auth.kilya.coop/api/auth \
         -H "Content-Type: application/json" \
         -d '{"login":"admin","password":"adminabc"}'
    ```
  - **Exemple de réponse :**
    ```json
    {"token":"<jwt>"}
    ```
  - **Codes d’erreur :**
    - 400 : Données invalides ou incomplètes 
    - 405 : Méthode non autorisée  
    - 500 : Erreur serveur  

---

## 3. Joueurs

### EndpointJoueur  

- **Méthode : GET**  
  - **Description :** Récupère tous les joueurs ou un joueur spécifique  
  - **Paramètres :** `id` (int, optionnel)  
  - **Exemple de requête :**  
    ```
        https://frontend.kilya.coop/api/joueur/1
    ```
  - **Exemple de réponse :**
    ```json
    {"id":1,"nom":"Alice","prenom":"A.","numeroDeLicence":"12345"}
    ```
  - **Codes d’erreur :** 404 : Joueur non trouvé  

- **Méthode : POST**  
  - **Description :** Crée un nouveau joueur  
  - **Paramètres JSON :**
    - `nom`, `prenom`, `numeroDeLicence`, `dateDeNaissance` (YYYY-MM-DD), `tailleEnCm`, `poidsEnKg`, `statut`  
  - **Exemple de requête :**
    ```bash
    curl -X POST https://frontend.kilya.coop/api/joueur \
         -H "Content-Type: application/json" \
         -d '{"nom":"Doe","prenom":"John","numeroDeLicence":"123","dateDeNaissance":"2000-01-01","tailleEnCm":180,"poidsEnKg":75,"statut":"actif"}'
    ```
  - **Exemple de réponse :** 201 : Joueur créé  
  - **Codes d’erreur :** 400 : Erreur lors de la création  

- **Méthode : PUT**  
  - **Description :** Met à jour toutes les informations d’un joueur  
  - **Paramètres :** `id` (query string), mêmes champs que POST  
  - **Exemple de requête :**
    ```bash
    curl -X PUT https://frontend.kilya.coop/api/joueur/1 \
         -H "Content-Type: application/json" \
         -d '{...}'
    ```
  - **Exemple de réponse :** 200 : Joueur mis à jour  
  - **Codes d’erreur :** 400 : Champs manquants, 404 : Joueur non trouvé  

- **Méthode : DELETE**  
  - **Description :** Supprime un joueur  
  - **Paramètres :** `id` (query string)  
  - **Exemple de requête :**
    ```bash
    curl -X DELETE https://frontend.kilya.coop/api/joueur/1
    ```
  - **Exemple de réponse :** 200 : Joueur supprimé avec succès 
  - **Codes d’erreur :** 404 : Joueur non trouvé  

---

## 4. Commentaires

### EndpointCommentaire

- **Méthode : GET**  
  - **Description :** Liste les commentaires d’un joueur  
  - **Paramètres :** `joueur_id` (int, obligatoire)  
  - **Exemple de requête :**
    ```bash
    curl https://frontend.kilya.coop/api/commentaire?joueur_id=1
    ```
  - **Exemple de réponse :**
    ```json
    [{"id":1,"contenu":"Super joueur"}]
    ```
  - **Codes d’erreur :** 400 : Paramètre manquant, 404 : Joueur non trouvé  

- **Méthode : POST**  
  - **Description :** Ajoute un commentaire  
  - **Paramètres JSON :** `contenu` (string), `joueur_id` (int)  
  - **Exemple de requête :**
    ```bash
    curl -X POST https://frontend.kilya.coop/api/commentaire \
         -H "Content-Type: application/json" \
         -d '{"contenu":"Bien joué","joueur_id":1}'
    ```
  - **Exemple de réponse :** 201 : Commentaire créé  
  - **Codes d’erreur :** 400 : Champs manquants ou JSON invalide  

- **Méthode : DELETE**  
  - **Description :** Supprime un commentaire  
  - **Paramètres :** `id` (query string)  
  - **Exemple de requête :**
    ```bash
    curl -X DELETE https://frontend.kilya.coop/api/commentaire?id=1
    ```
  - **Exemple de réponse :** 200 : Commentaire supprimé  
  - **Codes d’erreur :** 400 : ID manquante, 404 : Commentaire non trouvé  

---

## 5. Participations

### EndpointParticipation

- **Méthode : GET**  
  - **Description :** Récupère toutes les participations ou une participation spécifique  
  - **Paramètres :** `id` (int, optionnel)  
  - **Exemple de requête :**
    ```bash
    curl https://frontend.kilya.coop/api/participation/1
    ```
  - **Exemple de réponse :** 200 : La requête a réussi 
  - **Codes d’erreur :** 404 : Participation non trouvée  

- **Méthode : POST**  
  - **Description :** Crée une participation  
  - **Paramètres JSON :** `joueur_id`, `rencontre_id`, `poste`, `titulaire_ou_remplacant`  
  - **Exemple de requête :**
    ```bash
    curl -X POST https://frontend.kilya.coop/api/participation \
         -H "Content-Type: application/json" \
         -d '{"joueur_id":1,"rencontre_id":10,"poste":"gardien","titulaire_ou_remplacant":"titulaire"}'
    ```
  - **Exemple de réponse :** 201 : Participation créée  
  - **Codes d’erreur :** 400 : JSON invalide ou conflit de poste  

- **Méthode : PUT**  
  - **Description :** Met à jour une participation complète  
  - **Paramètres JSON :** `joueur_id`, `poste`, `titulaire_ou_remplacant`  
  - **Exemple de requête :**
    ```bash
    curl -X PUT https://frontend.kilya.coop/api/participation/1 \
         -H "Content-Type: application/json" \
         -d '{...}'
    ```
  - **Exemple de réponse :** 200 : Participation mise à jour  
  - **Codes d’erreur :** 400 : JSON invalide ou conflit, 404 : Participation non trouvée  

- **Méthode : PATCH**  
  - **Description :** Met à jour partiellement la participation (ex : performance)  
  - **Paramètres JSON :** `performance`  
  - **Exemple de requête :**
    ```bash
    curl -X PATCH https://frontend.kilya.coop/api/participation/1 \
         -H "Content-Type: application/json" \
         -d '{"performance":5}'
    ```
  - **Exemple de réponse :** 200 : Performance mise à jour  
  - **Codes d’erreur :** 400 : JSON invalide, 404 : Participation non trouvée  

- **Méthode : DELETE**  
  - **Description :** Supprime une participation  
  - **Paramètres :** `id` (query string)  
  - **Exemple de requête :**
    ```bash
    curl -X DELETE https://frontend.kilya.coop/api/participation/1
    ```
  - **Exemple de réponse :** 200 : Participation supprimée  
  - **Codes d’erreur :** 400 : Impossible de supprimer, 404 : Participation non trouvée  

  ## 6. Rencontres

### EndpointRencontre

- **Méthode : GET**  
  - **Description :** Liste toutes les rencontres ou récupère une rencontre spécifique  
  - **Paramètres :** `id` (int, optionnel)  
  - **Exemple de requête :**
    ```bash
    curl https://frontend.kilya.coop/api/rencontre/1
    ```
  - **Exemple de réponse :**
    ```json
    {"id":1,"dateHeure":"2026-04-01T15:00:00","equipeAdverse":"Lyon","adresse":"Stade Municipal","lieu":"DOMICILE"}
    ```
  - **Codes d’erreur :** 404 : Rencontre non trouvée  

- **Méthode : POST**  
  - **Description :** Crée une rencontre  
  - **Paramètres JSON :**
    - `dateHeure` (string, format YYYY-MM-DDTHH:MM:SS)  
    - `equipeAdverse` (string)  
    - `adresse` (string)  
    - `lieu` (string, DOMICILE ou EXTERIEUR)  
  - **Exemple de requête :**
    ```bash
    curl -X POST https://frontend.kilya.coop/api/rencontre \
         -H "Content-Type: application/json" \
         -d '{"dateHeure":"2026-04-01T15:00:00","equipeAdverse":"Lyon","adresse":"Stade Municipal","lieu":"DOMICILE"}'
    ```
  - **Exemple de réponse :** 201 : Rencontre créée  

- **Méthode : PUT**  
  - **Description :** Met à jour une rencontre  
  - **Paramètres JSON :** mêmes que POST + `id` dans la query string  
  - **Codes d’erreur :** 400 : JSON invalide, 404 : Rencontre non trouvée  

- **Méthode : PATCH**  
  - **Description :** Met à jour uniquement le résultat d’une rencontre  
  - **Paramètres JSON :**
    - `resultat` (string)  
  - **Exemple de requête :**
    ```bash
    curl -X PATCH https://frontend.kilya.coop/api/rencontre/1 \
         -H "Content-Type: application/json" \
         -d '{"resultat":"3-1"}'
    ```
  - **Exemple de réponse :** 200 : Résultat enregistré  

- **Méthode : DELETE**  
  - **Description :** Supprime une rencontre  
  - **Paramètres :** `id` (query string)  
  - **Exemple de réponse :** 200 : Rencontre supprimée  

---

## 7. Statistiques

### EndpointStatistiques

- **Méthode : GET**  
  - **Description :** Récupère les statistiques de l’équipe et des joueurs  
  - **Exemple de requête :**
    ```bash
    curl https://frontend.kilya.coop/api/statistiques
    ```
  - **Exemple de réponse :**
    ```json
    {
      "statistiques_equipe": {"victoires":10,"defaites":2},
      "statistiques_joueurs": [
        {"joueur_id":1,"buts":5,"passes":3},
        {"joueur_id":2,"buts":2,"passes":4}
      ]
    }
    ```
  - **Codes d’erreur :** 401 : Token invalide, 405 : Méthode non autorisée  

---

---

### Notes
- Toutes les requêtes autres que GET/POST/PUT/PATCH/DELETE renvoient **405 Méthode non autorisée**.  
- Toutes les requêtes nécessitent un **JWT valide** dans le header `Authorization: Bearer <token>`.  
- Les erreurs de serveur renvoient **500 avec message d’erreur détaillé**.