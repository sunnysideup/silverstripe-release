# best practice

 - Whenever you push to a branch, it releases on a server (test / production).
 - Dist files are buid on the server
 - We are able to roll back

# option 1 - use BEAM
 
 - https://github.com/heyday/beam/

# option 2 - use bitbucket pipepline

 1. create ssh key on bitbucket.com (settings > pipelines > ssh keys)
 2. add ssh key to server (see if you can just add it to ~/.ssh/authorized_keys) / may have to added to control panel
 3. add `bitbucket` user to server
 4. turn on pipelines in bitbucket.com
 5. write file below as `bitbucket-pipelines.yml`

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

6. add to your repo `release.sh`

```shell
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

7. add to your repo `npm-build-script.sh`

### note

You can then also run this locally:

```shell
ssh myserver 'cd container/application/; bash release.sh'
```


# option 3
use this module:

### first 
set:
 - `SS_RELEASE_TOKEN=" ___ HELLO ___ OR SOMETHING LIKE THAT __"`
 - `SS_RELEASE_SCRIPT="~/container/application/run.sh"`
in your `.env` file. 

### next

add a hook to bitbucket https://mysite.co.nz/_resources/vendor/sunnysideup/release/client/ReleaseProjectFromBitbucketHook.php?ts=29083w490809suiaiofd78897 (edited) 

### after that

create a release script on your server using a name you like. 
