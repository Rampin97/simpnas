#!/bin/bash


if ! id -u "$1" > /dev/null 2> /dev/null;
then
  echo "user_not_found"
  exit 1
else
  ADMINUSER=$(groups "$1" | grep -w admins)
  export ADMINUSER
  if [ -z "$ADMINUSER" ]
  then
    echo "user_not_found"
    exit 1
  fi
  PASSWD=$2
  ORIGPASS=$(grep -w "$1" /etc/shadow | cut -d: -f2)
  ALGO=$(echo "$ORIGPASS" | cut -d'$' -f2)
  SALT=$(echo "$ORIGPASS" | cut -d'$' -f3)
  export ALGO
  export SALT
  export PASSWD
  GENPASS=$(perl -le 'print crypt("$ENV{PASSWD}","\$$ENV{ALGO}\$$ENV{SALT}\$")')
  
  if [ "$GENPASS" == "$ORIGPASS" ]
  then
    echo "ok"
    exit 0
  else
    echo "wrong_password"
    exit 1
  fi
fi
