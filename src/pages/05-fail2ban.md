# Advanced

<!-- 
### INPUT Chain Defaults

Instead of defauling to ACCEPT traffic, we can have our INPUT chain default to DROP. This let's us delete the last rule, saying to DROP traffic that doesn't match any of the rules above it.

```bash
sudo iptables -P INPUT DROP
```

Then remove last line of DROP:

```
sudo iptables -D INPUT -j DROP
```

> Be careful not to set your default behavior of your INPUT chain to DROP without first allowing current and/or SSH connections through! Otherwise you will be cut out of your server!

-->

# Fail2Ban

Let's learn more about chains and how they work by installing Fail2Ban.

First, we'll install and configure Fail2ban. This means installing it, and then copying the `jail.conf` file over to `jail.local`.

```
sudo apt-get install -y fail2ban
sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
sudo service fail2ban reload
```

We enable Fail2Ban by creating a `jail.local` file. By default, Fail2Ban is setup to monitor your `/var/log/auth.log` file for failed SSH logins. It will ban a host from accessing the server after 6 failed login attempts.

You can check it to see if there are other rules you'd like to enable, such as monitoring Nginx or Apache for too many failed HTTP basic auth attempts, or go deeper by defining your own filters and ban rules.

To start here, we'll just check for SSH attempts (its default settings).

You can see some actions taken in the fail2ban log:

```bash
sudo tail -f /var/log/fail2ban.log
```

## Fail2Ban and Iptables

Fail2Ban makes use of Iptables to accomplish banning hosts who meet specified threshold.

Check out the iptables after installing and configuring Fail2ban:

```bash
sudo iptables -L -v
```

We'll see something like the following:

```
Chain INPUT (policy ACCEPT 0 packets, 0 bytes)
pkts bytes target prot opt in out source destination
123K 123M Fail2Ban-ssh tcp -- any any anywhere anywhere multiport dports\ ssh
292K 169M ACCEPT tcp -- any any anywhere anywhere tcp dpt:http
... additional omitted ...

Chain FORWARD (policy ACCEPT 0 packets, 0 bytes)
 ... omitted ...

Chain OUTPUT (policy ACCEPT 939K packets, 2332M bytes)
 ... omitted ...

Chain Fail2Ban-ssh (1 references)
 pkts bytes target     prot opt in     out     source     destination
1962K 1498M RETURN     all  --  any    any     anywhere   anywhere
```

We see a new chain `Fail2Ban-ssh`. This chain will be added to in order to block traffic from hosts attempting to log in over SSH.

Essentially all incoming traffic is checked against the new `Fail2Ban-ssh` chain. If a host was banned (for too many incorrect SSH attempts), then there will be a rule in tha new Fail2Ban-ssh chain telling iptables to drop or deny that traffic.

