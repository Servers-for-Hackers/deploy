# Upstart for NodeJS and Python

## Node Web Listener:

Change Node `builder.js` to use Environmental variables:

```javascript
/* near top: */
var gitHubHandler = createHandler({ path: '/webhook', secret: process.env.GITHUB_SECRET });

/* and later: */
QueueUrl: process.env.QUEUE_URL
```

As admin/root, create file `/etc/init/deploy-node.conf` with the following:

```
description "NodeJS Web Listener"

start on filesystem or runlevel [2345]
stop on runlevel [!2345]

setuid appuser
setgid appuser

env GITHUB_SECRET=bb4610181a2c95fe695db0a5d29ac3098719d39c
env QUEUE_URL=https://sqs.us-east-1.amazonaws.com/308352032554/serial-deploy

respawn
respawn limit 5 2

chdir /home/appuser/build-server

script
    # Start Listener
    /usr/bin/node builder.js
end script
```

Then we can start and check it's status:

```bash
# Ensure it's read
sudo service deploy-node status

# Start it and check status, ensure PID doesn't change
# signifying it's failing and getting restarted
sudo service deploy-node start
sudo service deploy-node status
```


## Python Queue Listener

Doesn't really need environment variables, except perhaps for the queue name if you want. If you want, set an `env`, perhaps to `serial-deploy`, in the Upstart configuration and then:

```python
import os

os.environ['QUEUE_NAME'] // 'serial-deploy'
```

Let's skip over that and head to our Upstart configuration. Create file `/etc/init/deploy-py.conf` with the following:

```
description "Python Queue Listener"

start on filesystem or runlevel [2345]
stop on runlevel [!2345]

setuid appuser
setgid appuser

respawn
respawn limit 5 2

chdir /home/appuser/build-server

pre-start script
    # Source Python Virtualenv
    . .venv/bin/activate
end script

script
    # Start Python using full or relative
    # path to the python script
    /home/appuser/build-server/.venv/bin/python deploy.py 
end script
```

Then we can start and check it's status:

```bash
# Ensure it's read
sudo service deploy-py status

# Start it and check status, ensure PID doesn't change
# signifying it's failing and getting restarted
sudo service deploy-py start
sudo service deploy-py status
```

## Error Checking

If you see the PID change, it's a sign the script is failing and upstart is trying to bring it back online. It will eventually fail. You can check for output (stdout and stderr) in `/var/log/upstart/*.log`, where the `*` is the name of your service. In this case we have to, `deploy-node.log` and `deploy-py.log`.



