# CrontabManager

PHP library to manage programmatically GNU/Linux cron jobs.

It enables you to : 

- Deal with your cron jobs in PHP.
- Create new Cron jobs.
- Update existing cron jobs.
- Manage cron jobs of others users than runtime user using some sudo tricks (see below). 

## Requirments:
- PHP 5.3+
- `crontab` command-line utility (should be already installed in your distribution).
- `sudo`, if you want to manage crontab of another user than runtime user without running into right issues (see below)

## Installation:

The library can be installed using Composer. 
```   
composer require tiben/crontab-manager ~1.0
```

## Usage:
The library is composed of three classes: 

- `CrontabJob` is an entity class which represents a cron job.
- `CrontabRepository` is used to persist/retrieve your cron jobs.
- `CrontabAdapter` handles cron jobs persistance in the crontab. 

### Instanciate the repository:
In order to work, the CrontabRepository needs an instance of CrontabAdapter.

```php
$crontabRepository = new CrontabRepository(new CrontabAdapter());
```

### Create new Job and persist it into the crontab:
Suppose you want to create an new job which consists of launching the command "df >> /tmp/df.log" every day at 23:30. You can do it in two ways.

- In Pure oo way :
```php
$crontabJob = new CrontabJob();
$crontabJob->minutes = '30';
$crontabJob->hours = '23';
$crontabJob->dayOfMonth = '*';
$crontabJob->months = '*';
$crontabJob->dayOfWeek = '*';
$crontabJob->taskCommandLine = 'df >> /tmp/df.log';
$crontabJob->comments = 'Logging disk usage'; // Comments are persisted in the crontab
```

- From raw cron syntax string using a factory method : 
```php
$crontabJob = CrontabJob::createFromCrontabLine('30 23 * * * df >> /tmp/df.log');
```

You can now add your new cronjob in the crontab repository and persist all changes to the crontab.
```php
$crontabRepository->addJob($crontabJob);
$crontabRepository->persist();
```

### Find a specific cron job from the crontab repository and update it:
Suppose we want to modify the hour of an already existing cronjob. Finding existings jobs is done using some regular expressions. The regex is applied to the entire crontab line. 
```php
$results = $crontabRepository->findJobByRegex('/Logging\ disk\ usage/');
$crontabJob = $results[0];
$crontabJob->hours = '21';
$crontabRepository->persist();
```

### Remove a cron job from the crontab:
You can remove a job like this :
```php
$results = $crontabRepository->findJobByRegex('/Logging\ disk\ usage/');
$crontabJob = $results[0];
$crontabRepository->removeJob($crontabJob);
$crontabRepository->persist();
```
Note: Since cron jobs are internally matched by reference, they must be previously obtained from the repository or previously added.

### Work with the crontab of another user than runtime user:
This feature allows you to manage the crontab of another user than the user who launched the runtime. This can be useful when the runtime user is `www-data` but the owner of the crontab you want to edit is your own linux user account. 

To do this, simply pass the username of the crontab owner as parameter of the CrontabAdapter constructor. Suppose you are `www-data` and you want to edit the crontab of user `bobby`:
```php
$crontabAdapter = new CrontabAdapter('bobby');
$crontabRepository = new CrontabRepository($crontabAdapter);
```

Using this way you will propably run into user rights issues. 
This can be handled by editing your sudoers file using 'visudo'.     
If you want to allow user `www-data` to edit the crontab of user `bobby`, add this line:
```
www-data        ALL=(bobby) NOPASSWD: /usr/bin/crontab
```
which tells sudo not to ask for password when user `www-data` calls `crontab` as user `bobby` using `sudo`

Now, you can access to the crontab of user `bobby` like this :
```php
$crontabAdapter = new CrontabAdapter('bobby', true);
$crontabRepository = new CrontabRepository($crontabAdapter);
```
Note the second parameter `true` of the CrontabAdapter constructor call. This boolean tells the CrontabAdapter to use `sudo` internally when calling `crontab`.   

### Enable or disable a cron job
You can enable or disable your cron jobs by setting the `enabled` attribute of a CronJob object accordingly :
```php
$crontabJob->enabled = false;
```
This will have the effect to prepend your cron job by a "#" in your crontab when persisting it. 
