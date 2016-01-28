# Data Access in Zoop #
_Using databases is surprisingly easy with Zoop_

## Example Projects ##
The following projects from the examples directory in the codebase provide examples of data access in Zoop:
  * DbFunctions
  * DbObject
  * DbSchema
  * mysql
  * pgsql

## Configuring a database in your project ##
  * Step 1: Load neccesary libraries
In order to use data access in your Zoop project you must load the "db" library.  Usually this is done by adding the following line to your index.php:
```
	Zoop::loadLib('db');
```

  * Step 2: Set configuration parameters
Zoop uses the popular [YAML](http://www.yaml.org/) file format for project configuration.  This makes editing the configuration files simple and easy.  Here is an example config.yaml file with database configuration options in it:

/config.yaml
```
zoop:
    db:
        default:
            driver: php_mysql
            host: localhost
            database: zoop_project
            username: zoop
            password: Z00pz00p!
```

This example uses the [Mysql database driver](DatabaseDrivers.md), but there are many supported.  For more information on configuration options with different databases, please see the [Database Drivers page](DatabaseDrivers.md).

Once you have your yaml config file set up correctly, you are ready to start using the database tools!

## Choose your access method ##
For ease of use, Zoop provides a few different options for data access.  These methods are all connected together, but it's important to understand the basic groups when you are starting a new project.  There are three basic types:

  * SQL functions
    * SqlQuery
    * SqlInsertRow
    * SqlInsertArray
    * SqlUpdateRows
    * SqlDeleteRows
    * SqlBeginTransaction
    * Many more (see db/functions.php)
  * Object Oriented DB access framework
    * DBConnection
    * DbResultSet
    * DbTable
    * DbSchema
  * Object Oriented Data abstraction framework
    * DbObject
    * DbRelationship
    * DbZone

### Basic SQL functions ###
If you like to write the SQL yourself and will just be doing basic data access, the SQL functions will probably provide the most useful interface for you.  These functions provide a simple interface to the OO Data Access framework to keep things clean.
|Function|Description|
|:-------|:----------|
|`SqlQuery($sql, $params)`|This is generally the most common function used by developers who work with SQL directly -- simply put, it executes a SQL query and returns a DbResultSet object.|
|`SqlInsertRow($sql, $params)`|Very much like SqlQuery, but if there is an auto-generated primary key for the inserted row it will be returned.|
|`SqlInsertArray($tableName, $values)`|Accepts a table name and an array of fields to be inserted, generates the insert statement, and executes it.   If there is an auto-generated primary key for the inserted row it will be returned.|
|`SqlUpdateRows($sql, $params)`|Executes a SQL query and returns the number of rows affected. `SqlUpdateRow($sql, $params)` does the same thing but will throw an error if mroe than one row is updated.|
|`SqlDeleteRows($sql, $params)`|Pretty much an alias to SqlUpdateRows in most databases.  In the future we may find some that this will be different for.|

<sup>*It is worth noting that all of these functions have equivilents in -- and are usually just aliases to -- methods in the DbConnection object</sup>

All variables to be inserted into the sql query that may have come from un untrusted source (like from a form on a web page) should not be inserted into the SQL directly, but rather should be passed in as parameters.

Example:
```
/**
 * Pretend that these are coming from a web form. :-P
 */
$first = "Bobb";
$last = "Doe";
$age = 45;

$params = array(
	"first" => $first
	, "last" => $last
	, $age => $age);

$sql = "insert into People (age, first, last) values (:first, :last, :age:int)";
$newId = SqlInsertRow($sql, $params);
```
This code will execute the following SQL statement (if mysql is the database server in use)
```
insert into People (age, first, last) values ('Bobb', 'Doe', 45)
```

As you can see, the placeholders for field data are pretty simple.  Here are the options:
  * `:<varname>:string` 	-- the variable is escaped as a string and replaces this entry
  * `:<varname>:int` 		-- the variable is escaped as a number and replaces this entry
  * `:<varname>:keyword`	-- the variable replaces this entry with no change
  * `:<varname>:identifier`	-- the variable is escaped as a SQL identifier (table, field name, etc) and replaces this entry
  * `:<varname>`			-- if no type is specified, variable is treated as a string.

The database query functions will automatically escape all variables thus inserted correctly so that no query insertion tricks can be used (Note: We aren't responsible if somehow it happens anyway... we're pretty sure it's not possible, but we take no responsibility if someone still somehow manages to fry your database.  If you're concerned, look through the sourcecode and see if you find any flaws in the implementations)

### Advanced SQL functions ###

**TODO:**Add docs for functions like SqlFetchOne, SqlFetchMap, SqlFetchColumn, SqlInsertArray, etc

## Object Oriented DB access framework ##

**TODO:**Add docs for directly using the database objects

## Object Oriented Data abstraction framework ##

The core componant to this framework is an class called DbObject.

**TODO:**Finish documenting DbObject, DbZone, and others

See also: DbObject example in the svn tree