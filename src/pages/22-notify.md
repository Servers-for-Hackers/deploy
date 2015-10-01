# Add Notifications

```bash
pip install requests
```

Create `notify.py`:

```python
import requests
import json

def slack(results, error=False):
    slackUrl = 'https://hooks.slack.com/services/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'

    color = 'good'
    msg = 'Success'

    if error:
        color = 'danger'
        msg = 'Error'

    title = 'Deployment %s' % msg

    fields = []

    for host in results:
        fields.append({
            'title': 'host',
            'value': host,
            'short': True
        })

    # Go through each host?

    payload = {
        'username': 'Deploy Bot',
        'icon_emoji': ':cloud:',
        'channel': '#builds',
        'attachments' : [{
            'fallback': title,
            'color': color,
            'title': title,
            'title_link': "http://45.55.209.211",
            'text': 'Deployment Status',
            'fields': fields
        }]
    }

    response = requests.post(slackUrl, data=json.dumps(payload))

    return response
```

Add into the project.

```python
import sys
import json
from datetime import datetime
from StringIO import StringIO

import fabfile
from fabric.api import execute

import boto3

import notify

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

        notify.slack(result, has_errors)
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
