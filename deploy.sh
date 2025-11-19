#!/bin/bash
cd /home/u119812537/domains/rice2.pixelstail.com/public_html
git fetch origin
git reset --hard origin/master
/opt/alt/php83/usr/bin/php $(which composer) install --no-dev --optimize-autoloader
