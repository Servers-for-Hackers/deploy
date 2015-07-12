#! /usr/bin/env bash
rsync -vzcrSLh --exclude="deploy.sh" --exclude="design" --exclude=".git*" \
    ./ sfh-deploy:/var/www/deploy.serversforhackers.com/public
