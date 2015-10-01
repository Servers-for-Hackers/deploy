Need notes on .gitignore changes and moving static asset building to build server

## Application

Update `gitignore`:

We're ignoring our production build files!

```
public/css/*
public/js/*
```

Update `gulpfile.js`:

```javascript
// Disable Gulp Notify
process.env.DISABLE_NOTIFIER = true;
var elixir = require('laravel-elixir');

elixir(function(mix) {
     mix.sass('app.scss');

     mix.scripts([
        "app.js"
    ]);
}
```


To run:

```bash
cd ~/build-server/serialapp/repo
./node_modules/.bin/gulp --production
```