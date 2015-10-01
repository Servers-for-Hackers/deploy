# Deploy with Built Site

## Node

Node listener needs to pass in the sha of the commit:

> Don't do this, because we want to keep it telling it to push master.

<!--
```javascript
gitHubHandler.on('push', function (event)
{
    console.log(
        'Received a push event for %s to %s, sha: %s',
        event.payload.repository.name,
        event.payload.ref,
        event.payload.after
    )

    // Test what our reference actually is!
    if( event.payload.ref == 'refs/heads/master' )
    {
        queue_deploy(event.payload.after);
    }
});

function queue_deploy(sha)
{
    var params = {
        MessageBody: JSON.stringify({
            time: Math.floor(new Date() / 1000),
            sha: sha
        }),
        QueueUrl: process.env.QUEUE_URL
    };

    sqs.sendMessage(params, function(err, data)
    {
        if (err)
        {
            console.log(err, err.stack);
        } else
        {
            console.log('Job Sent', data);
        }
    });
}
```
-->

## Python

Python needs to upload the .zip file and unzip/swap symlinks. No more building or using git on the production server:

```python
from  __future__ import with_statement
import os
from time import time
from StringIO import StringIO
from tempfile import NamedTemporaryFile

from fabric.api import local, env, run, cd, get, lcd, put
from fabric.decorators import task
from fabric.contrib.files import exists, upload_template

env.use_ssh_config = True
env.hosts = [
'serial-app-1',
]

release_dir     = '/home/appuser/serialapp/releases'
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

    local("mkdir -p ~/build-server/packages")
    local("mkdir -p ~/build-server/releases")

    with lcd("~/build-server/repo"):
        local("git checkout master")
        local("git pull origin master")

        release_sha = local("git rev-parse HEAD", capture=True)

        local("mkdir -p ~/build-server/releases/%s" % release_sha)
        local("git archive --worktree-attributes master | tar -x -C ~/build-server/releases/%s" % release_sha)

    with lcd("~/build-server/releases/%s" % release_sha):

        local("npm install")
        local("node ./node_modules/.bin/gulp")
        local("composer install")

        local("cd ~/build-server/releases")
        local("zip -r --exclude=\"*.git*\" --exclude=\"*node_modules*\" ~/build-server/packages/%s.zip ./*" % release_sha)

    return release_sha

def upload_site(sha):
    remote_release_dir = "%s/%s" % (release_dir, sha)
    local_release_file = "/home/appuser/build-server/packages/%s.zip" % sha

    run("mkdir -p %s" % remote_release_dir)
    put(local_path=local_release_file, remote_path=remote_release_dir)

    with cd(remote_release_dir):
        # Unzip and remove zip file
        run("unzip -o %s.zip" % sha)
        run("rm -r %s.zip" % sha)

        # Delete Storage dir
        run("rm -rf storage")

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

## Why?

This is not necessarily more useful for CI, as in CI, you usually pull in dependencies and their tests to run unit tests

Then, if tests pass, you build your app with the least number of dependencies (e.g. we ignore node_modules, .git dir, etc)

### OK then, what?

I've seen this method used when you want to store version/hashes for:

1. Separating building the site from deployment, perhaps if their's a delay due to QA or needing to build a test environment
2. Storing application builds for later usage (easier roll-backs, or for integration with more advanced deployment strategies, such as container-based services or AWS Beanstalk).





