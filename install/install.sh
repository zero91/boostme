#!/bin/bash

WEB_ROOT=..
DB_USER=boostme_alpha
DB_PASSWD=boostme_alpha
DB_NAME=boostme_alpha
#MYSQL_BIN=/usr/local/mysql/bin/mysql
MYSQL_BIN=`which mysql`

#force=0
#while [ -n "$1" ]; do
#    case "$1" in
#        -f) force=1; shift 1;;
#        *) break;;
#    esac
#done

mkdir -p $WEB_ROOT/data
mkdir -p $WEB_ROOT/data/cache
mkdir -p $WEB_ROOT/data/view
mkdir -p $WEB_ROOT/data/avatar
mkdir -p $WEB_ROOT/data/logs
mkdir -p $WEB_ROOT/data/backup
mkdir -p $WEB_ROOT/data/attach
mkdir -p $WEB_ROOT/data/tmp
chmod -R 777 $WEB_ROOT/data

$MYSQL_BIN -u$DB_USER -p$DB_PASSWD $DB_NAME < boostme.sql
