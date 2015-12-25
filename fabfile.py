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
def prod():
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
def update():
    """ Updates NPM modules with new packages """
    with settings(warn_only=True):
        with cd(env.work_dir):
            run('rm -rf node_modules')
            run('npm install')
            run('gulp build')

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
                if env.environment == 'dev':
                    run('git checkout -f')
                    run('git checkout master')
                    run('git reset --hard origin/master')
                    run('git pull')
                    if branch != 'master':
                        run('git checkout {}'.format(branch))
                    run('git pull origin {}'.format(branch))
                    run('gulp build')
                else:
                    run('git add -A')
                    run('git commit -m "Production Commit on {}"'.format(st))
                    run('git pull origin master')
                    run('gulp build')
                    run('git push origin master')

@task
def revert(commit):
    """ Revert git via reset --hard <commit hash> """
    with cd(env.work_dir):
        run('git reset --hard {}'.format(commit))