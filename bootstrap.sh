#!/usr/bin/env bash

apt-get update
export DEBIAN_FRONTEND=noninteractive
apt-get install -q -y apache2 php5 libapache2-mod-php5 php5-cli php-pear mysql-server php5-mysql phpmyadmin
rm -rf /var/www
ln -fs /vagrant /var/www

# create a root user for testing
mysqladmin -u root password root

# put the default example database into mysql
mysqladmin -u root -proot create citrus
mysql -u root -proot citrus < /var/www/citrus.sql

#pear config-set auto_discover 1
#pear install pear.phpunit.de/PHPUnit
#/etc/init.d/apache2 restart