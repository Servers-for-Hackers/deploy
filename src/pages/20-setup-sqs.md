# Setup SQS in Amazon to do Queues

1. Login into AWS
2. Create queue. Note URL and ARN
3. Create IAM role/user
4. Assign read/write privileges to that user

arn:aws:sqs:us-east-1:308352032554:serial-deploy

```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Sid": "Stmt1425442063001",
            "Effect": "Allow",
            "Action": [
                "sqs:ChangeMessageVisibility",
                "sqs:DeleteMessage",
                "sqs:GetQueueAttributes",
                "sqs:GetQueueUrl",
                "sqs:ReceiveMessage",
                "sqs:SendMessage"
            ],
            "Resource": [
                "arn:aws:sqs:us-east-1:308352032554:serial-deploy"
            ]
        }
    ]
}
```

NodeJS to `send` to this queue

```bash
npm install --save aws-sdk

vim ~/.aws/credentials
```

Inside of `~/.aws/config`

```ini
[default]
region=us-east-1
output=json
```

Inside of `~/.aws/credentials`

```ini
[default]
aws_access_key_id = your_access_key
aws_secret_access_key = your_secret_key
region = us-east-1
```


Then we'll take the same code and just throw that into a queue.



```javascript
var express = require('express');
var app = express();

var createHandler = require('github-webhook-handler')
var gitHubHandler = createHandler({ path: '/webhook', secret: 'myhashsecret' });

var AWS = require('aws-sdk');
AWS.config.update({region: 'us-west-1'}); // Must be first
var sqs = new AWS.SQS();

/**
 * Use middleware provided by github-webhook-handler
 * @link https://github.com/rvagg/github-webhook-handler
 * @link http://expressjs.com/guide/using-middleware.html
 */
app.post('/webhook', gitHubHandler, function (req, res)
{
    // If we made it this far
    res.statusCode = 404;
    res.end('no such location');
});

/**
 * A custom webhook for ourselves to call externally
 *   Perhaps from Slack or another chatroom
 *   Or from a CI server
 */
app.post('/deploy', function (req, res)
{
    queue_deploy();

    res.send('Deploying');
});

/**
 * Make ExpressJS application listen
 */
var server = app.listen(8080, '0.0.0.0', function ()
{
  var host = server.address().address;
  var port = server.address().port;

  console.log('Deployer listening at http://%s:%s', host, port);
});

/**
 * Handle Github Webhook events
 */
gitHubHandler.on('error', function (err)
{
  console.error('Github Webhook Error:', err.message)
});

gitHubHandler.on('push', function (event)
{
    console.log(
        'Received a push event for %s to %s',
        event.payload.repository.name,
        event.payload.ref
    )

    // Test what our reference actually is!
    if( event.payload.ref == 'refs/heads/master' )
    {
        queue_deploy();
    }
});

/**
 * Do the actual application deploment
 * @return null
 */
function queue_deploy(ref)
{
    if( typeof ref == "undefined" )
    {
        ref = 'master';
    }

    var params = {
        MessageBody: JSON.stringify({
            time: Math.floor(new Date() / 1000),
            reference: ref
        }),
        QueueUrl: 'https://...' // Load from config
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

Watch in SQS as a new job appears in the queue
