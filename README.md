
This module helps you release your silverstripe (or other) projects. 




# install
`composer require sunnysideup/release:dev-master` 


# release script usage

This module comes with an opiniated release script that can be used as follows:

### local
```shell
# release `develop` branch locally
vendor/bin/sake-release dev

# release `features/test` branch locally
vendor/bin/sake-release dev features/test 
```

### stage / test site
```shell
# release `develop` branch 
vendor/bin/sake-release test

# release `features/test` branch while making a backup first (-a)
vendor/bin/sake-release -a test features/test
```

### live / production / prod site
```shell
# release `production` branch 
vendor/bin/sake-release live

# release `production` branch while making a backup first (-a)
vendor/bin/sake-release -a live

# release `features/test` branch while making a backup first (-a)
vendor/bin/sake-release -a live features/test
```

## ALSO FLUSH FRONT-END
To also flush the front-end, set the following variable in your `.env` file:

```.env
SS_RELEASE_FRONT_END=true
```


## SPEED UP DEV/BUILD

To speed up the dev/build, you can add the following to your `.env` file:

```.env
SS_FAST_DEV_BUILD=true
```


# Building a Deployment Strategy / Pipeline


## requirements

release should be easy ....

 - Whenever you push to a designated branch, it releases on a server (test / production).
 - We are able to roll back (db + code)
 - Releases should be fast


# option 1 - use bitbucket _hook_ with this module. 

Here is how:

### set up .env variables
set:
 - `SS_RELEASE_TOKEN="ABC_ABC_ABC_ABC_ABC_ABC_ABC_ABC_"` # set to a random string
 - `SS_RELEASE_SCRIPT="vendor/bin/sake-release"`

in your `.env` file.

### finally

add a hook to bitbucket: 

`https://mysite.co.nz/_resources/vendor/sunnysideup/release/client/ReleaseProjectFromBitbucketHook.php?ts=ABC_ABC_ABC_ABC_ABC_ABC_ABC_ABC`


See https://confluence.atlassian.com/bitbucketserver/using-repository-hooks-776639836.html



# option 2 - use BEAM

See https://github.com/heyday/beam/





# option 3 - use bitbucket pipepline with this module

##### a. enable pipelines: https://bitbucket.org/yourorganisation/yourproject/admin/addon/admin/pipelines/settings (see settings / pipelines / settings) 

##### b. create ssh key on bitbucket.com (settings > pipelines > ssh keys)

##### c. add public ssh key to server in ~/.ssh/authorized_keys (or through a control panel)

##### d. write file below as `bitbucket-pipelines.yml` in the root of your project

```shell
pipelines:
  branches:

    development:
      - step:
          script:
            - ssh -o StrictHostKeyChecking=no bitbucket@111.222.333.444 'cd ./container/application; bash vendor/bin/sake-release test'

    master:
      - step:
          script:
            - ssh -o StrictHostKeyChecking=no bitbucket@111.222.333.444 'cd ./container/application; bash vendor/bin/sake-release live'

```

### example pipelines with extra stuff:

https://github.com/brettt89/silverstripe-docker




# Option 4: use https://deployer.org/ 

see details above.

