echo "Starting"
apt-get update
apt-get install -y python3
flock -n /var/lock/cron.sh.lock bash /var/cron/cron.sh
crontab -r
(crontab -l ; echo "* * * * * flock -n /var/lock/cron.sh.lock bash /var/cron/cron.sh") | crontab
cron
docker-php-entrypoint apache2-foreground
