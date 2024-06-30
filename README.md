# Developer Instructions #

## Prerequisites ##

* Composer
* Node
* NPM
* Docker

IDE of your choice
* PHP Storm (preferred)
* VScode

## Setup ##

1. at project root  /
* `npm install` to add all the packages
* `composer update` to add the libraries and dependencies


## Development ##

1. Coding Standards
* Configure your IDE to use PHP Code sniffer  /phpcs.xml   see  [Configure PHP CS](https://github.com/WordPress/WordPress-Coding-Standards#using-phpcs-and-wpcs-from-within-your-ide)
* Configure your IDE to adopt WP coding layouts  ( reference your IDE  e.g. [PHPstorm](https://www.jetbrains.com/help/phpstorm/wordpress-aware-coding-assistance.html) )


2. Develop in the project sub directory this is the plugin development source `/load-html-files`

If you use `wp-env`  ( recommended ) then mapping happens automatically otherwise
your development installation  should map  `/meet-my-team` to `your-local-install/wp-contents/plugins/meet-my-team`
in PHPstorm you can use auto deployment mapping  or otherwise use a symbolic link or some other method such as a local VScode deployment method

3. Local Development

The project is set up so you can use  `wp-env` as a local dev environment if you wish, see quick start https://developer.wordpress.org/block-editor/getting-started/devenv/get-started-with-wp-env/

If you need local configuration of wp-env you can use `.wp-env.override.json`, which is ignored by git.

Running `wp-env start --xdebug` in the project route will start a local dev environment with xdebug enabled on
`localhost:8720` with `/load-html-file` mapped into plugins


## Validation ##

In the project file run `composer check` to check for any issues

Pushing to GitHub  will also run the check automation

## Release manager only tasks ##

### Build for release ###

Ensure versions are updated in root php and readme.txt

run `gulp build` - copies all /load-html-file into /dist and cleans it up removing unneeded dev code etc and creates a zip in /zipped

commit the branch and tag with the release e.g. 5.4.5

merge the version branch with `master`  and push to github

### Release ###

Move the zip to the local wordpress.org directory and run the script update_wp_free_plugin


### Update this README.md ###

Update this file if process changes or can be clarified or extended in anyway