# Migrations (Handling other calls during deployment)

Notes:

* Laravel rollback will rollback the last items migrated, so the `rollback` command is relatively safe-ish.
* This flexibility and ability to do rollbacks completely depends on if your application will handle it safely. In general, I run these manually and after testing in a staging environment. Handling data safetly is a "hard problem".

Changes:

```python
@task
def deploy(migrate='no'):
    init()
    update_git()
    create_release()
    build_site()

    # New
    if migrate == 'yes':
        migrate_from = "%s/%s" % (release_dir, next_release)
        migrate_forward(migrate_from)

    swap_symlinks()

@task
def rollback(migrate_back='no'):
    last_release = get_last_release()
    current_release = get_current_release()

    # New
    # Do it first so we rollback using same code base
    #    as when we ran migration
    if migrate_back == 'yes':
        migrate_from = "%s/%s" % (release_dir, current_release)
        migrate_backward(migrate_from)

    rollback_release(last_release)

    write_last_release(current_release)
    write_current_release(last_release)

@task
def migrate():
    migrate_forward()

@task
def migrate_back():
    migrate_backward()

def migrate_forward(release_dir=None, env='production'):
    if not release_dir:
        release_dir = current_release

    with cd(current_release):
        return run('php artisan migrate --env=%s' % env)

def migrate_backward(release_dir=None, env='production'):
    if not release_dir:
        release_dir = current_release

    with cd(current_release):
        return run('php artisan migrate:rollback --env=%s' % env)
```

Final:

```python
from  __future__ import with_statement
import os
from time import time
from StringIO import StringIO
from tempfile import NamedTemporaryFile

from fabric.api import local, env, run, cd, get
from fabric.decorators import task
from fabric.contrib.files import exists, upload_template

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
last_release_file       = '/home/serial/serialapp.com/LAST_RELEASE'
current_release_file    = '/home/serial/serialapp.com/CURRENT_RELEASE'
persist_dir     = '/home/serial/serialapp.com/persist'
next_release    = "%(time).0f" % {'time': time()}

@task
def deploy(migrate='no'):
    init()
    update_git()
    create_release()
    build_site()

    if migrate == 'yes':
        migrate_from = "%s/%s" % (release_dir, next_release)
        migrate_forward(migrate_from)

    swap_symlinks()

@task
def rollback(migrate_back='no'):
    last_release = get_last_release()
    current_release = get_current_release()

    if migrate_back == 'yes':
        migrate_from = "%s/%s" % (release_dir, current_release)
        migrate_backward(migrate_from)

    rollback_release(last_release)

    write_last_release(current_release)
    write_current_release(last_release)

@task
def migrate():
    migrate_forward()

@task
def migrate_back():
    migrate_backward()

def migrate_forward(release_dir=None, env='production'):
    if not release_dir:
        release_dir = current_release

    with cd(current_release):
        return run('php artisan migrate --env=%s' % env)

def migrate_backward(release_dir=None, env='production'):
    if not release_dir:
        release_dir = current_release

    with cd(current_release):
        return run('php artisan migrate:rollback --env=%s' % env)

def get_last_release():
    fd = StringIO()
    get(last_release_file, fd)
    return fd.getvalue()

def get_current_release():
    fd = StringIO()
    get(current_release_file, fd)
    return fd.getvalue()

def write_last_release(last_release):
    last_release_tmp = NamedTemporaryFile(delete=False)
    last_release_tmp.write('%(release)s')
    last_release_tmp.close()

    upload_template(last_release_tmp.name, last_release_file, {'release':last_release}, backup=False)
    os.remove(last_release_tmp.name)

def write_current_release(current_release):
    current_release_tmp = NamedTemporaryFile(delete=False)
    current_release_tmp.write('%(release)s')
    current_release_tmp.close()

    upload_template(current_release_tmp.name, current_release_file, {'release':current_release}, backup=False)
    os.remove(current_release_tmp.name)

def rollback_release(to_release):
    release_into = "%s/%s" % (release_dir, to_release)
    run("ln -nfs %s %s" % (release_into, current_release))
    run('sudo service php5-fpm reload')

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

    # Put current_release as last release
    write_last_release(get_current_release())

    # Put new current_release
    write_current_release(next_release)


    # Reload PHP-FPM gracefully
    run('sudo service php5-fpm reload')
```

Usage:

```bash
fab -l

fab deploy:migrate=yes
fab rollback:migate_back=yes

fab migrate
fab migrate_back
```