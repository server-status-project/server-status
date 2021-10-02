#!/bin/bash

source docker/helper.sh


if [ $# -eq 0 ]; then
    renderHelp;
    exit 0;
fi


case "$1" in
    up)
        composeUp;
        ;;

    down)
        composeDown;
        ;;

    lint)
        php ./tools/phpcs.phar --standard=./tools/ruleset.xml --extensions=php --ignore=./src/vendor,./src/config.php ./src;
        ;;

    lint-sum)
        php ./tools/phpcs.phar --standard=./tools/ruleset.xml --extensions=php --ignore=./src/vendor,./src/config.php --report=summary ./src;
        ;;

    lint-fix)
        php ./tools/phpcbf.phar --standard=./tools/ruleset.xml --extensions=php --ignore=./src/vendor,./src/config.php ./src;
        ;;

    *)
        echo "Bad choice, try it again..";
        exit 1;
        ;;

esac

exit 0;
