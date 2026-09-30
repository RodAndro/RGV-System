#!/bin/sh
set -eu

port="${PORT:-8080}"
sed -ri "s/Listen 8080/Listen ${port}/" /etc/apache2/ports.conf
sed -ri "s/:8080>/:${port}>/" /etc/apache2/sites-available/000-default.conf

exec apache2-foreground