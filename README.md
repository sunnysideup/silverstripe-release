
This module helps you release your silverstripe (or other) projects. 




# install
`composer require sunnysideup/release:dev-master` 



# philosophy

release should be easy ....

 - Whenever you push to a designated branch, it releases on a server (test / production).
 - We are able to roll back (db + code)


# release strategy
 
 - `features branches` are created for - wait for it - new features
 - they then merge into `develop` (merge develop into feature branch fist, test and then merge into develop)
 - `develop` is (automatically - see below) released on the **test site**.
 - `develop` is then merged into `production`
 - `production` is tagged
 - tags are released on the **live site**.

Here are some options on how to achieve this



# option 1 - use bitbucket _hook_ with this module. 
use this module:

### first
set:
 - `SS_RELEASE_TOKEN=" ___ HELLO ___ OR SOMETHING LIKE THAT __"`
 - `SS_RELEASE_SCRIPT="run.sh"`

in your `.env` file.

### next

create a release script in the root of your silverstripe project (e.g. `run.sh`)

### finally

add a hook to bitbucket https://mysite.co.nz/_resources/vendor/sunnysideup/release/client/ReleaseProjectFromBitbucketHook.php?ts=29083w490809suiaiofd78897 




# option 2 - use BEAM

 - https://github.com/heyday/beam/





# option 3 - use bitbucket pipepline

##### a. enable pipelines: https://bitbucket.org/yourorganisation/yourproject/admin/addon/admin/pipelines/settings (see settings / pipelines / settings) 

##### b. create ssh key on bitbucket.com (settings > pipelines > ssh keys)

##### c. add ssh key to server in ~/.ssh/authorized_keys. (CONTROL PANEL VERSION: add autorized key to control panel and add `bitbucket` user to server)

##### d. write file below as `bitbucket-pipelines.yml`

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

##### f. add to your repo `npm-build-script.sh`


### pipelines with extra stuff:

https://github.com/brettt89/silverstripe-docker




# Option 4: use sake-release from this module directly on the command line

see details above.



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
