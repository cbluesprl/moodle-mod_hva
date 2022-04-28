# mod_hva

Nom du pluggin : "Hyperfiction VR Activity"

## Installation

Deux méthodes d'installation sont disponibles.
Suivez l'une d'entre elles, puis connectez-vous à votre site Moodle en tant qu'administrateur et visitez la page des notifications pour terminer l'installation.

### GIT

Cela nécessite que Git soit installé. Si vous n'avez pas installé Git, veuillez visiter le site web de Git.
Une fois Git installé, rendez-vous simplement dans votre répertoire Moodle mod et clonez le dépôt en utilisant la commande suivante.

git clone https://github.com/cbluesprl/moodle-mod_hva.git hva

Ou ajoutez-le avec la commande submodule si vous utilisez des submodules.

git submodule add https://github.com/cbluesprl/moodle-mod_hva.git mod/hva

### Download the zip

Visitez le site web des plugins Moodle et téléchargez le zip correspondant à la version de Moodle que vous utilisez. Extrayez le zip et placez le dossier 'hva' dans le dossier mod de votre répertoire Moodle.


## Dépendances

aucune

### Pré-requis

Vous devez activer web service REST de votre plateforme pour utiliser ce pluggin et paramètrer un token pour  les webservice du pluggin Hyperfiction VR Activity.

Vous pouvez retrouver la documentation nécessaire à cela sur :

`https://docs.moodle.org/400/en/Using_web_services`

## Test

Une page de test est accessible aux managers avec la capability suivante : 'mod/hva:test' .

## Webservices

Ce plugin fonctionne avec 2 webservice :  

### get_info 

`/webservice/rest/server.php?wstoken=token&wsfunction=mod_hva_get_info&moodlewsrestformat=json`

Il faut fournir le paramètre suivant :
`pincode` : le code pin de l'étudiant au lancement de l'activité.


Voici un exemple de json que doit retourner le ws :
```json
{
  "studentId": 2,
  "studentName": "Nom Prénom",
  "activityTitle": "activité de test",
  "LMSTracking": {
    "score": 88,
    "completion": "3"
  },
  "hyperfictionTracking": "{\"valeur\":1,\"valeur\":2}",
  "url": "http://plateforme/webservice/pluginfile.php/73/mod_hva/zipfile/1/nom_du_fichier.zip?token=dd315c54548c8ef9b1238b11111b27c3"
}
```

Le tracking json retourner est en string.

### save_data

`/webservice/rest/server.php?wstoken=token&wsfunction=mod_hva_get_info&moodlewsrestformat=json`

Ce web service retourne le status 'save succeeded' si la sauvegarde à fonctionner sinon retourne le status  'Can't find data record in database table hva.' si le code pin est erroné.

Il faut fournir les paramètres :

* `pincode` : le code pin récupéré dans l'activité hva
* `LMSTracking[score]` : Ce paramètre doit contenir le paramètre score 
* `LMSTracking[completion]` : Ce paramètre doit contenir le paramètre completion
* `hyperfictionTracking` : Le json à sauvegarder sur la plateforme.

Si le webservice à fonctionner, vous recevez un json avec ce status :
```json
{
  "status": "save succeeded"
}
```

