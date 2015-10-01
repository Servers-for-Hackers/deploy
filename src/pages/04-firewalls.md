# Firewalls

IPtables is a default firewall you'll find in most places. Here we'll discuss how to view, add and delete rules.

## The situation

`sudo iptables -L -v`

```
Chain INPUT (policy ACCEPT 0 packets, 0 bytes)
 pkts bytes target     prot opt in     out     source               destination         

Chain FORWARD (policy ACCEPT 0 packets, 0 bytes)
 pkts bytes target     prot opt in     out     source               destination         

Chain OUTPUT (policy ACCEPT 0 packets, 0 bytes)
 pkts bytes target     prot opt in     out     source               destination   
```

The default policy is `ACCEPT` for all three chains. We see there are no rules in the three chains, so this server is completely open to all traffic.

The three chains here are INPUT, FORWARD, and OUTPUT. Input deals with external network traffic going into the server. Output is exactly the opposite. The forward chain deals with traffic going between networks within the server.

We'll deal mostly with the INPUT chain to protect the server against external traffic headed into the server.

> We can make the default policy to DROP or REJECT traffic. Read more about DROP vs REJECT here: http://serverfault.com/questions/157375/reject-vs-drop-when-using-iptables

## Persistance

Firewall rules won't persist through reboots out of the box. 

However, we can use some basic commands to output the current ruleset as text, and a command that will take that text and apply them as the current ruleset.

```bash
sudo iptables-save
sudo iptables-restore
```

For Example, to save iptables rules:

```bash
sudo iptables-save > ~/rules.v4
```

Later, we can restore those rules:

```bash
sudo iptables-restore < ~/rules.v4
```

### Persisting Rules through Reboots (Debian/Ubuntu)

Install the `iptables-persistent` package to persist firewall rules:

```bash
sudo apt-get install -y iptables-persistent
sudo service iptables-persistent start

sudo service iptables-persistent save
```

<!-- 
### Persisting Rules through Reboots (CentOS/RedHat)

We won't need to install anything to do this on these distributions of Linux.

You can run the following:

```bash
sudo chkconfig iptables on
sudo service iptables save
sudo service iptables start
```

You'll find your IPv4 and IPv6 rule files at `/etc/sysconfig/iptables` and `/etc/sysconfig/ip6tables` respectively.

-->

## Firewall Rule Basics

We're ready to create firewall rules for our server.

First, we'll handle loopback/localhost data. The following will allow data between items on the localhost network (loopback interface):

`sudo iptables -A INPUT -i lo -j ACCEPT`

* Append to INPUT chain
* interface loopback
* jump to ACCEPT target [packets get SENT somewhere]

Second, a more advanced but necessary command which ensures I don't accidentally cut myself out of the server (so it allows my currently established SSH connection):

`sudo iptables -A INPUT -m conntrack --ctstate RELATED,ESTABLISHED -j ACCEPT`

This will allow/keep current related/established rules.

The command does the following:

* Appends (`-A`) to the INPUT chain
* Uses the module (`-m`) conntrack
* Check for state 'related', 'established'
* JUMP to the ACCEPT target

What's this affecting?

Use the command `netstat -a` to see items with the state ESTABLISHED or RELATED. These items will be near the top:

```
Proto Recv-Q Send-Q Local Address           Foreign Address         State      
tcp        0      0 *:ssh                   *:*                     LISTEN     
tcp        0    324 172.30.0.86:ssh         cpe-173-174-200-3:61248 ESTABLISHED
tcp6       0      0 [::]:ssh                [::]:*                  LISTEN     
udp        0      0 *:bootpc                *:*                                
udp        0      0 *:40118                 *:*                                
udp6       0      0 [::]:46591              [::]:*
```

We can see that I have an ESTABLISHED connection to SSH to this server from my local network (my internet connection at home).

## Web Server Firewall Rules

We'll continue on and allow in web and SSH traffic:

```bash
sudo iptables -A INPUT -p tcp --dport 22 -j ACCEPT
sudo iptables -A INPUT -p tcp --dport 80 -j ACCEPT
```

* Append to INPUT chain
* Protocol TCP
* Destination port 22 and 80
* Jump to ACCEPT

Drop everything else

```bash
sudo iptables -A INPUT -j DROP
```

* Append to INPUT chain
* Drop all the things

Now investigate:

```bash
sudo iptables -L -v
```

### HTTPS (Insert over Append)

We need to insert a rule instead of append one to allow other connections, such as https traffic:

```bash
sudo iptables -I INPUT 5 -p tcp --dport 443 -j ACCEPT
```


* Insert to INPUT chain, 5th position
* Protocol TCP
* Destination port 443
* Jump to ACCEPT

## Delete Rules

These are equivalent:

```bash
sudo iptables -D INPUT 3
sudo iptables -D INPUT -p tcp --dport 22 -j ACCEPT
```

### Find Rules by Command

We can delete rules by find the command originally used to create them.

To show all the current rules as valid Iptables command, run: `sudo iptables -S`

Copy one of them, perhaps this one: `-A INPUT -p tcp -m tcp --dport 22 -j ACCEPT`

And then tweak it to delete `-D` rather than append or insert. We'll end up with a command like, which will delete this rule allowing port 22 traffic:

```bash
sudo iptables -D INPUT -p tcp -m tcp --dport 22 -j ACCEPT
```


