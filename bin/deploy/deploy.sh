#!/bin/bash
if [ -z "$1" ]; then
echo "The version is mandatory";
exit 1
fi

rm -rf /tmp/prayer-times-v3
mkdir -p /tmp/prayer-times-v3

git archive $1 | (cd /tmp/prayer-times-v3 && tar xf -)


mkdir -p ~/www/prayer-times-v3/$1
rsync -r --delete --force --files-from=bin/deploy/files-to-package --exclude-from=bin/deploy/files-to-exclude /tmp/prayer-times-v3 ~/www/prayer-times-v3/$1
cd ~/www/prayer-times-v3/$1

cp docker/docker-compose.deploy.yml docker/docker-compose.yml 
cd docker
docker-compose up -d kill & docker-compose up -d --remove-orphans
cd ..

cp ~/perso/projects/prayer-times-v3-parameters.prod.yml ~/www/prayer-times-v3/$1/app/config/parameters.yml

chmod -R 777 var/cache var/logs var/sessions

./dock-deploy composer install --no-dev --optimize-autoloader
./dock-deploy php bin/console assets:install --env=prod --no-debug
./dock-deploy php bin/console assetic:dump --env=prod --no-debug

#rm -rf bin docker dock-deploy composer.*

cd ..

rm -f current
ln -s $1 current
