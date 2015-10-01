# Deploying Sites with SCP

SCP is "secure copy". It works just like the "cp" command on your server, except it works for copying files between servers (in both directions).

```bash
# Local "copy" example
cp src/file dest/file

# Use "scp" to copy files remotely, recursively
scp -r /some/local/directory  user@ip-address:/path/to/remote/location
```

This is the simplest form of deployment since it's just moving files.

SCP works over SSH, so we can re-use our SSH knowledge to log in as well. Let's deploy a simple set of HTML files for an "application":

```bash
cd ~/Sites/serialapp
scp ./* serial@45.55.209.211:~/serialapp.com/current/public/
```

That didn't work, but we recognize the error (too many login attempts).

We can re-use our SSH options to set the identity key and other options:

```bash
# Use our ssh key
scp -o "IdentitiesOnly yes" \
    -i ~/.ssh/id_series \
    ./* serial@45.55.209.211:~/serialapp.com/current/public/
```

SCP conveniently uses the same options as the SSH command.

That's basically it. 

Since we created an aliases in our `~/.ssh/config` file, we can make this even simpler by using the `serial-app` host:

```bash
scp ./* serial-app:~/serialapp.com/current/public/
```





