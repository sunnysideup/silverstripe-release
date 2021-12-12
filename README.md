# philosophy

release should be easy ....

 - Whenever you push to a designated branch, it releases on a server (test / production).
 - Dist files are build on the server (where possible)
 - We are able to roll back (db + code)


# sunny side up release strategy
 
 - `features branches` are created for - wait for it - new features
 - they then merge into `develop` (merge develop into feature branch fist, test and then merge into develop)
 - `develop` is (automatically - see below) released on the **test site**.
 - `develop` is then merged into `master`
 - `master` is tagged
 - tags are released on the **live site**.

Here are some options on how to achieve this

# option 1 - use bitbucket book with this module. 
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
            - ssh -o StrictHostKeyChecking=no bitbucket@111.222.333.444 'cd ./container/application; bash release.sh'

    master:
      - step:
          script:
            - echo "hello"

```

##### e. add to your repo `release.sh`

```shell
#!/bin/bash
cd "$(dirname "$0")"

echo "=========================" >> release.log
echo "Time: $(date). START UPDATE: " >> release.log
git describe --all --long > release.log
git fetch --all
git pull
composer install --no-dev
vendor/bin/sake dev/build flush=all
bash npm-build-script.sh
git describe --all --long > release.log
```
OR (with backup)

```shell
#!/bin/bash
cd "$(dirname "$0")"

echo "=========================" >> release.log
echo "Time: $(date). START UPDATE: " >> release.log

wget https://silverstripe.github.io/sspak/sspak.phar
chmod +x sspak.phar

rm backup.sspak
php sspak.phar save --db . backup.sspak

git describe --all --long > release.log
git fetch --all
git status
git pull

composer install --no-dev
vendor/bin/sake dev/build flush=all

npm-build-script.sh

git describe --all --long > release.log
```

##### f. add to your repo `npm-build-script.sh`

### bonus idea

Once you have a release file on the server, you can then also run this locally:

```shell
ssh myserver 'cd container/application/; bash release.sh'
```

### pipelines with extra stuff:

https://github.com/brettt89/silverstripe-docker

# Option 4: use sake-release from this module.

##### a. install this module

##### b. on command line browse to root dir and run: 
 
   - `sake-relase` to release the latest version of the current branch.
   - `sake-relase -l` to release the latest tag
   - `sake-relase -t 1.0.0` to release a specific tag
   - `sake-relase -b feature/something` to release the lastet version of a specific branch

