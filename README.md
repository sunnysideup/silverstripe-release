# Silverstripe Release Module

This module helps you release your silverstripe (or other) projects.

## install

`composer require sunnysideup/release`

## release script usage

This module comes with an opiniated release script that can be used as follows:

### try it

```shell
vendor/bin/sake-release -h
```

#### skip front-end flushing

The script flushes the cli and apache cache by default.

To skip flushing the front-end, set the following variable in your `.env` file:

```.env
SS_RELEASE_FRONT_END=false
```

#### to release a specific branch

In general, the script will release the branch

1. set in your command - e.g. `vendor/bin/sake-release develop` will release the `develop` branch.
2. if not set, the script will release the branch set in the `.env` file - e.g. `SS_RELEASE_BRANCH=develop`.
3. if not set, the script will release the branch that fits your `SS_ENVIRONMENT_TYPE` - e.g. `SS_ENVIRONMENT_TYPE=live` will release the `production` branch. Mapping is as follows: `live` => `production`, `staging` => `staging`, `test` => `test`, `dev` => `develop`.

To release a specific branch, set the following variable in your `.env` file:

```.env
SS_RELEASE_BRANCH=feature/my-branch
```

#### speed up DEV/BUILD

To speed up the `dev/build`, you can add the following to your `.env` file:

```.env
SS_FAST_DEV_BUILD=true
```

## other notes about releasing

Here are some general notes about releases. Use as you see fit.

### requirements

release should be easy ....

- Whenever you push to a designated branch, it releases on a server (test / production).
- We are able to roll back (db + code)
- Releases should be fast

### option 1 - use bitbucket _hook_ with this module

Here is how:

##### set up .env variables

set:

- `SS_RELEASE_TOKEN="FOO_BARFOO_BARFOO_BAR"` # set to a random string
- `SS_RELEASE_SCRIPT="vendor/bin/sake-release"`

in your `.env` file.

then add a hook to bitbucket:

`https://mysite.co.nz/_resources/vendor/sunnysideup/release/client/ReleaseProjectFromBitbucketHook.php?ts=FOO_BARFOO_BARFOO_BAR`

See <https://confluence.atlassian.com/bitbucketserver/using-repository-hooks-776639836.html>

### option 2 - use BEAM

See <https://github.com/heyday/beam/>

### option 3 - use bitbucket pipepline with this module

1. enable pipelines: <https://bitbucket.org/yourorganisation/yourproject/admin/addon/admin/pipelines/settings> (see settings / pipelines / settings)

2. create ssh key on bitbucket.com (settings > pipelines > ssh keys)

3. add public ssh key to server in ~/.ssh/authorized_keys (or through a control panel)

4. write file below as `bitbucket-pipelines.yml` in the root of your project

```shell
pipelines:
  branches:

    development:
      - step:
          script:
            - ssh -o StrictHostKeyChecking=no bitbucket@111.222.333.444 'cd ./var/www/mysite; bash vendor/bin/sake-release develop'

    master:
      - step:
          script:
            - ssh -o StrictHostKeyChecking=no bitbucket@111.222.333.444 'cd ./var/www/mysite; bash vendor/bin/sake-release production'

```

##### example pipelines with extra stuff

<https://github.com/brettt89/silverstripe-docker>

### Option 4: use <https://deployer.org/>

TBC

### Option 5: use github actions

TBC
