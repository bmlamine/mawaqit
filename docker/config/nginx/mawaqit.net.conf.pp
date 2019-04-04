proxy_cache_path /tmp/nginx_cache levels=1:2 keys_zone=mobile:10m inactive=60m inactive=24h  max_size=1g;
proxy_cache_key "$scheme$request_method$host$request_uri";

server {
      listen 80 default_server;
      listen [::]:80 default_server;
      server_name pp.mawaqit.net;

      # redirect HTTP to HTTPS
      return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl;
    listen [::]:443 ssl;

    proxy_buffers 4 64k;
    proxy_buffer_size 64k;

    #SSL Certificates
    ssl on;
    ssl_certificate "/etc/letsencrypt/live/pp.mawaqit.net/fullchain.pem";
    ssl_certificate_key "/etc/letsencrypt/live/pp.mawaqit.net/privkey.pem";
    ssl_stapling on;
    ssl_stapling_verify on;
    ssl_trusted_certificate /etc/letsencrypt/live/pp.mawaqit.net/fullchain.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

    location  / {
        proxy_buffering   on;
        proxy_cache_valid 200 302 301 1d;
        proxy_cache_valid 404 1h;
        proxy_cache mobile;
        include proxy_params;
        add_header X-Proxy-Cache $upstream_cache_status;
        proxy_cache_use_stale  error timeout invalid_header updating http_500 http_502 http_503 http_504;
        proxy_pass http://localhost:81/;
    }
}

server {
    listen 81;
    root /var/www/mawaqit/web;
    server_name pp.mawaqit.net;

    location / {
        try_files $uri /app.php$is_args$args;
    }

    location ~ ^/(\w+)\.php(/|$) {
        fastcgi_pass 127.0.0.1:9000;
        include fastcgi_params;
        fastcgi_read_timeout 300;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        fastcgi_param HTTPS on;
        fastcgi_param APP_ENV pp;
        internal;
    }

    location ~ \.php$ {
        return 404;
    }

    set $cache_uri $request_uri;

    location ~ /.well-known {
        allow all;
    }

    # cache assets
    location ~* \.(?:jpg|jpeg|gif|png|ico|gz|svg|mp4|css|js|eot|woff|woff2|ttf)$ {
        expires 1y;
        access_log off;
        add_header Cache-Control "public";
    }

    # Deny dotfiles
    location ~ /\. { deny all; access_log off; log_not_found off; }
}
