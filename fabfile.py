from fabric.api import *
import datetime, time

env.work_dir = '/var/www/html'
env.colorize_errors = 'true'

### Environment Utilities ###
@task
def _set_host(environment):
    """
    Sets the environment to either local or the remote host specified.
    If remote, specifies the project path and key file needed to connect.
    """
    env.environment = environment

@task
def dev():
    """ Use development server settings """
    _set_host('dev')
    env.hosts = ['']

@task
def production():
    """ Use production server settings """
    print('******************************')
    print('********   WARNING    ********')
    print('********  PRODUCTION  ********')
    print('******************************')
    _set_host('production')
    env.hosts = ['']

### Get Date for Commit Message ###
ts = time.time()
st = datetime.datetime.fromtimestamp(ts).strftime('%Y-%m-%d %H:%M:%S')

### Server Commands ###
@task
def server_update():
    """ Updates server with new packages """
    with settings(warn_only=True):
        with cd(env.work_dir):
            check = raw_input('\nWarning you are about to deploy code to {}.\n'.format(
                env.environment) + 'Please confirm the environment that you wish to '
                'push to if you want continue: ')

            if check == env.environment:
                run('sudo apt-get -y update')
                run('sudo apt-get -y upgrade')
                run('sudo apt-get -y dist-upgrade')

@task
def update():
    """ Updates NPM modules with new packages """
    with cd(env.work_dir):
        run('rm -rf node_modules')
        run('npm install')
        run('gulp build')

@task
def install():
    """ Updates NPM modules with new packages """
    with cd(env.work_dir):
        run('npm install')

@task
def status():
    """ Navigates to the site directory and executes `git status` """
    with cd(env.work_dir):
        run('git status')

@task
def pull(branch):
    """ Navigates to the site directory and executes `git pull` """
    with cd(env.work_dir):
        run('git add .')
        run('git pull origin {}'.format(branch))
        run('gulp build')

@task
def push(branch):
    """ Navigates to the site directory and executes `git push origin <branch>` """
    with cd(env.work_dir):
        run('git push origin {}'.format(branch))

@task
def deploy(branch='master'):
    """ Navigates to the site directory and deploys code (master branch on production)` """
    with settings(warn_only=True):
        with cd(env.work_dir):
            check = raw_input('\nWarning you are about to deploy code to {}.\n'.format(
                env.environment) + 'Please confirm the environment that you wish to '
                'push to if you want continue: ')

            if check == env.environment:
                run('sudo chown -R www-data:www-data /var/www/html/')
                run('sudo find /var/www/html/ -type f -exec chmod 664 {} \;')
                run('sudo find /var/www/html/ -type d -exec chmod 755 {} \;')
                run('git add -A')
                run('git commit -m "Production Commit on {}"'.format(st))
                run('git pull origin master')
                run('gulp build')
                run('git push origin master')
                run('sudo chown -R www-data:www-data /var/www/html/')
                run('sudo find /var/www/html/ -type f -exec chmod 664 {} \;')
                run('sudo find /var/www/html/ -type d -exec chmod 755 {} \;')

@task
def revert(commit):
    """ Revert git via reset --hard <commit hash> """
    with cd(env.work_dir):
        run('git reset --hard {}'.format(commit))
