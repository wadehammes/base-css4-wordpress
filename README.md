Base CSS4 for Wordpress (v1)
===

#####USE (install <a href="http://nodejs.org/download/">NPM</a>):
Clone this repo into your code directory and remove the .git file:
```
$ cd your/development/directory/
$ git clone git@github.com:wadehammes/base-css4.git your-project-name
$ rm -rf .git
$ rm .gitignore
$ npm install
```

Run Gulp:
```
$ npm start
```

Your project should compile successfully.

##### In order to optimize your SVG
```
$ cd assets
$ mkdir svg
$ gulp svg
```

##### In order to optimize your images
```
$ cd assets
$ mkdir img
$ gulp images
```

##### In order to update packages:
```
$ npm run-script update
```

#### To fix breaking npm builds
```
$ npm rebuild
```

Launching Wordpress
===
To get up and running, you will need to setup an environment using MAMP, <a href="https://www.mamp.info/en/">download it here</a>.

Once installed, point your MAMP to the <b>site/</b> directory, and then visit it in your browser at:
```
http://localhost:8888/
```

You will then be asked to start setting up your config files. At this point, you should use MAMP to acces your local databases (click the MySQL tab, and launch PHPMyAdmin, and create a new database), and fill out the respective information into the config setup.

Once you are setup and logged in, upgrade Wordpress to the latest version, and then click Appearance -> Themes, and Activate the Base Joints theme. You should now see a bare website at the url above.

Using this to build
===

All theme dev is done in the assets/ directory. You will need to create the SVG and IMG directories (svg/ and img/ respectively). For more information on BASSCSS, see http://basscss.com, and for more info on using CSS4 now, visit the CSS Next website at http://cssnext.io

To include new CSS files outside of the BASSCSS defaults, comment out modules, or add optional modules, please see <code>assets/css/base.css</code>.
