# SSH Security

Now that we've created users and added the ability to connect to them via SSH keys, we can setup some security around server access.

We'll change some configuration of how SSH allows itself to be connected to on our web server.

## Log In

First, if you are not logged into your server, we can do so using our ssh keys:

```bash
ssh -o "IdentitiesOnly yes" -i ~/.ssh/id_series root@45.55.209.211
```

We're logged in as user root (for now), so we can make the following changes easily.

## Check out SSHD Config on server:

We want to  
```
sudo vim /etc/ssh/sshd_config
```

Some interesting items you may see:

```
PermitRootLogin without-password  # PW auth disabled, key-only
PasswordAuthentication no         # Key-only in general
```

(Read more about these available options using `man sshd_config`)

## Turn off Root User login:

We don't want the root user to be able to login over SSH. Instead, we want to enforce the use of the admin user to log in, as they need a user password to run sudo commands. This provides an extra layer of security that simply logging in as the root user does not provide.

```
PermitRootLogin no
DenyUsers ubuntu # if applicable, for example on AWS's Ubuntu image
```

## Turn off Password-based login

Next we'll disallow logging in using a password altogether. Users will only be able to login in using the SSH keys we've created.

`sudo vim /etc/ssh/sshd_config`. We'll check on a few things.

```
PasswordAuthentication no
```

## Restart SSH

Let's save these changes by saving our changes to the configuraiton and then restarting the network service.

```bash
sudo service ssh restart
``` 








