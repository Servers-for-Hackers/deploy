#! /usr/bin/env bash
rsync -vzcrSLh --exclude=".idea" --exclude="deploy.sh" --exclude="design" --exclude=".git*" \
    ./ sfhdeploy:/var/www/deploy.serversforhackers.com/public
