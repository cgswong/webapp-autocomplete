#!/bin/bash
# Setup VM for web app demo

echo "Install OS requirements"
sudo apt-get -y update
sudo apt-get -y install \
  apache2 \
  memcached \
  php \
  php-memcached

echo "Install git repo"
sudo apt-get -y install git
mkdir ~/workspace
cd ~/workspace
git clone https://github.com/cgswong/webapp-autocomplete.git
cd webapp-autocomplete

