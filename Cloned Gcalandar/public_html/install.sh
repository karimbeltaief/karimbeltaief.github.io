#!/usr/bin/env bash
SRC="./site/public_html/*"
DST="."
LUMEN_PATH="./wp-content/plugins/wp-lumen-plugin-framework/"
echo "Installation du site"
echo "Deplacement du contenu wordpress ..."
mv ${SRC} ${DST}
echo "Fait"
echo "Création de wp-config.php"
read -p "Nom de la base de données :" dbName
read -p "Utilisateur :" dbUser
read -p "Mot de passe :" dbPassword
cp "wp-config-sample.php" "wp-config.php"
sed -i 's/votre_nom_de_bdd/'${dbName}'/g' wp-config.php
sed -i 's/votre_utilisateur_de_bdd/'${dbUser}'/g' wp-config.php
sed -i 's/votre_mdp_de_bdd/'${dbPassword}'/g' wp-config.php
echo "Fait"
echo "Installation de dépendances de lumen"
cd ${LUMEN_PATH}
composer install
echo "Fait"
echo "Suppression des dossiers inutiles"
cd "../../../";
rm -rf "./site"
echo "Fait"