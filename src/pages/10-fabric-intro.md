# Fabric Basics

Install pip, and then either install fabric globally or within a virtualenv.

```bash
# Install pip
sudo easy_install pip
# Update pip
sudo pip install -U pip
# Install virtualenv
sudo pip install virtualenv

# Install `fabric` globally
sudo pip install fabric

# or install in a virtualenv
virtualenv .venv
source .venv/bin/activate
pip install fabric
```

Then create the `fabfile.py` in the project root:

```python
from __future__ import with_statement
from fabric.api import local, env, settings, abort, run, cd
from fabric.decorators import task

env.use_ssh_config = True
env.hosts = ['serial-app']
# env.hosts = ['45.55.209.211']
# env.user = 'serial'
# env.key_filename = '~/.ssh/id_series'

@task
def deploy():
    # Change into repository directory
    with cd('/home/serial/serialapp.com/current/repo'):
        # Update git repository
        # assuming we got into the directory
        run('git pull origin master')
```

Push up some changes to git and then run `fab deploy` to run the `deploy` task.
