# mod_hva

Plugin name : "Hyperfiction VR Activity"

## Description
{{ à faire}}

## Installation

There are two installation methods available.
Follow one of these, then log into your Moodle site as an administrator and visit the notifications page to complete the install.

### GIT

This requires Git being installed. If you do not have Git installed, please visit the Git website.
Once you have Git installed, simply visit your Moodle mod directory and clone the repository using the following command.

git clone https://github.com/cbluesprl/moodle-mod_hva.git hva

Or add it with submodule command if you use submodules.

git submodule add https://github.com/cbluesprl/moodle-mod_hva.git mod/hva

### Download the zip

Visit the Moodle plugins website and download the zip corresponding to the version of Moodle you are using. Extract the zip and place the 'hva' folder in the mod folder in your Moodle directory.

### Pré-requis

Need to enable web service REST for use the web service.


## Test

Une page test est accessible aux professeurs, manager et admin mias pas aux étudiant.

## Webservices

There 3 webservices : 

### get_info 

`/webservice/rest/server.php?wstoken=token&wsfunction=mod_hva_get_info&moodlewsrestformat=json`

Il faut fournir le paramètre suivant :
`pincode` : le code pin de l'étudiant au lancement de l'activité.

Voici un exemple json des paramètres :

```json
{
    "methodname": "mod_hva_get_info",
    "args": {
        "pincode" : 9347
    }
}
```

Voici un exemple de ce que doit retourner le ws d'information :
```json
{
  "error": false,
  "data": {
    "studentId": 2,
    "studentName": "Nom Prénom",
    "activityTitle": "Exemple d'activité HVA",
    "LMSTracking": {
      "score": 0,
      "completion": "0"
    },
    "hyperfictionTracking": "{\"browsers\":{\"firefox\":{\"name\":\"Firefox\",\"pref_url\":\"about:config\",\"releases\":{\"1\":{\"release_date\":\"2004-11-09\",\"status\":\"retired\",\"engine\":\"Gecko\",\"engine_version\":\"1.7\"}}}}}"
  }
}
```

Le tracking json retourner est en string.

### get_zip

`/webservice/rest/server.php?wstoken=token&wsfunction=mod_hva_get_info&moodlewsrestformat=json`

Il faut fournir le paramètre suivant :
`pincode` : le code pin de l'étudiant au lancement de l'activité.

Voici un exemple json des paramètres :

```json
{
    "methodname": "mod_hva_get_zip",
    "args": {
        "pincode" : 9347
    }
}
```

Voici un exemple de ce que doit retourner le ws d'information :
```json
{
    "error": false,
    "data": {
        "url": "http://grtgaz.local73/webservice/pluginfile.php/73/mod_hva/zipfile/1/local_obf_moodle311_2022020200%281%29.zip?token=dd315c54548c8ef9b1238b11111b27c3"
    }
}
```
### save_data

`/webservice/rest/server.php?wstoken=token&wsfunction=mod_hva_get_info&moodlewsrestformat=json`

Ce web service retourne le status 'save succeeded' si la sauvegarde à fonctionner sinon retourne le status  'Can't find data record in database table hva.' si le code pin est erroné.

Il faut fournir les paramètres :

* `methodname` : le nom de la méthode (mod_hva_save_data)
* `pincode` : le code pin récupéré dans l'activité hva
* `LMSTracking` : Ce paramètre doit contenir le paramètre score et completion
* `hyperfictionTracking` : Le json à sauvegarder sur la plateforme. Le json doit être converti en string pour pouvoir être stocker

Voici un exemple json des paramètres :
```json
{
    "methodname": "mod_hva_save_data",
    "args": {
        "pincode" : 9347,
        "LMSTracking": {
            "score": 99,
            "completion":"completed"
        },
        "hyperfictionTracking" : "{\"browsers\":{\"firefox\":{\"name\":\"Firefox\",\"pref_url\":\"about:config\",\"releases\":{\"1\":{\"release_date\":\"2004-11-09\",\"status\":\"retired\",\"engine\":\"Gecko\",\"engine_version\":\"1.7\"}}}}}"
    }
}
```

