#!/bin/bash

NC='\033[0m' # No Color
RED='\033[0;31m'
GREEN='\033[0;32m'
ORANGE='\033[0;33m'
APP_CONTAINER='server-status-php'

function renderHelp() {
    echo -e "${GREEN}Console tool${NC}"
    echo ""
    echo -e "${ORANGE}Available commands for localhost environment:${NC}"
    echo -e " ${GREEN}up${NC}            Start development environment."
    echo -e " ${GREEN}down${NC}             Stop development environment."
    echo ""
    echo -e "${ORANGE}Available commands in container:${NC}"
    echo -e " ${GREEN}test-unit${NC}        Tests - unit tests."
    exit 0;
}


function composeUp() {
    MYSQL_HOST='server-status-mysql'
    MYSQL_USER='root'
    MYSQL_PASS='root'
    MYSQL_DB='server-status'
    cp config/config.php src/config.php
    docker compose up -d --build;
    until docker compose run server-status-php mysql -h ${MYSQL_HOST} -u ${MYSQL_USER} -p${MYSQL_PASS} -e ""; do
        >&2 echo "MySQL is unavailable, waiting for it to start.";
        sleep 2;
    done;
    docker compose run server-status-php \
        mysql \
            -h ${MYSQL_HOST} \
            -u ${MYSQL_USER} \
            -p${MYSQL_PASS} server-status < sql/demo.sql;
    echo "==== Environment ready! ====";
}


function composeDown() {
    docker compose down;
}