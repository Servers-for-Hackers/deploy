# FPM to build Debs

Why:

1. Install as package
2. Package meta data and dependencies
3. Post-install scripts for cleanup

Why not:

1. May have more supporting infrastructure, such as an apt server
2. Requires ability to use sudo to install, security hole?

Best when:

1. Deployments involve building a server (perhaps read-only servers) or if deployment is integrated with configuration management tool (Ansible, chef, etc)


## FPM (effing package management):

```bash
# As user admin
sudo apt-get install -y build-essential ruby-dev
sudo gem install fpm
```


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
mkdir -p ~/build-server/packages/$RELEASE

git archive --worktree-attributes master | tar -x -C ~/build-server/releases/$RELEASE

# Build application
cd ~/build-server/releases/$RELEASE

npm install
./node_modules/.bin/gulp

composer install

# Package application
cd ~/build-server/releases

fpm -t deb -s dir \
    -C ~/build-server/releases/$RELEASE \
    -p ~/build-server/packages/$RELEASE/serialapp.deb \
    --force \
    -n serialapp \
    --version=1.0.0 \
    --iteration=$RELEASE \
    --exclude="*/.git*" \
    --exclude="*/node_modules"\
    --exclude="*/.env*" \
    --exclude="*/fabfile.py*" \
    --exclude="*/storage" \
    --after-install="~/build-server/repo/build/postinstall.sh" \
    .=/home/appuser/serialapp/releases

dpkg -I packages/serialapp.deb
```

Best used with Tags!

## On web server:

```bash
appuser ALL=NOPASSWD:/usr/bin/dpkg -i serialapp.deb
```

## Post install script:
Put this in `~/build-server/bin/postinstall.sh`:

```bash
#!/usr/bin/env bash

# Chown files just installed
chown -R appuser:appuser /home/appuser/serialapp/releases
chown -R appuser:appuser /home/appuser/serialapp/packages
```

## Python

```python
from  __future__ import with_statement
import os
from time import time
from StringIO import StringIO
from tempfile import NamedTemporaryFile

from fabric.api import local, env, run, cd, get, lcd, put
from fabric.decorators import task
from fabric.contrib.files import exists

env.use_ssh_config = True
env.hosts = [
'serial-app-1',
]

release_dir     = '/home/appuser/serialapp/releases'
package_dir     = '/home/appuser/serialapp/packages'
current_release = '/home/appuser/serialapp/current'
persist_dir     = '/home/appuser/serialapp/persist'

@task
def build_deploy():
    sha = build_app()
    deploy(sha)

@task 
def build():
    return build_app()

@task
def deploy(sha):
    init()
    upload_site(sha)
    swap_symlinks(sha)

def init():
    if not exists(release_dir):
        run("mkdir -p %s" % release_dir)
        run("mkdir -p %s" % package_dir)

    if not exists("%s/storage" % persist_dir):
        run("mkdir -p %s/storage" % persist_dir)
        run("mkdir -p %s/storage/app" % persist_dir)
        run("mkdir -p %s/storage/framework" % persist_dir)
        run("mkdir -p %s/storage/framework/cache" % persist_dir)
        run("mkdir -p %s/storage/framework/sessions" % persist_dir)
        run("mkdir -p %s/storage/framework/views" % persist_dir)
        run("mkdir -p %s/storage/logs" % persist_dir)

def build_app():
    release_sha = None

    local("mkdir -p ~/build-server/releases")
    local("mkdir -p ~/build-server/packages")

    with lcd("~/build-server/repo"):
        local("git checkout master")
        local("git pull origin master")

        release_sha = local("git rev-parse HEAD", capture=True)

        local("mkdir -p ~/build-server/releases/%s" % release_sha)
        local("mkdir -p ~/build-server/packages/%s" % release_sha)
        local("git archive --worktree-attributes master | tar -x -C ~/build-server/releases/%s" % release_sha)

    with lcd("~/build-server/releases/%s" % release_sha):

        local("npm install")
        local("node ./node_modules/.bin/gulp")
        local("composer install")

        local('fpm -t deb -s dir \
    -C ~/build-server/releases/%(release)s \
    -p ~/build-server/packages/%(release)s/serialapp.deb \
    --force \
    -n serialapp \
    --version=1.0.0 \
    --iteration=%(release)s \
    --exclude="*/.git*" \
    --exclude="*/node_modules"\
    --exclude="*/.env*" \
    --exclude="*/fabfile.py*" \
    --exclude="*/storage" \
    --after-install="~/build-server/bin/postinstall.sh" \
    .=/home/appuser/serialapp/releases/%(release)s' % {
        'release': release_sha
    })

    return release_sha

def upload_site(sha):
    remote_release_dir = "%s/%s" % (package_dir, sha)
    local_release_file = "~/build-server/packages/%s/serialapp.deb" % sha

    run("mkdir -p %s" % remote_release_dir)
    put(local_path=local_release_file, remote_path=remote_release_dir)

    with cd(remote_release_dir):
        # INSTALL LATEST VERSION
        # requires sudo!
        run("sudo dpkg -i serialapp.deb")

def swap_symlinks(sha):
    # Build release directory
    release_into = "%s/%s" % (release_dir, sha)

    # Symlink new .env and existing storage
    run("ln -nfs %s/.env %s/.env" % (persist_dir, release_into))
    run("rm -rf %s/storage" % release_into)
    run("ln -nfs %s/storage %s/storage" % (persist_dir, release_into))

    # Put site live
    run("ln -nfs %s %s" % (release_into, current_release))

    # Reload PHP-FPM gracefully
    if exists('/etc/redhat-release'):
        run('sudo service php-fpm reload')
    else:
        run('sudo service php5-fpm reload')
```


RAISES SOME QUESTIONS:
    - Do we keep using symlinks? Or just let it install over it?
    - Does this change anything with downtime if we don't use symlinks?


On web server:

```bash
appuser ALL=NOPASSWD:/usr/bin/dpkg -i serialapp.deb
```

Post install script:

```bash
sudo chown -R appuser:appuser /home/appuser/serialapp/releases
sudo chown -R appuser:appuser /home/appuser/serialapp/packages
```



```python
local('fpm -t deb -s dir \
    -C $(repo_dir)s \
    -p %(release_into)s/serialapp.deb \
    --force \
    -n serialapp \
    --version=1.0.0 \
    --iteration=%(sha)s \
    --exclude="*/.git*" \
    --exclude="*/node_modules"\
    --exclude="*/.env*" \
    --exclude="*/fabfile.py*" \
    --exclude="*/storage" \
    --after-install="build/postinstall.sh" \
    .=%(current_release)s' % {
        'repo_dir': repo_dir,
        'release_into': release_into,
        'sha': sha,
        'current_release': current_release
    })
```