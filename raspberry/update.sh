#!/bin/bash

cd /home/pi/mawaqit

wget -q --spider http://google.com

if [[ $? -ne 0 ]]; then
  whiptail --title "No internet" --msgbox "Please connect to the internet" 10 60
  exit 0;
fi

{
    echo 5
    git fetch
    latesttag=$(git tag | sort -V | tail -1)
    git checkout ${latesttag} > /dev/null 2>&1
    version=`echo $latesttag | sed 's/-.*//'`
    echo 10
    sed -i "s/version: .*/version: $version/" app/config/parameters.yml
    sudo rm -rf /tmp/* var/cache/* var/logs/*
    docker-compose run mawaqit_composer sh -c "export SYMFONY_ENV=raspberry; composer install -o -n --no-dev --no-suggest --prefer-dist --no-progress" /dev/null 2>&1
    echo 40
    sudo rm -rf var/cache/* var/logs/*
    docker-compose exec mawaqit_php bin/console assets:install --env=raspberry --no-debug /dev/null 2>&1
    echo 60
    docker-compose exec mawaqit_php bin/console assetic:dump --env=raspberry --no-debug /dev/null 2>&1
    echo 80
    docker-compose exec mawaqit_php sh -c "export SYMFONY_ENV=raspberry; bin/console doc:mig:mig -n --allow-no-migration" /dev/null 2>&1
    echo 90
    sudo rm -rf var/cache/* var/logs/*
    echo 100
} | whiptail --gauge "Updating in progress, this may take a few minutes... please wait" 10 60 0