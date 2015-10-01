New file to call our script. This will demonstrate that we can call our Fabric deployment script by other scripts. In other words, we can automate deployment.

## Execute Script

File `deploy.py`:

```python
import fabfile
from fabric.api import execute

execute(fabfile.deploy)
```

This assumes `fabfile.py` is present in the same directory.