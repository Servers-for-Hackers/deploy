# SSH Access

You have a brand new server - naturally you want to log in.

## Basic Mechanics

```bash
ssh root@104.236.90.57
```

We get root access (or perhaps a specific user). Let's try to login with the above. We get an error. Let's find out what's up:

```bash
ssh -vvvv root@104.236.90.57
```

It's trying to use all the various keys I happen to have. I have a lot, so it's going over the keys and reaching the max allowed attempts.

Let's tell it to NOT use public key authentication, which will let it fallback to password based authentication (currently allowed by the remote server).

```bash
# Don't use public key authentication
ssh -o "PubkeyAuthentication no" root@104.236.90.57
```

So we learned how to add options. We can see lots of options available when using `man ssh`.

## SSH Key Access

SSH access is more secure than using a password. It potentially lets us connect without a password (although I don't necessary suggest it). In either case, password-based auth sends the password to the remote server. SSH key access, even when there's a password used with the ssh keypair, does not.

SSH key access is also a way to give access to one or more servers with the same set of credentials.

Let's create an SSH key on our local computer, which we'll use to connect to this server and any other in the series.

```bash
cd ~/.ssh
ssh-keygen -t rsa -b 4096 -C "chris@serversforhackers.com" -f id_series
``` 

Create a password!

Next let's copy the *public* key to the remote server, in it's `authorized_keys` file. This authorizes our public/private key pair to be used to login for the remote server user's `authorized_key` file.

```bash
# On mac:
brew install ssh-copy-id

# Then do it
ssh-copy-id -o "PubkeyAuthentication no" \
    -i ~/.ssh/id_rsa.pub root@104.236.90.57
```

Now, in theory, we can login using our key! Because I have a lot of keys, I'll specify the key I want:

```bash
ssh -i ~/.ssh/id_series root@104.236.90.57
```

They, that didn't work! Same error as when using no key! Turns out you need to also tell it both to use a specific identity ... and nothign else. Sigh.

```bash
ssh -o "IdentitiesOnly yes" \
    -i ~/.ssh/id_series root@104.236.90.57
```

And then we're in!

## What we covered:

1. Logging in via password, despite ssh trying to use key pairs
2. Creating an SSH key pair
3. Ensuring we can log into the remote server via SSH key
4. Logging in via SSH key when you have many ssh keys






