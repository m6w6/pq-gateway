<?php

namespace pq 
{

	interface Converter 
	{
		abstract public function convertTypes();

		abstract public function convertFromString($data, $type);

		abstract public function convertToString($data, $type);

	}

	class Cancel 
	{
		public $connection;

		public function __construct(\pq\Connection $connection) {
		}

		public function cancel() {
		}

	}

	interface Exception 
	{
		const INVALID_ARGUMENT = 0;
		const RUNTIME = 1;
		const CONNECTION_FAILED = 2;
		const IO = 3;
		const ESCAPE = 4;
		const BAD_METHODCALL = 5;
		const UNINITIALIZED = 6;
		const DOMAIN = 7;
		const SQL = 8;

	}

	class COPY 
	{
		const FROM_STDIN = 0;
		const TO_STDOUT = 1;

		public $connection;
		public $expression;
		public $direction;
		public $options;

		public function __construct(\pq\Connection $connection, $expression, $direction, $options = NULL) {
		}

		public function put($data) {
		}

		public function end($error = NULL) {
		}

		public function get(&$data) {
		}

	}

	class Connection 
	{
		const OK = 0;
		const BAD = 1;
		const STARTED = 2;
		const MADE = 3;
		const AWAITING_RESPONSE = 4;
		const AUTH_OK = 5;
		const SSL_STARTUP = 7;
		const SETENV = 6;
		const TRANS_IDLE = 0;
		const TRANS_ACTIVE = 1;
		const TRANS_INTRANS = 2;
		const TRANS_INERROR = 3;
		const TRANS_UNKNOWN = 4;
		const POLLING_FAILED = 0;
		const POLLING_READING = 1;
		const POLLING_WRITING = 2;
		const POLLING_OK = 3;
		const EVENT_NOTICE = 'notice';
		const EVENT_RESULT = 'result';
		const EVENT_RESET = 'reset';
		const ASYNC = 1;
		const PERSISTENT = 2;

		public $status;
		public $transactionStatus;
		public $socket;
		public $errorMessage;
		public $busy;
		public $encoding;
		public $unbuffered;
		public $db;
		public $user;
		public $pass;
		public $host;
		public $port;
		public $params;
		public $options;
		public $eventHandlers;
		public $defaultFetchType;
		public $defaultTransactionIsolation;
		public $defaultTransactionReadonly;
		public $defaultTransactionDeferrable;
		public $defaultAutoConvert;

		public function __construct($dsn, $async = NULL) {
		}

		public function reset() {
		}

		public function resetAsync() {
		}

		public function poll() {
		}

		public function exec($query) {
		}

		public function execAsync($query, $callable = NULL) {
		}

		public function execParams($query, array $params, array $types = NULL) {
		}

		public function execParamsAsync($query, array $params, array $types = NULL, $callable = NULL) {
		}

		public function prepare($name, $query, array $types = NULL) {
		}

		public function prepareAsync($name, $query, array $types = NULL) {
		}

		public function declare($name, $flags, $query) {
		}

		public function declareAsync($name, $flags, $query = NULL) {
		}

		public function unlisten($channel) {
		}

		public function unlistenAsync($channel) {
		}

		public function listen($channel, $callable) {
		}

		public function listenAsync($channel = NULL, $callable = NULL) {
		}

		public function notify($channel, $message) {
		}

		public function notifyAsync($channel, $message) {
		}

		public function getResult() {
		}

		public function quote($string) {
		}

		public function quoteName($type) {
		}

		public function escapeBytea($bytea) {
		}

		public function unescapeBytea($bytea) {
		}

		public function startTransaction($isolation = NULL, $readonly = NULL, $deferrable = NULL) {
		}

		public function startTransactionAsync($isolation = NULL, $readonly = NULL, $deferrable = NULL) {
		}

		public function trace($stdio_stream = NULL) {
		}

		public function off($type) {
		}

		public function on($type, $callable) {
		}

		public function setConverter(\pq\Converter $converter) {
		}

		public function unsetConverter(\pq\Converter $converter) {
		}

	}

	class Cursor 
	{
		const BINARY = 1;
		const INSENSITIVE = 2;
		const WITH_HOLD = 4;
		const SCROLL = 16;
		const NO_SCROLL = 32;

		public $name;
		public $connection;

		public function __construct(\pq\Connection $connection, $name, $flags, $query, $async = NULL) {
		}

		public function open() {
		}

		public function close() {
		}

		public function fetch($spec) {
		}

		public function move($spec = NULL) {
		}

		public function fetchAsync($spec = NULL, $callback = NULL) {
		}

		public function moveAsync($spec = NULL, $callback = NULL) {
		}

	}

	class DateTime extends \DateTime implements \DateTimeInterface, \JsonSerializable 
	{
		public $format;

		public function __toString() {
		}

		public function jsonSerialize() {
		}

	}

	class LOB 
	{
		const INVALID_OID = 0;
		const R = 262144;
		const W = 131072;
		const RW = 393216;

		public $transaction;
		public $oid;
		public $stream;

		public function __construct(\pq\Transaction $transaction, $oid = NULL, $mode = NULL) {
		}

		public function write($data) {
		}

		public function read($length = NULL, &$read = NULL) {
		}

		public function seek($offset, $whence = NULL) {
		}

		public function tell() {
		}

		public function truncate($length = NULL) {
		}

	}

	class Result implements \Countable 
	{
		const EMPTY_QUERY = 0;
		const COMMAND_OK = 1;
		const TUPLES_OK = 2;
		const COPY_OUT = 3;
		const COPY_IN = 4;
		const BAD_RESPONSE = 5;
		const NONFATAL_ERROR = 6;
		const FATAL_ERROR = 7;
		const COPY_BOTH = 8;
		const SINGLE_TUPLE = 9;
		const FETCH_ARRAY = 0;
		const FETCH_ASSOC = 1;
		const FETCH_OBJECT = 2;
		const CONV_BOOL = 1;
		const CONV_INT = 2;
		const CONV_FLOAT = 4;
		const CONV_SCALAR = 15;
		const CONV_ARRAY = 16;
		const CONV_DATETIME = 32;
		const CONV_JSON = 256;
		const CONV_ALL = 65535;

		public $status;
		public $statusMessage;
		public $errorMessage;
		public $numRows;
		public $numCols;
		public $affectedRows;
		public $fetchType;
		public $autoConvert;

		public function bind($col, &$ref) {
		}

		public function fetchBound() {
		}

		public function fetchRow($fetch_type = NULL) {
		}

		public function fetchCol(&$ref, $col = NULL) {
		}

		public function fetchAll($fetch_type = NULL) {
		}

		public function fetchAllCols($col = NULL) {
		}

		public function count() {
		}

		public function map($keys = NULL, $vals = NULL, $fetch_type = NULL) {
		}

		public function desc() {
		}

	}

	class Statement 
	{
		public $name;
		public $connection;

		public function __construct(\pq\Connection $connection, $name, $query, array $types = NULL, $async = NULL) {
		}

		public function bind($param_no, &$param_ref) {
		}

		public function exec(array $params = NULL) {
		}

		public function desc() {
		}

		public function execAsync(array $params = NULL, $callable = NULL) {
		}

		public function descAsync($callable) {
		}

	}

	class Transaction 
	{
		const READ_COMMITTED = 0;
		const REPEATABLE_READ = 1;
		const SERIALIZABLE = 2;

		public $connection;
		public $isolation;
		public $readonly;
		public $deferrable;

		public function __construct(\pq\Connection $connection, $async = NULL, $isolation = NULL, $readonly = NULL, $deferrable = NULL) {
		}

		public function commit() {
		}

		public function rollback() {
		}

		public function commitAsync() {
		}

		public function rollbackAsync() {
		}

		public function savepoint() {
		}

		public function savepointAsync() {
		}

		public function exportSnapshot() {
		}

		public function exportSnapshotAsync() {
		}

		public function importSnapshot($snapshot_id) {
		}

		public function importSnapshotAsync($snapshot_id) {
		}

		public function openLOB($oid, $mode = NULL) {
		}

		public function createLOB($mode = NULL) {
		}

		public function unlinkLOB($oid) {
		}

		public function importLOB($local_path, $oid = NULL) {
		}

		public function exportLOB($oid, $local_path) {
		}

	}

	class Types 
	{
		const BOOL = 16;
		const BYTEA = 17;
		const CHAR = 18;
		const NAME = 19;
		const INT8 = 20;
		const INT2 = 21;
		const INT2VECTOR = 22;
		const INT4 = 23;
		const REGPROC = 24;
		const TEXT = 25;
		const OID = 26;
		const TID = 27;
		const XID = 28;
		const CID = 29;
		const OIDVECTOR = 30;
		const PG_TYPE = 71;
		const PG_ATTRIBUTE = 75;
		const PG_PROC = 81;
		const PG_CLASS = 83;
		const JSON = 114;
		const XML = 142;
		const XMLARRAY = 143;
		const JSONARRAY = 199;
		const PG_NODE_TREE = 194;
		const SMGR = 210;
		const POINT = 600;
		const LSEG = 601;
		const PATH = 602;
		const BOX = 603;
		const POLYGON = 604;
		const LINE = 628;
		const LINEARRAY = 629;
		const FLOAT4 = 700;
		const FLOAT8 = 701;
		const ABSTIME = 702;
		const RELTIME = 703;
		const TINTERVAL = 704;
		const UNKNOWN = 705;
		const CIRCLE = 718;
		const CIRCLEARRAY = 719;
		const MONEY = 790;
		const MONEYARRAY = 791;
		const MACADDR = 829;
		const INET = 869;
		const CIDR = 650;
		const BOOLARRAY = 1000;
		const BYTEAARRAY = 1001;
		const CHARARRAY = 1002;
		const NAMEARRAY = 1003;
		const INT2ARRAY = 1005;
		const INT2VECTORARRAY = 1006;
		const INT4ARRAY = 1007;
		const REGPROCARRAY = 1008;
		const TEXTARRAY = 1009;
		const OIDARRAY = 1028;
		const TIDARRAY = 1010;
		const XIDARRAY = 1011;
		const CIDARRAY = 1012;
		const OIDVECTORARRAY = 1013;
		const BPCHARARRAY = 1014;
		const VARCHARARRAY = 1015;
		const INT8ARRAY = 1016;
		const POINTARRAY = 1017;
		const LSEGARRAY = 1018;
		const PATHARRAY = 1019;
		const BOXARRAY = 1020;
		const FLOAT4ARRAY = 1021;
		const FLOAT8ARRAY = 1022;
		const ABSTIMEARRAY = 1023;
		const RELTIMEARRAY = 1024;
		const TINTERVALARRAY = 1025;
		const POLYGONARRAY = 1027;
		const ACLITEM = 1033;
		const ACLITEMARRAY = 1034;
		const MACADDRARRAY = 1040;
		const INETARRAY = 1041;
		const CIDRARRAY = 651;
		const CSTRINGARRAY = 1263;
		const BPCHAR = 1042;
		const VARCHAR = 1043;
		const DATE = 1082;
		const TIME = 1083;
		const TIMESTAMP = 1114;
		const TIMESTAMPARRAY = 1115;
		const DATEARRAY = 1182;
		const TIMEARRAY = 1183;
		const TIMESTAMPTZ = 1184;
		const TIMESTAMPTZARRAY = 1185;
		const INTERVAL = 1186;
		const INTERVALARRAY = 1187;
		const NUMERICARRAY = 1231;
		const TIMETZ = 1266;
		const TIMETZARRAY = 1270;
		const BIT = 1560;
		const BITARRAY = 1561;
		const VARBIT = 1562;
		const VARBITARRAY = 1563;
		const NUMERIC = 1700;
		const REFCURSOR = 1790;
		const REFCURSORARRAY = 2201;
		const REGPROCEDURE = 2202;
		const REGOPER = 2203;
		const REGOPERATOR = 2204;
		const REGCLASS = 2205;
		const REGTYPE = 2206;
		const REGPROCEDUREARRAY = 2207;
		const REGOPERARRAY = 2208;
		const REGOPERATORARRAY = 2209;
		const REGCLASSARRAY = 2210;
		const REGTYPEARRAY = 2211;
		const UUID = 2950;
		const UUIDARRAY = 2951;
		const PG_LSN = 3220;
		const PG_LSNARRAY = 3221;
		const TSVECTOR = 3614;
		const GTSVECTOR = 3642;
		const TSQUERY = 3615;
		const REGCONFIG = 3734;
		const REGDICTIONARY = 3769;
		const TSVECTORARRAY = 3643;
		const GTSVECTORARRAY = 3644;
		const TSQUERYARRAY = 3645;
		const REGCONFIGARRAY = 3735;
		const REGDICTIONARYARRAY = 3770;
		const JSONB = 3802;
		const JSONBARRAY = 3807;
		const TXID_SNAPSHOT = 2970;
		const TXID_SNAPSHOTARRAY = 2949;
		const INT4RANGE = 3904;
		const INT4RANGEARRAY = 3905;
		const NUMRANGE = 3906;
		const NUMRANGEARRAY = 3907;
		const TSRANGE = 3908;
		const TSRANGEARRAY = 3909;
		const TSTZRANGE = 3910;
		const TSTZRANGEARRAY = 3911;
		const DATERANGE = 3912;
		const DATERANGEARRAY = 3913;
		const INT8RANGE = 3926;
		const INT8RANGEARRAY = 3927;
		const RECORD = 2249;
		const RECORDARRAY = 2287;
		const CSTRING = 2275;
		const ANY = 2276;
		const ANYARRAY = 2277;
		const VOID = 2278;
		const TRIGGER = 2279;
		const EVENT_TRIGGER = 3838;
		const LANGUAGE_HANDLER = 2280;
		const INTERNAL = 2281;
		const OPAQUE = 2282;
		const ANYELEMENT = 2283;
		const ANYNONARRAY = 2776;
		const ANYENUM = 3500;
		const FDW_HANDLER = 3115;
		const ANYRANGE = 3831;

		public $connection;

		public function __construct(\pq\Connection $connection, array $namespaces = NULL) {
		}

		public function refresh(array $namespaces = NULL) {
		}

	}
}

namespace pq\Exception 
{

	class BadMethodCallException extends \BadMethodCallException implements \pq\Exception 
	{
	}

	class InvalidArgumentException extends \InvalidArgumentException implements \pq\Exception 
	{
	}

	class RuntimeException extends \RuntimeException implements \pq\Exception 
	{
	}

	class DomainException extends \DomainException implements \pq\Exception 
	{
		public $sqlstate;

	}
}

