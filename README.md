# PHP-template-runner

Generic PHP application for running a Tygron Template Project in an arbitrary location.

## Requirements
The application is written in PHP. A webserver, docker container, or other environment, should meet the following requiremts:
* Access via domainname, either on local network or via internet
* Access to the internet to connect to the Tygron Platform
* PHP 8.2 or newer.
  * Fileinfo extention
  * Curl extention
* Read-write access to a directory (preferably outside the web root) for managing running data
* Cron, TaskScheduler, or other periodic mechanism for activating tasks


## Deployment

### Deploy using docker-compose (recommended):
```cp sample-docker-compose-override.yaml docker-compose-override.yaml```

in docker-compose-override.yaml, replace "port:80" with the desired port-mapping. For example, 9000:80 will result in the container listening for incoming calls on port 9000.

```sh docker-run.sh```

### Deploy manually:
- Ensure the contents of "app" are in the folder, where the application is to be deployed.
- Ensure the contents of "cron" are in a folder, preferably outside the webroot.
- Ensure a "workspace" folder exists, preferably outside the webroot, where the web application has write-permissions.

- In .../cron/cron.php, ensure the $documentroot variable refers to the folder which contains the contents of "app".
- In .../cron/cron.sh, ensure /var/cron/ is changed to the folder which contains the contents of "cron".
- In .../cron/cron.sh, ensure /var/workspace is changed to the folder which is designated to be the workspace.
- In clean_workspace.sh, ensure workspace is changed to the folder which is designated to be the workspace.

- Ensure a cronjob is set to run .../cron/cron.sh, every 1 minute if possible. (See scripts/docker-start for an example)
