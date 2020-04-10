#!/bin/bash

action=touch

if [[ "$1" == "remove" ]]; then
    action="rm -f"
fi

ssh -p 1983 -t mawaqit@$MAWAQIT_PROD_IP '$action /var/www/mawaqit/repo/docker/data/maintenance && docker exec mawaqit_nginx nginx -s reload'