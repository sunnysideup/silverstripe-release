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
            - ssh -o StrictHostKeyChecking=no a@111.222.333.444 'cd ./container/application; bash release.sh'

    master:
      - step:
          script:
            - echo "hello"

```

6. add to your repo `release.sh`

```shell
composer install --no-dev
vendor/bin/sake dev/build flush=all
bash npm-build-script.sh
```
OR

```shell
wget https://silverstripe.github.io/sspak/sspak.phar
chmod +x sspak.phar

rm backup.sspak
php sspak.phar save --db . backup.sspak 


git fetch --all
git status
git pull
composer install --no-dev
vendor/bin/sake dev/build flush=all
```

7. add to your repo `npm-build-script.sh`

### note

You can then also run this locally:

```shell
ssh myserver 'cd container/application/; bash release.sh'
```


# option 3
only release tags? 
