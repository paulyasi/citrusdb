#!/usr/bin/env bash

apt-get update
export DEBIAN_FRONTEND=noninteractive
apt-get install -q -y apache2 php5 libapache2-mod-php5 php5-cli php-pear mysql-server php5-mysql phpmyadmin
rm -rf /var/www
ln -fs /vagrant /var/www

# add phpmyadmin to apache config
echo "Include /etc/phpmyadmin/apache.conf" >> /etc/apache2/apache2.conf

# create a root user for testing
mysqladmin -u root password root

# drop old database during development to start with fresh one each time
mysqladmin -u root -proot drop --force citrus
mysqladmin -u root -proot create citrus

# change this to load 242 file for testing the update command
mysql -u root -proot citrus < /var/www/citrus242.sql

#pear config-set auto_discover 1
#pear install pear.phpunit.de/PHPUnit

# restart apache for new settings to take effect
/etc/init.d/apache2 restart