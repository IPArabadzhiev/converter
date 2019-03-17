#!/usr/bin/env bash
#Example cron usage:
# chmod +x /var/www/html/crons/removeOlder.sh
# crontab -e
# 0 * * * * /var/www/html/crons/removeOlder.sh
# replace the path with the actual path of the script on the server
#Replace with actual path of downloads dir on the server
DOWNLOADS_DIR=/var/www/html/downloads
cd $DOWNLOADS_DIR;
#remove all .mp3 files that have been created more than a day ago
find ./ -type f -ctime +1 -iname "*.mp3" -exec rm {} \;

