#!/bin/bash

# online site
url=http://mawaqit.local/mosquee

if [[ -f ~/Desktop/online_site.txt ]]; then
    url=`cat ~/Desktop/online_site.txt`
fi

if [[ -f ~/pi/mawaqit/docker/data/online_url.txt ]]; then
    url=`cat ~/pi/mawaqit/docker/data/online_url.txt`
fi

i=0
while ! wget -q --spider --timeout=2 $url; do
  sleep 2
  ((i+=1))
  if (( $i == 10 )); then
    # warmup cache and set local url
    docker-compose exec mawaqit_php sh -c "bin/console c:w -e raspberry"
    docker-compose exec mawaqit_php chmod 777 -R var/cache

    url=http://mawaqit.local/mosquee
    break;
  fi 
done

chromium-browser --app=$url --start-fullscreen --start-maximized
