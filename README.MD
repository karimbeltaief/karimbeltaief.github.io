# Installation
## Actions requises
1. Veuillez enlevez vos hooks pre-commit s'il y en a.
2. Clonnez ce repository.

## Mise en place et configuration de l'environnement
### **Mise en place de la base de données**

Note: Assurez-vous que votre serveur de base de données **mysql** soit en marche.

1. Allez vers le dossier **bdd** 
    ```bash
    $ cd bdd
    ```

    Vous devez trouvez un fichier sql `nmwynyemhy.sql` dans ce dossier
2. Ouvrez votre terminal
3. Créez une base de donnée portant le nom **nmwynyemhy**

    Connectez-vous d'abord.
    ```bash
    bdd $ echo 'drop if exists database nmwynyemhy; create database nmwynyemhy;' | mysql -u utilisateur -p
    ```

4. Importer le fichier sql dans votre base de données
    ```bash
    bdd $ mysql -u utilisateur -p nmwynyemhy < nmwynyemhy.sql
    ```
5. Faite la migration du site en local/ngrok. Dans notre cas on va migrer depuis **https://www.hobby-mood.com** vers **http://127.0.0.1:8000**
   5.1. Site local
   ```bash
   echo "UPDATE wp_options SET option_value = replace(option_value, 'https://www.hobby-mood.com', 'https://gc-laravel.ngrok.io') WHERE option_name = 'home' OR option_name = 'siteurl';

   UPDATE wp_posts SET post_content = replace(post_content, 'https://www.hobby-mood.com', 'https://gc-laravel.ngrok.io');

   UPDATE wp_postmeta SET meta_value = replace(meta_value, 'https://www.hobby-mood.com', 'https://gc-laravel.ngrok.io');" | mysql -uroot -p nmwynyemhy
   ```
   5.2. Site ngrok
   ```bash
   echo "UPDATE wp_options SET option_value = replace(option_value,'http://gc-laravel.ngrok.io' , 'https://gc-laravel.ngrok.io') WHERE option_name = 'home' OR option_name = 'siteurl';

   UPDATE wp_posts SET post_content = replace(post_content, 'http://gc-laravel.ngrok.io', 'https://gc-laravel.ngrok.io');

   UPDATE wp_postmeta SET meta_value = replace(meta_value, 'http://gc-laravel.ngrok.io', 'https://gc-laravel.ngrok.io');" | mysql -uroot -p nmwynyemhy
   ```   


Si tout s'est bien passé procédons à la configuration de wordpress.

### **Configuration de Wordpress**
1. Revenez dans le dossier racine.
2. Naviguez vers le dossier `site/public_html`
3. Editez le fichier **wp-config.php**
   
   Configurez vos informations de connexion à la base de données

   ```php
    // ** MySQL settings - You can get this info from your web host ** //
    /** The name of the database for WordPress */
    define('DB_NAME', 'nmwynyemhy');

    /** MySQL database username */
    //define('DB_USER', 'nmwynyemhy');
    define('DB_USER', 'user');

    /** MySQL database password */
    //define('DB_PASSWORD', 'yk3bsrBrWa');
    define('DB_PASSWORD', 'password');

    /** MySQL hostname */
    define('DB_HOST', 'localhost');
   ```

4. Créez un virtualhost dont la racine web est ce dossier `site/public_html`
5. Synchronisation du calendrier
    5.1. Obtenir API
        5.1.a. https://console.developers.google.com/
        5.1.b. Créer Projet
        5.1.c. Activer des API et des services
        5.1.d. Rechercher l'API Calendar
        5.1.e. Créer un identifiant dans le menu API et services
        5.1.f. Créer le clé de l'API
            - Restrictions relatives aux applications: selectionner réference HTTP
            - Restrictions liées aux sites Web: nom de la domaine ex: http://www.
            - Restrictions relatives aux API: Restindre la clé => API
        5.1.g. Créer l'ID Clients OAuth 2.0
            - Origines JavaScript autorisées: Nom du domaine

### **Plugin pour integrer google calendar laravel: microframework lumen**

1. Voir ce lien: https://github.com/bayareawebpro/wp-lumen-plugin-framework
2. Télécharer les paquets à utiliser:
```bash
  cd <root>/wp-content/plugins/wp-lumen-plugin-framework
  composer install
```
3. Activation du plugin 
   3.1 Activer le plugin dans extension wp-lumen-plugin-framework 
   3.2 Revenir sur le tableau de bord et verifier la toolbar en bas qui devrait s'afficher

4. À faire, ramener et adapter le code de l'application pour le microframework lumen
