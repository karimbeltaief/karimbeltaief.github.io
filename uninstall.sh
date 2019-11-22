#!/usr/bin/env bash
echo "Desinstallation du site"
while true; do
    read -p "Voulez-vous vraiment procéder à la désinstallation du site ?" yn
    case $yn in
        [Yy]* ) echo "Suppression des fichiers du site web en cours ..."; rm -rf *; echo "Suppression des fichiers terminée"; break;;
        [Nn]* ) exit;;
        * ) echo "Veuillez repondre par Y/y ou N/n.";;
    esac
done
