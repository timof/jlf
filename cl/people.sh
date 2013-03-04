#!/bin/bash
#
# template/sample shell script to access sql:
# obtain mysql config, then call the php script on the server
#

db_server=selene
db_name='pi-potsdam'
db_user='pi-potsdam'
application_name='pi'
application_instance='pi'
scriptdir=/Users/jlf
pwfile=/keys/mysql.pi-potsdam.selene

# to harden input against shell parser:
# - hexdump -v -e '/1 "%02x"' creates a plain hexdump (why is that not default???)
# - xxd -p -r is its reverse function
#
hexargs=
while [ $# -gt 0 ] ; do
  hexargs="$hexargs."`echo -n "$1" | hexdump -v -e '/1 "%02x"'`
  shift
done

[ `hostname` = "$db_server" ] || { echo "wrong host!"; exit 7 ; }

### command="
  jlf_mysql_db_server=127.0.0.1
  jlf_mysql_db_name='$db_name'
  jlf_mysql_db_user='$db_user'
  jlf_application_name='$application_name'
  jlf_application_instance='$application_instance'
  read jlf_mysql_db_password < $pwfile
  export jlf_mysql_db_server jlf_mysql_db_name jlf_mysql_db_user jlf_mysql_db_password jlf_application_name jlf_application_instance
  cd $scriptdir
  ./cl/cl $hexargs
### "

### ssh $db_server "$command"


