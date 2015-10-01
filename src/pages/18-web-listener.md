## Create a web listener to demonstrate async nature

```javascript
var exec = require('child_process').exec,
    child;

console.log('this is happening first')

child = exec('sleep 3', function(error, stdout, stderr)
{
    console.log('stdout:', stdout, 'stderr:', stderr)

    if( error )
    {
        console.log('error', error)
    }
});

console.log('this is not actually happening last, thanks to async nature')
```

Docs so we can add in buffer size limit, bash over sh, and `source` first: https://nodejs.org/api/child_process.html#child_process_child_process_exec_command_options_callback

## Install Python Fabric onto Build Server

Install `/home/deployer/build-server/.venv` via virtualenv, then install `fabric`.

Copy latest `fabfile.py` so we can use it via `deploy.py` call (Don't just call "fab" command. While we can, using `deploy.py` opens us up to later possibilities, esp if we don't want to use Node.js for the web site).

## Actually do web listening

Express + this middleware: https://github.com/rvagg/github-webhook-handler
And your own POST hook for automating a deployment

```bash
cd /home/deployer/build-server

cd ~/path/to/server
virtualenv .venv
source .venv/bin/activate
pip install fabric


npm init
npm install express --save
```

Create a Github webhook to our server, and supply a secret.

First app:

```javascript
var express = require('express');
var app = express();

var exec = require('child_process').exec;

/**
 * A custom webhook for ourselves to call externally
 *   Perhaps from Slack or another chatroom
 *   Or from a CI server
 */
app.post('/deploy', function (req, res) {
    deploy_app()
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

Then:

```bash
curl -X POST localhost:8080/deploy
```



