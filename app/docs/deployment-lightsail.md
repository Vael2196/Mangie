# Deploy Mangie to mangie.vaell.dev

This runbook deploys Mangie into the shared Lightsail layout selected for
vaell.dev:

- one Ubuntu 24.04 Lightsail instance in Sydney;
- one host Nginx process owning public ports 80 and 443;
- one shared MySQL 8.4 container on a private Docker network;
- separate databases and users for each project;
- Mangie application, queue, scheduler, and Reverb containers;
- Cloudflare DNS and proxying;
- HTTPS at the origin with Let's Encrypt.

Mangie's HTTP and Reverb ports bind only to 127.0.0.1. They are not opened in
the Lightsail firewall. Nginx is the only public entry point.

## 1. Before starting

Complete the shared-server guide first. The expected server is:

| Setting | Expected value |
| --- | --- |
| Instance | projects-prod-01 |
| Region | Sydney (ap-southeast-2) |
| OS | Ubuntu 24.04 LTS |
| Plan | 2 GB RAM, 2 vCPUs, 60 GB SSD |
| Public firewall | TCP 22 restricted, TCP 80 and 443 public |
| Docker | Docker Engine and the Compose plugin installed |
| Static IP | projects-prod-ip attached |

Do not open ports 3306, 8081, or 8082 in Lightsail.

## 2. Add the Cloudflare DNS record

In Cloudflare, open vaell.dev → DNS → Records and add:

| Field | Value |
| --- | --- |
| Type | A |
| Name | mangie |
| IPv4 address | the Lightsail static IPv4 |
| Proxy status | DNS only initially |
| TTL | Auto |

Confirm that the record resolves to the Lightsail address:

~~~bash
getent ahostsv4 mangie.vaell.dev
~~~

Leave the record DNS-only until the origin certificate has been issued.

## 3. Install Docker, host Nginx, and Certbot

If Docker Engine and the Compose plugin are not already installed, add
Docker's official Ubuntu repository and install them:

~~~bash
sudo apt update
sudo apt install -y ca-certificates curl
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg \
    -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc

. /etc/os-release
printf '%s\n' \
    "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu ${UBUNTU_CODENAME:-$VERSION_CODENAME} stable" \
    | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

sudo apt update
sudo apt install -y \
    docker-ce \
    docker-ce-cli \
    containerd.io \
    docker-buildx-plugin \
    docker-compose-plugin
sudo systemctl enable --now docker
sudo docker run --rm hello-world
~~~

Then install Nginx and Certbot on the host:

~~~bash
sudo apt update
sudo apt install -y nginx certbot python3-certbot-nginx
sudo systemctl enable --now nginx
~~~

The project containers do not publish ports 80 or 443, so they will not
compete with Nginx or with future projects.

## 4. Clone Mangie

~~~bash
sudo install -d -o "$USER" -g "$USER" /srv/projects
git clone https://github.com/Vael2196/Mangie.git /srv/projects/mangie
cd /srv/projects/mangie/app
~~~

Future commands in this guide that refer to the application directory are run
from /srv/projects/mangie/app.

## 5. Create the shared private Docker network

This is a one-time server action. Future project containers join the same
network but use separate MySQL credentials.

~~~bash
sudo docker network inspect projects_backend >/dev/null 2>&1 \
    || sudo docker network create projects_backend
~~~

## 6. Start the shared MySQL service

Copy the supplied server files:

~~~bash
sudo install -d /srv/shared/mysql/conf.d
sudo cp deploy/server/shared-mysql.compose.yaml \
    /srv/shared/mysql/compose.yaml
sudo cp deploy/server/mysql/low-memory.cnf \
    /srv/shared/mysql/conf.d/low-memory.cnf
sudo cp deploy/server/shared-mysql.env.example \
    /srv/shared/mysql/.env
sudo chmod 600 /srv/shared/mysql/.env
~~~

Generate a root password:

~~~bash
openssl rand -hex 32
~~~

Edit /srv/shared/mysql/.env and replace CHANGE_ME with that value:

~~~bash
sudoedit /srv/shared/mysql/.env
~~~

Start and inspect MySQL:

~~~bash
cd /srv/shared/mysql
sudo docker compose up -d
sudo docker compose ps
sudo docker compose logs --tail=50 mysql
~~~

Wait until the container reports healthy.

The supplied MySQL configuration limits the buffer pool and connection count
for the 2 GB shared instance. Review those limits when more projects are
deployed or the Lightsail plan is upgraded.

## 7. Create Mangie's database and database user

Generate a separate password for Mangie:

~~~bash
openssl rand -hex 32
~~~

Keep this value available for the next section. Open the MySQL console:

~~~bash
sudo docker exec -it shared-mysql mysql -uroot -p
~~~

Enter the shared MySQL root password, then run the following SQL. Replace
MANGIE_DATABASE_PASSWORD with the newly generated Mangie password:

~~~sql
CREATE DATABASE mangie
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

CREATE USER 'mangie'@'%'
    IDENTIFIED BY 'MANGIE_DATABASE_PASSWORD';

GRANT ALL PRIVILEGES ON mangie.* TO 'mangie'@'%';
FLUSH PRIVILEGES;
EXIT;
~~~

The MySQL container has no published host port. The user is reachable only
from containers attached to projects_backend.

## 8. Configure Mangie's production environment

Return to the application directory:

~~~bash
cd /srv/projects/mangie/app
cp .env.production.example .env.production
chmod 600 .env.production
~~~

Generate the remaining secrets:

~~~bash
printf 'APP_KEY=base64:'
openssl rand -base64 32

openssl rand -hex 16
openssl rand -hex 32
~~~

Edit the file:

~~~bash
nano .env.production
~~~

Replace these values:

- APP_KEY: the complete base64 value, including the base64: prefix.
- DB_PASSWORD: the Mangie database password from section 7.
- REVERB_APP_KEY: the 16-byte hexadecimal value.
- REVERB_APP_SECRET: the 32-byte hexadecimal value.

Do not commit .env.production. It is ignored by Git and should remain mode
0600 on the server.

The supplied defaults already configure:

- APP_URL as https://mangie.vaell.dev;
- production mode with debugging disabled;
- secure, encrypted database sessions;
- the database-backed queue and cache;
- Reverb at wss://mangie.vaell.dev on port 443;
- trusted proxy headers from the loopback Nginx proxy;
- HTTP port 8081 and Reverb port 8082 on 127.0.0.1 only.

If password-reset emails should work publicly, replace the MAIL settings with
credentials from an SMTP provider before inviting users. The default log
mailer does not deliver email.

## 9. Validate and build the stack

Always pass the production environment file to Compose because it contains
both container variables and build-time Vite variables:

~~~bash
sudo docker compose \
    --env-file .env.production \
    -f compose.production.yaml \
    config

sudo docker compose \
    --env-file .env.production \
    -f compose.production.yaml \
    build --pull
~~~

The build creates one immutable Mangie image containing:

- PHP 8.5 and the required extensions;
- Apache with the document root restricted to public/;
- Composer production dependencies;
- compiled Vite/Tailwind assets containing the production Reverb endpoint.

## 10. Choose the initial-data path

Use exactly one of the following paths.

### Option A: fresh production database

Start the stack:

~~~bash
sudo docker compose \
    --env-file .env.production \
    -f compose.production.yaml \
    up -d
~~~

The web container waits for MySQL and runs migrations automatically. Inspect
the result:

~~~bash
sudo docker compose \
    --env-file .env.production \
    -f compose.production.yaml \
    ps

sudo docker compose \
    --env-file .env.production \
    -f compose.production.yaml \
    logs --tail=100 app
~~~

Create the first administrator, project, backlog, backlog column, and standard
labels using the interactive bootstrap command:

~~~bash
sudo docker compose \
    --env-file .env.production \
    -f compose.production.yaml \
    exec app php artisan mangie:bootstrap
~~~

The password prompt is hidden and requires at least 12 characters. The command
refuses to run after any user, project, or board data exists, so it cannot
silently overwrite an established installation.

### Option B: move the existing development database

Before the first application start, export the development database on the
local computer:

~~~bash
mysqldump -u root -p \
    --single-transaction \
    --routines \
    --triggers \
    YOUR_LOCAL_DATABASE > mangie.sql

scp mangie.sql ubuntu@YOUR_STATIC_IP:/srv/projects/mangie/
~~~

Import it on the server using the root password already present inside the
shared MySQL container:

~~~bash
cd /srv/projects/mangie
sudo docker exec -i shared-mysql sh -c \
    'exec mysql -uroot -p"$MYSQL_ROOT_PASSWORD" mangie' \
    < mangie.sql
rm mangie.sql
~~~

Then start Mangie:

~~~bash
cd /srv/projects/mangie/app
sudo docker compose \
    --env-file .env.production \
    -f compose.production.yaml \
    up -d
~~~

The normal startup migration applies any migrations that were not present in
the imported database. Do not run mangie:bootstrap after importing data.

If existing avatars and board backgrounds are needed, archive
app/storage/app/public on the local computer, copy the archive to the server,
and restore it into the mangie_storage volume after the image has been built:

~~~bash
sudo docker compose \
    --env-file .env.production \
    -f compose.production.yaml \
    run --rm --no-deps \
    --entrypoint tar \
    app -C /var/www/html/storage/app/public -xzf - \
    < /path/to/mangie-uploads.tar.gz
~~~

## 11. Verify Mangie before adding public Nginx routing

~~~bash
curl --fail --silent --show-error \
    http://127.0.0.1:8081/up

sudo docker compose \
    --env-file .env.production \
    -f compose.production.yaml \
    exec app php artisan about --only=environment
~~~

The health request should return an HTTP 200 response. All four services
should be running:

- app;
- queue;
- scheduler;
- reverb.

## 12. Add the Nginx virtual host

~~~bash
sudo cp deploy/nginx/mangie.vaell.dev.conf \
    /etc/nginx/sites-available/mangie.vaell.dev

sudo ln -sfn \
    /etc/nginx/sites-available/mangie.vaell.dev \
    /etc/nginx/sites-enabled/mangie.vaell.dev

sudo nginx -t
sudo systemctl reload nginx
~~~

Test hostname routing locally on the server:

~~~bash
curl -I -H 'Host: mangie.vaell.dev' http://127.0.0.1/up
~~~

This should return 200 before HTTPS is issued.

## 13. Issue the HTTPS certificate

Confirm that Cloudflare is still DNS-only and that mangie.vaell.dev resolves
to the Lightsail static address. Then run:

~~~bash
sudo certbot --nginx \
    -d mangie.vaell.dev \
    --redirect
~~~

Choose the contact email and accept the terms when prompted. Certbot updates
the Nginx site with the certificate and HTTP-to-HTTPS redirect.

Verify renewal:

~~~bash
sudo systemctl status certbot.timer
sudo certbot renew --dry-run
curl -I https://mangie.vaell.dev/up
~~~

Do not recopy the original Nginx template over the Certbot-modified file after
this point.

## 14. Enable Cloudflare proxying

In Cloudflare:

1. Change the mangie A record to Proxied (orange cloud).
2. Open SSL/TLS and select Full (strict).
3. Open Network and confirm WebSockets is On.
4. Do not create a Cache Everything rule for this authenticated application.

Full (strict) verifies the Let's Encrypt certificate at the Lightsail origin.
Cloudflare supports proxied WebSockets, so the application and Reverb can use
the same hostname and public HTTPS port.

Verify the site in a private browser window:

- https://mangie.vaell.dev loads without a certificate warning;
- login and registration work;
- an avatar or board background upload remains after a container restart;
- two browser sessions see a task update without refreshing;
- the browser's WebSocket request receives status 101.

## 15. Deploy later updates

The supplied deployment script performs a fast-forward pull, builds the image,
briefly enables Laravel maintenance mode, recreates the containers, and checks
the local health endpoint:

~~~bash
cd /srv/projects/mangie/app
./deploy/bin/deploy.sh
~~~

The web container automatically runs php artisan migrate --force before Apache
starts. The persistent storage and MySQL volumes are not replaced by a normal
deployment.

Before a migration-heavy release, run the backup script manually:

~~~bash
sudo /srv/projects/mangie/app/deploy/bin/backup.sh
~~~

## 16. Schedule backups

The backup script writes a compressed MySQL dump and a compressed upload
archive to /srv/backups/mangie. It retains 14 days by default.

Test it:

~~~bash
sudo /srv/projects/mangie/app/deploy/bin/backup.sh
sudo ls -lh /srv/backups/mangie
~~~

Edit root's crontab:

~~~bash
sudo crontab -e
~~~

Add:

~~~cron
17 3 * * * /srv/projects/mangie/app/deploy/bin/backup.sh >> /var/log/mangie-backup.log 2>&1
~~~

This runs at 03:17 server time. Continue using Lightsail automatic snapshots,
and periodically copy /srv/backups/mangie to a different machine or storage
service. Backups stored only on the same instance are not sufficient if the
instance is lost.

## 17. Useful operations

Show service state:

~~~bash
sudo docker compose \
    --env-file .env.production \
    -f compose.production.yaml \
    ps
~~~

Follow application logs:

~~~bash
sudo docker compose \
    --env-file .env.production \
    -f compose.production.yaml \
    logs -f app queue reverb
~~~

Restart Reverb after configuration changes:

~~~bash
sudo docker compose \
    --env-file .env.production \
    -f compose.production.yaml \
    restart reverb
~~~

Run the test suite inside the production image without changing production
data only if a separate testing environment has been configured. Do not point
phpunit.xml at the production mangie database.

Check disk and memory:

~~~bash
df -h
free -h
sudo docker system df
sudo docker stats --no-stream
~~~

Do not run docker system prune --volumes. The named volumes contain MySQL data
and uploaded files.

## 18. Restore a backup

Put the application into maintenance mode:

~~~bash
sudo docker compose \
    --env-file .env.production \
    -f compose.production.yaml \
    exec -T app php artisan down
~~~

Restore a database dump:

~~~bash
gzip -dc /srv/backups/mangie/database-TIMESTAMP.sql.gz \
    | sudo docker exec -i shared-mysql sh -c \
        'exec mysql -uroot -p"$MYSQL_ROOT_PASSWORD" mangie'
~~~

Restore uploads:

~~~bash
gzip -dc /srv/backups/mangie/uploads-TIMESTAMP.tar.gz \
    | sudo docker compose \
        --env-file .env.production \
        -f compose.production.yaml \
        exec -T app \
        tar -C /var/www/html/storage/app/public -xf -
~~~

Bring the application back:

~~~bash
sudo docker compose \
    --env-file .env.production \
    -f compose.production.yaml \
    exec -T app php artisan up
~~~

## 19. Troubleshooting

### Application container repeatedly restarts

~~~bash
sudo docker compose \
    --env-file .env.production \
    -f compose.production.yaml \
    logs --tail=200 app
~~~

Common causes are an unchanged CHANGE_ME value, a missing database/user, or
an incorrect DB_PASSWORD.

### Website works but real-time changes do not

Check Reverb and Nginx:

~~~bash
sudo docker compose \
    --env-file .env.production \
    -f compose.production.yaml \
    logs --tail=200 reverb queue

sudo nginx -t
sudo tail -n 200 /var/log/nginx/error.log
~~~

Confirm that the current image was rebuilt after changing REVERB_APP_KEY.
Vite embeds that public key into the browser bundle at build time.

### Uploads return 413

The Nginx template allows 10 MB. Mangie validates board images at 8 MB. If the
Nginx file was customized, ensure client_max_body_size remains at least 9 MB.

### Cloudflare shows 521 or 502

Check that Nginx is running, the Lightsail firewall allows 80/443, and the
loopback services respond:

~~~bash
sudo systemctl status nginx
curl -I http://127.0.0.1:8081/up
sudo ss -lntp | grep -E ':80|:443|:8081|:8082'
~~~

## Official references

- Laravel deployment:
  https://laravel.com/docs/13.x/deployment
- Laravel Reverb production proxying:
  https://laravel.com/docs/13.x/reverb#web-server
- Docker Engine on Ubuntu:
  https://docs.docker.com/engine/install/ubuntu/
- Docker Compose for Laravel:
  https://docs.docker.com/guides/laravel/
- Cloudflare proxy status:
  https://developers.cloudflare.com/dns/proxy-status/
- Cloudflare Full (strict):
  https://developers.cloudflare.com/ssl/origin-configuration/ssl-modes/full-strict/
- Cloudflare WebSockets:
  https://developers.cloudflare.com/network/websockets/
- Lightsail firewall:
  https://docs.aws.amazon.com/lightsail/latest/userguide/understanding-firewall-and-port-mappings-in-amazon-lightsail.html
- Lightsail static IP:
  https://docs.aws.amazon.com/lightsail/latest/userguide/lightsail-create-static-ip.html
