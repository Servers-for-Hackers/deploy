# Build Server Builds

## Git Repo

As our build server has the Github key for this repository, I can close it on the build server:

```bash
# As seen in ~/.ssh/id_github
#   and with help from ~/.ssh/config
# As "appuser"
cd ~/build-server
git clone git@github.com:Servers-for-Hackers/serialapp.git repo
```

## Node Dependencies
Now let's install dependencies needed to build our application.

Our build server already has NodeJS, so when we build, we can:

```bash
npm install
./node_modules/.bin/gulp
```

## PHP Dependencies

We'll need PHP stuff now:

```bash
# As sudo user, admin

# Install PHP
sudo add-apt-repository -y ppa:ondrej/php5-5.6
sudo apt-get update
# Also install zip to zip up builds
sudo apt-get install -y \
    php5-cli php5-mcrypt \
    zip

# Install Composer
curl -sS https://getcomposer.org/installer | sudo php
sudo mv composer.phar /usr/local/bin/composer
```

## Build It!

That should be it. We can now move a little bit of our build set to a local one:

```bash
#!/usr/bin/env bash

# Bail on first non-zero exit code
set -e

# Ensure this dir exists
mkdir -p ~/build-server/packages

# Get latest code and copy to releases
# Create new release and package directory
cd ~/build-server/repo
git checkout master
git pull origin master

RELEASE=`git rev-parse HEAD`
mkdir -p ~/build-server/releases/$RELEASE

git archive --worktree-attributes master | tar -x -C ~/build-server/releases/$RELEASE

# Build application
cd ~/build-server/releases/$RELEASE

npm install
./node_modules/.bin/gulp

composer install

# Package application
cd ~/build-server/releases
zip -r --exclude="*.git*" --exclude="*node_modules*" \
    ~/build-server/packages/${RELEASE}.zip ./${RELEASE}
```

Run that and time it:

```bash
time bash build.sh
```

## Package It

We'll package it by making a zip for now. Or tar, or tar.bz, whatever you want!



## Cache NPM Locally

(If you don't have a fancy private repo):

https://www.npmjs.com/package/npm-cache




