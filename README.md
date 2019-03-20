# Base CSS4 Wordpress Starter

## USE -- install [NPM](http://nodejs.org/download/):

Clone this repo into your code directory and remove the .git file(s):

```
$ cd your/development/directory/
$ git clone git@github.com:TrackMaven/base-css4-wordpress.git
$ cd base-css4-wordpress
$ rm .git
$ rm .gitignore
$ npm install
```

Run Gulp:

```
$ gulp
```

## Launching Wordpress

Currently, we run everything in a MAMP environment on OSX. For other computer types, skip this step.

### Install Mamp

To get up and running, you will need to setup an environment using MAMP, [download it here](https://www.mamp.info/en/).

Once installed, point your MAMP to the repo, and then visit it in your browser at:

```
http://localhost:8888/
```

You will also need to create a `settings.json` file in the root of your repository, with the following information:

```
{
  "devUrl": "localhost:8888"
}
```

This allows our browser-sync task to properly proxy so we can use MAMP plus the goodness of Browser-Sync.

**NOTE: if you plan to create a custom hostname for this in MAMP, like `mysite.local:8888`, then you need to update this file to reflect that change**

You will then be asked to start setting up your config files. At this point, you should use MAMP to acces your local databases (click the MySQL tab, and launch PHPMyAdmin, and create a new database), and fill out the respective information into the config setup.

Once you are setup and logged in, upgrade Wordpress to the latest version, and then click Appearance -> Themes, and Activate the Base Joints theme. You should now see a bare website at the url above.

### Add your local URL to wp-config.php

Open `wp-config.php` and add to line 22:

```
define('WP_SITEURL', '<your local URL here>');
define('WP_HOME', '<your local URL here>');
```

### Add other wp-config goodies

Add to line 44:

```
/** Allocate more memory */
define('WP_MEMORY_LIMIT', '128M');

/** Define permissions */
define('FS_CHMOD_DIR', (0755 & ~ umask()));
define('FS_CHMOD_FILE', (0644 & ~ umask()));

/** Clear trash */
define('EMPTY_TRASH_DAYS', 60);

define('DISABLE_WP_CRON', 'true');
```

## Using this to build

All theme dev is done in the `assets/` directory within `base` theme. For more information on BASSCSS (our primary framework), see http://basscss.com, and for more info on using CSS4 now, visit the CSS Next website at http://cssnext.io.

To include new CSS files outside of the BASSCSS defaults, comment out modules, or add optional modules, please see `assets/css/base.css`.

## Deploying

We use Fabric to deploy. You will need to have your public SSH key set up in AWS, work with Wade or an engineer to get that completed.

In the `fabfile.py`, add your environment hosts:

```python
@task
def dev():
    """ Use development server settings """
    _set_host('dev')
    env.hosts = ['user@ip:22']

@task
def prod():
    """ Use production server settings """
    print('******************************')
    print('********   WARNING    ********')
    print('********  PRODUCTION  ********')
    print('******************************')
    _set_host('production')
    env.hosts = ['user@ip:22']
```

First, install `pip`:

```
sudo easy_install pip
```

Next, install Fabric:

```
pip install fabric
```

### Fabric Commands

Deploy():

```
fab <environment> deploy:<branch name>
```

Using this command, you will first pass an envirment to deploy to, either `dev` or `prod`. You will then supply a branch.

_Note:_ production only takes `master` as a branch.

When you hit enter, it will ask you to confirm the enviroment by typing the name of it in the raw input. Hit enter again and your deploy will run.

Status():

```
fab <environment> status
```

Use this to get a snapshot of current state of remote env you pass
