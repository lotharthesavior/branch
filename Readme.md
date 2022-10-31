![Branch Logo](img/branch-logo.png)

[![Tests](https://github.com/lotharthesavior/branch/actions/workflows/php.yml/badge.svg)](https://github.com/lotharthesavior/branch/actions/workflows/php.yml)

# Branch

Branch is a PHP Git library based on kbjr/Git.php



## Description

Branch is a PHP Git repository control library. Allows the running of any git command from a PHP class. Runs git commands using `proc_open`, not `exec` or the type, therefore it can run in PHP safe mode.



## Requirements

A system with [git](http://git-scm.com/) installed

## Installation

To install via composer, you can simply run:

```shell
composer require lotharthesavior/branch
```



## Basic Use

### Open Repository

```php
$console = new Console;
$repo = Git::open($console, '/path/to/repo'); // GitRepo
```

### Create Repository

```php
$console = new Console;
$repo = Git::create($console, '/path/to/repo'); // GitRepo
```

### Stage Changes

```php
$repo->add('.');
```

### Commit

```php
$repo->commit('Some commit message');
```

### Clone

```php
$localPath = 'local-repo';
$repo = 'repo-url';
$repo->clone( $repo, $localPath );
```

### Push

```php
$repo->push('origin', 'master');
```

### Branches

#### Create

```php
$repo->branchNew( 'name-for-the-branch' );
```

#### Get

```php
$repo->branchGet(); // array
```

#### Get Active Branch

```php
// Git\GitRepo;
$repo;

$repo->getActiveBranch(); // string
```

### Remotes

#### Get

```php
$repo->remote(); // \Git\DTO\Remote[]
```

#### Add

```php
$name = 'name';
$address = 'url';
$type = '(fetch)';
$remote = new Remote($name, $address, $type);
$repo->remoteAdd($remote);
```

#### Push

```php
$branch = new Branch('master');
$remote = new Remote('name', 'url', '(push)');
$repo->push($remote, $branch);
```

#### Pull

```php
$branch = new Branch('master');
$remote = new Remote('name', 'url', '(fetch)');
$repo->pull($remote, $branch);
```

### Description

#### Set

```php
$repo->setDescription( 'Some Description' );
```

#### Get

```php
$repo->getDescription(); // string
```

