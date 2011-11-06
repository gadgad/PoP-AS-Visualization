#!/bin/bash

sudo mv -f config/config.xml config/config.backup
sudo rename .xml .local xml/*.xml 
sudo rename .data .local data/*.data
sudo rename .xml .local users/*.xml
sudo git pull
sudo mkdir -p shell/log/queries
sudo mkdir -p mail_log
sudo rm	-rf examples old_versions tests
[ -f config/config.xml ] && sudo rename config.xml config.remote config/config.xml
sudo mv	-f config/config.backup	config/config.xml
sudo rename .local .xml xml/*.local
sudo rename .local .data data/*.local
sudo rename .local .xml users/*.local
sudo chmod -R 777 config data queries shell users xml mail_log