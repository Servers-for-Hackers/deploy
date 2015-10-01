# Rsync

Before we used `scp` to do a secure copy. Now let's try something a little more sophisticated: `rsync`.

Rsync will only copy changed files to remote servers. It uses SSH as well.

Let's change our script to use Rsync instead of `scp`:

```bash
#! /usr/bin/env bash

rsync -vzcrSLh --exclude="publish.sh" --exclude=".git" \
    ./ serial-app:~/serialapp.com/current/public

# The options:
# v - verbose
# z - compress data
# c - checksum, use checksum to find file differences
# r - recursive
# S - handle sparse files efficiently
# L - follow links to copy actual files
# h - show numbers in human-readable format
# --exclude - Exclude files from being uploaded. You can use multiple --exclude flags

# Or, run the command as a dry run, so no files are sent and you can preview the changes:
rsync -vzcrSLh --exclude="publish.sh" --exclude=".git" \
    --dry-run \
    ./ series-app:~/serialapp.com/current/public
```

This can save bandwidth and speed up deployments. Rsync works both ways as well.

If you need or want to pass SSH options, you can use the `-e` flag along with what looks like a "normal" ssh command:

```bash
rsync -vzcrSLh \
       -e "ssh -i /home/fideloper/.ssh/id_series -o IdentitiesOnly yes" \
       --exclude="publish.sh" \
       --exclude=".git" \
       ./ serial-app:~/serialapp.com/current/public
```

