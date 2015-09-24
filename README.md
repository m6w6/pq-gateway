# pq-gateway

A gateway and data mapper implementation 
for [ext-pq](http://git.php.net/?p=pecl/database/pq.git;a=summary).

## Docs

https://mdref.m6w6.name/pq-gateway

## News
* ***2015-??-??:*** 2.2.0
* ***2015-05-20:*** 2.1.0 tagged
* ***2014-10-15:*** 2.0.0 tagged
* ***2013-05-15:*** 1.1.0 tagged
* ***2013-05-03:*** 1.0.0 tagged

## ChangeLog

### 2.2.0
* Added pq\Mapper
* pq\Gateway\Cell is only considered dirty if updated data differs
* pq\Query\ExecutorInterface::execute()'s 2nd parameter `callable $callback` is
  now optional

### 2.1.0
* Added pq\Query\AsyncExecutor::setCallbacks(callable $init, callable $done, callable $then)  
  and removed soft dependency on reactphp/promise
* Fixed pq\Gateway\Table::with()'s relation handling when source table equals foreign table

### 2.0.0
* Published documentation
* Added support for pecl/pq-0.5
* Refactored relations

	
### 1.1.0
* Added support for one-dimensional arrays
* Added pq\Gateway\Table\Attributes (type support for input parameters)

### 1.0.0
* First stable release
