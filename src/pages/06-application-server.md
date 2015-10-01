# Setup an Application Server

## Install

Install all the things specific to your application.

As many potential listeners are PHP developers, let's say we have a PHP application.

```bash
# Ensure basics are installed (already done earlier)
sudo apt-get install -y vim curl tmux wget unzip htop

# Add repositories for latest software
sudo apt-get install -y software-properties-common
sudo add-apt-repository -y ppa:nginx/stable
sudo add-apt-repository -y ppa:ondrej/php5-5.6

# Update and install Nginx + PHP
sudo apt-get update

sudo apt-get install -y nginx php5-fpm php5-cli php5-mcrypt php5-curl \
                              php5-mysql php5-pgsql php5-sqlite \
                              php5-gd php5-memcached \
                              mysql-server-5.6

sudo mysql_secure_installation
```

## Configure PHP

Let's configure PHP-FPM to run as our login-user:

Copy file `/etc/php5/fpm/pool.d/www.conf` to `/etc/php5/fpm/pool.d/series.conf`.

Adjust the following items:

```ini
[series] # Instead of [www]

user = serial
group = serial

listen: /var/run/php5-fpm-serial.sock
```

We've setup a separate php-fpm pool for user `serial` to use for it's applications. Let's reload PHP5-FPM to let that take affect:

```bash
sudo service php5-fpm restart
```

## Configure Nginx

Copy default:

```bash
cd /etc/nginx/sites-available
sudo cp default serial
```

Remove default site from "enabled":

```bash
sudo rm /etc/nginx/sites-enabled/default
```

Enable our new one:

```bash
sudo ln -s /etc/nginx/sites-available/serial /etc/nginx/sites-enabled/serial
```

Edit the default to run our new site (the following are changed items):

```nginx
server {
    listen 80 default_server;

    index index.htm index.html index.php;

    root /home/serial/serialapp/current/public;

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php5-fpm-serial.sock;
    }
}
```

Then test and reload our configuration:

```bash
sudo -u serial mkdir ~/serialapp/current/public

sudo service nginx configtest
sudo service nginx reload
```




