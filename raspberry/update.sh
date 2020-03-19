#!/bin/bash

cd /home/pi/mawaqit

wget -q --spider http://google.com

if [ $? -ne 0 ]; then
  whiptail --title "No internet" --msgbox "Please connect to the internet" 10 60
  exit 0;
fi

if [ "$currenttag" != "$latesttag" ]; then
  {
     git fetch
     echo 5
     currenttag=$(git describe --tags --abbrev=0)
     latesttag=$(git tag | sort -V | tail -1)
     git checkout ${latesttag} > /dev/null 2>&1
     version=`echo $latesttag | sed 's/-.*//'`
     sed -i "s/version: .*/version: $version/" app/config/parameters.yml
     sudo rm -rf /tmp/* var/cache/* var/logs/*
     echo 10
     docker-compose run mawaqit_composer sh -c "export SYMFONY_ENV=raspberry; composer install -o -n --no-dev --no-suggest --prefer-dist --no-progress"
     echo 40
     sudo rm -rf var/cache/* var/logs/*
     docker-compose exec mawaqit_php bin/console assets:install --env=raspberry --no-debug
     echo 60
     docker-compose exec mawaqit_php bin/console assetic:dump --env=raspberry --no-debug
     docker-compose exec mawaqit_php sh -c "export SYMFONY_ENV=raspberry; bin/console doc:mig:mig -n --allow-no-migration"
     echo 90
     sudo rm -rf var/cache/* var/logs/* 
     echo 100
  } | whiptail --gauge "Please wait for upgrading, this may take a fiew minutes..." 10 60 0
else
  whiptail --title "Nothig to do" --msgbox "You are on the last version $latesttag :)" 10 60
fi
