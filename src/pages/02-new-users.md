# Creating/Modifying Users

We've logged into the new server. We'll can now setup this server to be run as a web server. First, we'll create some users.

## Login

By defualt, we only get the root user to log in as:

```bash
ssh -o "IdentitiesOnly yes" -i ~/.ssh/fideloper02.pem ubuntu@52.1.221.221
```

## Some Basics

I'm going to use the following tools often, so let's just install some utilities now:

```bash
sudo apt-get install -y tmux vim curl wget unzip htop
```

## New User

Now if we did't want user root (or Ubuntu, if we're using the Ubuntu image on AWS), we can add a new admin user:

```bash
sudo adduser admin
sudo adduser serial
```

Then:

```bash
cat /etc/passwd | grep admin
cat /etc/passwd | grep serial
```

User is admin, uid/gid is 1001 (anything over 1000 is a non-system user). The user's home directory exists (`/home/admin`) and the user's shell (the shell used when they log in) is defined as `/bin/bash`:

`admin:x:1001:1001:,,,:/home/admin:/bin/bash`

We want the admin user to be able to run sudo commands. Let's add that user into the `sudo` group:

```bash
usermod -a -G sudo admin

# Confirm user is in admin group
groups admin
```

## Local SSH Key to new users

Copy public key to both new user's `authorized_keys` directory using `ssh-copy-id`. Note that we created `id_series` in the previous video.

```bash
ssh-copy-id -i ~/.ssh/id_series -o "PubkeyAuthentication no"
    admin@45.55.209.211

ssh-copy-id -i ~/.ssh/id_series -o "PubkeyAuthentication no"
    serial@45.55.209.211
```

SSH in from local to test it works.

> Do this carefully, so as not to get locked out! Keep a connection open in another tab in your terminal.
