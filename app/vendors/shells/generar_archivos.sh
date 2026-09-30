#!/bin/bash
cd /var/www/naturales
/usr/share/php/cake/console/cake generar_archivos -app app
fecha=$(date +%Y%m%d)
mkdir /tmp/$fecha
cd /tmp/$fecha/
ftp -n <<EOF
open  FSCCSAPL24FTP
user usrftp@activo.com @dmin2008
lcd /tmp/$fecha
cd Lcash
mkdir $fecha
cd $fecha
prompt
mput *
EOF