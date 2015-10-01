# Python

Used to be:

```bash
source .venv/bin/activate

pip install boto3
```

Now can be (in `deploy.py`):

```python
import sys
import json
from datetime import datetime
from StringIO import StringIO

import fabfile
from fabric.api import execute

import boto3

sqs = boto3.resource('sqs')
deploy_queue = sqs.get_queue_by_name(QueueName='serial-deploy')

def run_deploy(message):
    try:
        iomsg = StringIO(message.body)
        job_data = json.load(iomsg)

        # job_data['job']['time']
        # job_data['job']['reference']

        # We're not using job_data, yet
        result = execute(fabfile.deploy)

        # Check if successful
        has_errors = False
        for host in result:
            if result[host]:
                has_errors = True
                print "Error: %s" % result[host]

        if not has_errors:
            message.delete()
    except Exception as e:
        print "Unexpected error:", sys.exc_info()[0]

while True:
    messages = deploy_queue.receive_messages(
        MaxNumberOfMessages=1,  # Single message at a time
        VisibilityTimeout=60,   # 60 seconds
        WaitTimeSeconds=10      # Wait for 10 seconds for next job
    )

    if len(messages) > 0:
        print "Starting deployment at %s" % datetime.strftime(datetime.now(), '%Y-%m-%d %H:%M:%S')
        run_deploy(messages[0])
```