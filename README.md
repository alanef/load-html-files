# Developer Instructions #

## Prerequisites ##
You will need the following tools:

* Composer
* Node
* NPM
* Docker
* Git
* GitHub account
* An IDE of your choice, such as:
    * PHP Storm (preferred)
    * VSCode

## Source Control ##
We follow the GitHub Flow. Read more about it [here](https://docs.github.com/en/get-started/using-github/github-flow)

* External developers should fork and clone the repository, then create a branch.
* Project team developers should create a branch named with the issue tracker ID and description.

## Setup ##
At project root `/`, execute the following commands:

* `npm install` to install all the packages
* `composer update` to install the libraries and dependencies

## Development ##
1. Coding Standards:

    * Configure your IDE to use PHP Code Sniffer with the `/phpcs.xml` file. Follow the instructions [here](https://github.com/WordPress/WordPress-Coding-Standards#using-phpcs-and-wpcs-from-within-your-ide)
    * Configure your IDE to adopt WP coding layouts. Here is how you can do it in [PHPStorm](https://www.jetbrains.com/help/phpstorm/wordpress-aware-coding-assistance.html)

2. Development should be done in the `/load-html-files` project subdirectory. This is the plugin development source.
   If you use `wp-env` (recommended), the mapping happens automatically. Otherwise, your development installation should map `/meet-my-team` to `your-local-install/wp-content/plugins/meet-my-team`.

   In PHPStorm, you can use auto deployment mapping, or use a symbolic link or another method such as a local VSCode deployment method for this.

3. Local Development:

   The project is setup so you can use `wp-env` as a local dev environment. You will find a quick start guide [here](https://developer.wordpress.org/block-editor/getting-started/devenv/get-started-with-wp-env/).

   If you require a local configuration of `wp-env`, you can use `.wp-env.override.json`, which is ignored by Git. Running `wp-env start --xdebug` in the project root starts a local dev environment with xdebug enabled on `localhost:8720`. Here, `/load-html-file` is mapped into plugins.

## Validation ##
Run `composer check` in the project file to check for issues. Pushing to GitHub will trigger the check automation.

## Create a Pull Request ##
Create a detailed pull request for review.

## Release Manager Only Tasks ##
### Build for Release ###
Ensure versions are updated in the root PHP and readme.txt.

Run `gulp build`. This command copies everything from `/load-html-file` to `/dist`, removes unnecessary development code, and creates a zip in `/zipped`.

Then, commit the branch and tag with the release e.g. `5.4.5`. Merge the version branch with `master` and push it to GitHub.

### Release ###
Move the zip to the local `wordpress.org` directory and run the private script `update_wp_free_plugin`.

### Update this README.md ###
Periodically update this file if process changes, or if more clarity or extended information is required.