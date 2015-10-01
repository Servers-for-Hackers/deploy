* Create build server
* Setup admin user
* Setup deploy user
* Create local ssh keypair (passwordless)
* ssh-copy-id to build server
* Manually copy private key to build server "deployer" user
* Don't bother showing security, etc, setup, just do it and mention it

## Create Users

```bash
sudo adduser admin
sudo adduser deployer

usermod -a -G sudo admin
groups admin
```

## Install Some Basics

```bash
sudo apt-get update
sudo apt-get install -y vim curl tmux wget unzip htop
```

## Check out SSHD Config on server:

```
sudo vim /etc/ssh/sshd_config
```

Highlights:

```
PermitRootLogin without-password  # PW auth disabled, key-only
PasswordAuthentication no         # Key-only in general
```

(Read about them usig `man sshd_config`)


## Turn off Password based login

`sudo vim /etc/ssh/sshd_config`. We'll check on a few things.

```
PermitRootLogin no
PasswordAuthentication no
```

## Firewalls


```bash
sudo apt-get install -y iptables-persistent
sudo service iptables-persistent start

sudo iptables -A INPUT -i lo -j ACCEPT
sudo iptables -A INPUT -m conntrack --ctstate RELATED,ESTABLISHED -j ACCEPT
sudo iptables -A INPUT -p tcp --dport 22 -j ACCEPT
sudo iptables -A INPUT -p tcp --dport 80 -j ACCEPT
sudo iptables -A INPUT -p tcp --dport 443 -j ACCEPT
sudo iptables -A INPUT -j DROP

sudo service iptables-persistent save
```

## Software

```bash
# Python
sudo apt-get install -y python-pip python-dev build-essential
sudo pip install -U pip
sudo pip install virtualenv

# NodeJS
curl --silent --location https://deb.nodesource.com/setup_0.12 | sudo bash -
sudo apt-get install -y nodejs
```

ALSO generated a new SSH key and copied that over to our web server(s), so we could connect using Fabric to the remote servers.

```bash
ssh-keygen -t rsa -b 4096 -C "chris@serialapp.com" -f id_serialdeploy
# Then copy the pub key to `authorized_keys` file on web application server for user `serial`
```

This also invovles creating/copying `fabfile.py` and `deploy.py` as a means to start automating deployments from this build server.

That's just copy and paste `fabfily.py` and `deploy.py` into dir `/home/deployer/build-server`.