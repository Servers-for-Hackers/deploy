# SSH Config Files

Typing out `ssh -o "IdentitiesOnly yes" -i ~/.ssh/id_series admin@45.55.209.211` is annoying. Very annoying.

We'll see how to make this easier!

Add the following configuration to your local (your local workstation) user's SSH configuration file:

```bash
# Add/Edit local user's ssh config
vim ~/.ssh/config
```

Add the following to log into our previous created user with the SSH key we created.

```
Host series-admin
    HostName 45.55.209.211
    User admin
    IdentitiesOnly yes
    IdentityFile ~/.ssh/id_series
```

We can use many options there - just about all the ones you see if you use the `man ssh` command.

After that configuration is added, we can now simply log in using the following:

```bash
ssh series-admin
```

## More Users

We can add an additional configuration to log in as our "serial" application user as well!

Make the configuration file look like this:

```
Host series-admin
    HostName 45.55.209.211
    User admin
    IdentitiesOnly yes
    IdentityFile ~/.ssh/id_series

Host series-app
    HostName 45.55.209.211
    User serial
    IdentitiesOnly yes
    IdentityFile ~/.ssh/id_series
```


Now we can quickly use SSH to log in (and copy files!) to our production server without having to re-type all the various options you might need to connect.