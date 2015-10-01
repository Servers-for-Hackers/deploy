# Expand on it:

> Create a github webhook right before or after this!

```bash
npm install --save github-webhook-handler
```

Then:

```javascript
var express = require('express');
var app = express();
var exec = require('child_process').exec;
var createHandler = require('github-webhook-handler')
var gitHubHandler = createHandler({ path: '/webhook', secret: 'myhashsecret' });

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
app.post('/deploy', function (req, res) {
    var deployment = deploy_app();

    deployment.on('exit', function(code, signal)
    {
        if( code > 0 )
        {
            console.log('Deployment ended in error', deployment.stderr)
        } else {
            console.log('Deployment succeeded')
        }
    });

    res.send('Deploying');
});

/**
 * Make ExpressJS application listen
 */
var server = app.listen(8080, '0.0.0.0', function () {
  var host = server.address().address;
  var port = server.address().port;

  console.log('Deployer listening at http://%s:%s', host, port);
});

/**
 * Handle Github Webhook events
 */
gitHubHandler.on('error', function (err) {
  console.error('Github Webhook Error:', err.message)
});

gitHubHandler.on('push', function (event) {
    console.log(
        'Received a push event for %s to %s',
        event.payload.repository.name,
        event.payload.ref
    )

    // Test what our reference actually is!
    if( event.payload.ref == 'refs/heads/master' )
    {
        deploy_app();
    }
});

/**
 * Do the actual application deploment
 * @return null
 */
function deploy_app()
{
    var options = {
        cwd: '/home/deployer/build-server',
        shell: '/bin/bash',
        maxBuffer: 1048576 // One MB
        // uid: //deployer
        // gid: // deployer
    };

    var childProcess = exec(
        'source .venv/bin/activate && python deploy.py',
        options,
        function(error, stdout, stderr)
        {
            // Possibly log stdout/stderr
            // Do error checking
            console.log(error, stdout, stderr)
        });

    return childProcess;
}
```

Iptables:

```bash
sudo iptables -I INPUT 5 -p tcp --dport 8080 -j ACCEPT
```

---

<!--
```javascript
var express = require('express');
var app = express();

var createHandler = require('github-webhook-handler')
var gitHubHandler = createHandler({ path: '/webhook', secret: 'myhashsecret' });

var Deployment = require('./lib/deployment.js');

/**
 * Use middleware provided by github-webhook-handler
 * @link https://github.com/rvagg/github-webhook-handler
 * @link http://expressjs.com/guide/using-middleware.html
 */
app.use(function (req, res, next) {
    gitHubHandler(req, res, function (err) {
        if( req.url == '/deploy' )
        {
            return next();
        }
        res.statusCode = 404
        res.end('no such location')
    });
    next();
});

/**
 * A custom webhook for ourselves to call externally
 *   Perhaps from Slack or another chatroom
 *   Or from a CI server
 */
app.post('/deploy', function (req, res) {
    var deployment = deploy_app();

    deployment.on('exit', function(code, signal)
    {
        if( code > 0 )
        {
            console.log('Deployment ended in error', deployment.stderr)
        } else {
            console.log('Deployment succeeded')
        }
    });

    res.send('Deploying');
});

/**
 * Make ExpressJS application listen
 */
var server = app.listen(8080, function () {
  var host = server.address().address;
  var port = server.address().port;

  console.log('Deployer listening at http://%s:%s', host, port);
});

/**
 * Handle Github Webhook events
 */
gitHubHandler.on('error', function (err) {
  console.error('Github Webhook Error:', err.message)
})

gitHubHandler.on('push', function (event) {
    console.log(
        'Received a push event for %s to %s',
        event.payload.repository.name,
        event.payload.ref
    )

    // Test what our reference actually is!
    if( event.payload.ref == 'master' )
    {
        deploy_app();
    }
})

/**
 * Do the actual application deploment
 * @return null
 */
function deploy_app()
{
    var deployment = new Deployment();

    deployment.child = childProcess;

    return deployment;
}
```

Track a deployment:

```javascript
var EventEmitter = require('events').EventEmitter;
var exec = require('child_process').exec;

function Deployment()
{
    EventEmitter.call(this)

    this.time   = new Date();
    this.error  = false;
    this.stdout = false;
    this.stderr = false;
    this.child  = false;
    this.signal = false;
    this.code   = false;
}

Deployment.prototype = Object.create(EventEmitter.prototype);

Deployment.prototype.child = function(child)
{
    this.child = child;
}

Deployment.prototype.run = function()
{
    var _this = this;

    var options = {
        cwd:       '/home/deployer/build-server',
        shell:     '/bin/bash',
        maxBuffer: 1048576 // One MB
        // uid: //deployer
        // gid: // deployer
    };

    var childProcess = exec(
        'source .venv/bin/activate && python deploy.py',
        options,
        function(error, stdout, stderr)
        {
            _this.output(error, stdout, stderr);
        });

    childProcess.on('exit', function(code, signal)
    {
        _this.exit(code, signal);
    });

    return _this;
}

Deployment.prototype.output = function(error, stdout, stderr)
{
    this.error = error;
    this.stdout = stdout;
    this.stderr = stderr;

    this.emit('output', error, stdout, stderr);
}

Deployment.prototype.exit = function(code, signal)
{
    this.code = code;
    this.signal = signal;

    this.emit('exit', code, signal);
}

module.exports = Deployment;
```
-->