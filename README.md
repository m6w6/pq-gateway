# pq-gateway

A [gateway](http://martinfowler.com/eaaCatalog/tableDataGateway.html) implementation 
for [ext-pq](http://git.php.net/?p=pecl/database/pq.git;a=summary).

## Docs

http://devel-m6w6.rhcloud.com/mdref/pq-gateway

## News
* ***2015-05-20:*** 2.1.0 tagged
* ***2014-10-15:*** 2.0.0 tagged
* ***2013-05-15:*** 1.1.0 tagged
* ***2013-05-03:*** 1.0.0 tagged

## ChangeLog

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
