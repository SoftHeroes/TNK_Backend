#!/bin/bash
source ../scripts/setup.sh

mytitle="Calculate Game Results"
command="while:calculateGameResults"

if [ "$env" == "local" ]; then
    echo -e '\033]2;'$mytitle'\007'
fi

cd ..

if [ "$env" == "local" ]; then
    clear
    echo 'runing command : php artisan '$command
fi

php artisan $command
exit 0