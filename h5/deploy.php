<?php
namespace Deployer;

require 'recipe/common.php';

// Configuration

set('repository', 'git@coding.codeages.net:edusoho/edusoho-h5.git');
set('git_tty', false); // [Optional] Allocate tty for git on first deployment
set('writable_mode', 'acl');


host('115.231.100.54')
    ->stage('dev')
    ->user('deployer')
    ->identityFile('~/.ssh/deployerkey')
    ->addSshOption('UserKnownHostsFile', '/dev/null')
    ->addSshOption('StrictHostKeyChecking', 'no')
    ->set('deploy_path', '/var/www/h5.st.edusoho.cn');

// Tasks

desc('Build frontend');
task('frontend:build', function() {
    run('npm run build');
})->local();

desc('Upload frontend compiled files');
task('frontend:upload', function() {
    desc('Upload frontend compiled files');
    upload('./dist/', '{{release_path}}');
});

desc('Deploy your project');
task('deploy', [
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:clear_paths',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'success'
]);

before('deploy:prepare', 'frontend:build');
after('deploy:release', 'frontend:upload');

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
