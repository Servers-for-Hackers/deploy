# Sudoers

We want the `serial` user to be able to restart/reload PHP-FPM. This requires `sudo`, but user `serial` is not a sudo user. Even if that user was, we'd need a sudo password.

Let's fix that.

In the remote server:

```bash
sudo visudo
```

Add the following:

```
serial ALL(ALL:ALL) NOPASSWD:/usr/sbin/service php5-fpm restart,/usr/sbin/service php5-fpm reload
```

Then user `serial` will be able to restart and reload PHP5-FPM, using sudo, without a password. Let's try that out in our `fabfile.py`:

```python
from __future__ import with_statement
from fabric.api import local, env, run, cd
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
        # Reload PHP5-FPM gracefully
        run('sudo service php5-fpm reload')
```



