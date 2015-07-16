#! /usr/bin/env bash
rsync -vzcrSLh --exclude="deploy.sh" --exclude="design" --exclude=".git*" \
    ./ sfhdeploy:/var/www/deploy.serversforhackers.com/public
