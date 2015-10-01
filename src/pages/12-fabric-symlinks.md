Capistrano style, zero down-time deployment.

* Updates local repository
* Copies files to a release directory
* Runs any commands needed
* Swaps out current release with latest release
* Allows for rollbacks

## Nginx:

```nginx
server {
    root /home/serial/serialapp.com/current/public
}
```

## Persistent Files

```bash
mkdir -p /home/serial/serialapp.com/persist
cp .env /home/serial/serialapp.com/persist/.env
```

```python
from __future__ import with_statement
from time import time

from fabric.api import local, env, run, cd, get
from fabric.decorators import task
from fabric.contrib.files import exists

env.use_ssh_config = True
env.hosts = ['serial-app']
# env.hosts = ['45.55.209.211']
# env.user = 'serial'
# env.key_filename = '~/.ssh/id_series'

git_repo        = 'git@github.com:Servers-for-Hackers/serialapp.git'
git_branch      = 'master'
release_dir     = '/home/serial/serialapp.com/releases'
current_release = '/home/serial/serialapp.com/current'
repo_dir        = '/home/serial/serialapp.com/repo'
persist_dir     = '/home/serial/serialapp.com/persist'
next_release    = "%(time).0f" % {'time': time()}

@task
def deploy():
    init()
    update_git()
    create_release()
    build_site()
    swap_symlinks()

def init():
    if not exists(release_dir):
        run("mkdir -p %s" % release_dir)

    if not exists(repo_dir):
        run("git clone -b %s %s %s" % (git_branch, git_repo, repo_dir))

    if not exists("%s/storage" % persist_dir):
        run("mkdir -p %s/storage" % persist_dir)
        run("mkdir -p %s/storage/app" % persist_dir)
        run("mkdir -p %s/storage/framework" % persist_dir)
        run("mkdir -p %s/storage/framework/cache" % persist_dir)
        run("mkdir -p %s/storage/framework/session" % persist_dir)
        run("mkdir -p %s/storage/framework/views" % persist_dir)
        run("mkdir -p %s/storage/logs" % persist_dir)

def update_git():
    with cd(repo_dir):
        run("git checkout %s" % git_branch)
        run("git pull origin %s" % git_branch)

def create_release():
    release_into = "%s/%s" % (release_dir, next_release)
    run("mkdir -p %s" % release_into)
    with cd(repo_dir):
        run("git archive --worktree-attributes %s | tar -x -C %s" % (git_branch, release_into))

def build_site():
    with cd("%s/%s" % (release_dir, next_release)):
        run("composer install")

def swap_symlinks():
    # Build release directory
    release_into = "%s/%s" % (release_dir, next_release)
    
    # Symlink new .env and existing storage
    run("ln -nfs %s/.env %s/.env" % (persist_dir, release_into))
    run("rm -rf %s/storage" % release_into)
    run("ln -nfs %s/storage %s/storage" % (persist_dir, release_into))

    # Put site live
    run("ln -nfs %s %s" % (release_into, current_release))

    # Reload PHP-FPM gracefully
    run('sudo service php5-fpm reload')



#
#
#
# UNUSED SO FAR
import os
from StringIO import StringIO
from fabric.contrib.files import exists, upload_template
from tempfile import NamedTemporaryFile

last_release_file       = '/home/serial/serialapp.com/LAST_RELEASE'
current_release_file    = '/home/serial/serialapp.com/CURRENT_RELEASE'

# within create_releases
append("%s/%s" % (release_into, 'RELEASE'), next_release)

@task 
def rollback():
    last_release = get_last_release()
    rollback_release(last_release)
    
def get_last_release():
    fd = StringIO()
    get(last_release_file, fd)
    return fd.getvalue()

def rollback_release(to_release):
    release_into = "%s/%s" % (release_dir, to_release)
    run("ln -nfs %s %s" % (release_into, current_release))

# Write current release
append('/home/serialapp.com/current/RELEASE', next_release)

# Finish with upload template nonsense:
http://fabric.readthedocs.org/en/1.3.3/api/contrib/files.html


last_release_tmp = NamedTemporaryFile(delete=False)
last_release_tmp.write('%(release)s', )
last_release_tmp.close()
# The fabric magic
upload_template(last_release_tmp.name, last_release_file, {'release':'12345'},backup=False)
os.remove(last_release_tmp.name)

current_release_tmp = NamedTemporaryFile(delete=False)
current_release_tmp.write('%(release)s', )
current_release_tmp.close()
# The fabric magic
upload_template(current_release_tmp.name, current_release_file, {'release':'12345'},backup=False)
os.remove(current_release_tmp.name)
```
