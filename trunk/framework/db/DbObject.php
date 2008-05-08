<?php
class DbObject implements Iterator
{
	/**
	 * The assigned name of the table (defaults to the class name translated by the getDefaultTableName
	 *
	 * @var string
	 */
	protected $tableName;

	/**
	 * The field name(s) of the primary key in an array
	 *
	 * @var array
	 */
	protected $primaryKey;
	protected $keyAssignedBy;
	private $missingKeyFields;
	private $bound;
	private $persisted;
	private $scalars;
	private $relationships;

	const keyAssignedBy_db = 1;
	const keyAssignedBy_dev = 2;
	const keyAssignedBy_auto = 3;

	/**
	 * This is the constructor.  (honest, I swear)
	 *
	 * Some things to know about the defaults defined by this constructor:
	 * Default primary key: id
	 * Default keyAssignedBy: keyAssignedBy_db
	 *
	 * If keyAssignedBy is keyAssignedBy_db the primary_key array can contain no more than one field
	 *
	 * @param mixed $init Initial value for the primary key field (if this is supplied there can be only one field in the primary key)
	 */
	function __construct($init = NULL)
	{
		//	set up some sensible defaults
		$this->primaryKey = array('id');
		$this->tableName = $this->getDefaultTableName();
		$this->bound = false;
		$this->keyAssignedBy = self::keyAssignedBy_db;
		$this->scalars = array();
		$this->relationships = array();
		$this->persisted = NULL;
		
		$this->init($init);

		$this->missingKeyFields = count($this->primaryKey);
		if($this->keyAssignedBy == self::keyAssignedBy_db && count($this->primaryKey) != 1)
			trigger_error("in order for 'keyAssignedBy_db' to work you must have a single primary key field");

		if(is_array($init))
		{
			$this->assignScalars($init);
		}
		else if($init === NULL)
		{
			return;
		}
		else
		{
			assert(count($this->primaryKey) == 1);
			$this->assignScalars(array($this->primaryKey[0] => $init));
		}
	}

	/**
	 * This is a second stage constructor that should be overridden in individual database objects to initialize the instance
	 *
	 */
	protected function init()
	{
		//	override this function to setup relationships without having to handle the constructor chaining
	}

	/**
	 * Returns the name of the table based on the name of the current class.  If the class is called "personObject" the tablename will default to person_object.
	 *
	 * @return string
	 */
	private function getDefaultTableName()
	{
		$name = get_class($this);

		//      if there are any capitals after the firstone insert and underscore
		$name = $name[0] . preg_replace('/[A-Z]/', '_$0', substr($name, 1));

		//      lowercase everything and return it
		return strtolower($name);
	}

	/**
	 * Returns the name of the table associated with the db object
	 *
	 * @return string
	 */
	public function getTableName()
	{
		return $this->tableName;
	}

	/**
	 * Returns the value of the primary key of the record that this object is associated with.  An error will be thrown if there is more than one primary key.
	 *
	 * @return mixed
	 */
	public function getId()
	{
		assert(count($this->primaryKey) == 1);
		return $this->scalars[$this->primaryKey[0]];
	}

	/**
	 * Returns the field name(s) in the primary key in an array
	 *
	 * @return array of field names
	 */
	public function getPrimaryKey()
	{
		return $this->primaryKey;
	}

	/**
	 * Returns true if this DbObject class is set to use primary keys generated by the database
	 *
	 * @return boolean True if the database automatically generates primary keys
	 */
	public function primaryKeyAssignedByDb()
	{
		return $this->keyAssignedBy == self::keyAssignedBy_db ? true : false;
	}

	/**
	 * Returns a DbTable object with scheme information for the associated table (if supported by your database)
	 *
	 * @return DbTable DbTable object
	 */
	static public function _getTableSchema($className)
	{
		$object = new $className();
		return $object->getSchema();
	}
	
	/**
	 * Returns a DbTable object with scheme information for the associated table (if supported by your database)
	 *
	 * @return DbTable DbTable object
	 */
	public function getSchema()
	{
		return new DbTable(self::_getConnection(get_class($this)), $this->tableName);
	}
	
	/**
	 * Alias for getSchema
	 *
	 * @return DbTable DbTable object
	 */
	public function getTable()
	{
		return $this->getSchema();
	}

	/**
	 * Loops through all the fields in the associated table and ensures a default NULL value for each of them in the class
	 * This feature only works with DbConnection objects that have full schema support implemented
	 */
	public function forceFields()
	{
		if($this->bound)
			$this->loadScalars();
		else
		{
			foreach($this->getSchema()->fields as $thisField)
				$this->scalars[$thisField->name] = NULL;
		}
	}

	/**
	 * Serializes all column names and values and returns them in a string of the format "<DbObject class>: <field> => <value> <field> => <value> ..."
	 *
	 * @return string string
	 */
	public function getString()
	{
		$s = '';
		$this->loadScalars();
		foreach($this->scalars as $field => $value)
			$s .= " $field => $value";
		return get_class($this) . ':' . $s;
	}

	/**
	 * Returns the connection associated with the DbObject
	 *
	 * @return DbConnection DbConnection object
	 */
	public function getDb()
	{
		return self::_getConnection(get_class($this));
	}

	//
	//	the scalar handlers
	//
	//	rewrite them and make them handle primary keys with different names or more than one field
	//

	/**
	 * Returns a $field => $value array containing all the fields and their values
	 *
	 * @return assoc_array assoc_array containing all fields and values
	 */
	public function getFields()
	{
		return $this->scalars;
	}

	/**
	 * Accepts a $field => $value array containing fields and their values to be set in this DbObject
	 *
	 * @param assoc_array $data $field => $value associative array with data to be stored in this object
	 */
	public function setFields($data)
	{
		$this->assignScalars($data);
	}

	/**
	 * Returns the value of the specified field
	 *
	 * @param string $field Field to retreive
	 * @return string String value of the field
	 */
	public function getField($field)
	{
		return $this->getScalar($field);
	}

	/**
	 * Returns the value of the specified field
	 *
	 * @param string $field Field to retreive
	 * @return string String value of the field
	 */
	private function getScalar($field)
	{
		if(!isset($this->scalars[$field]))
		{
			if(!$this->bound)
			{
				/* TODO: Handle "getScalar" calls to unbound DbObject instances
				Different possibilities on how to handle this situation.  Maybe we could use some flags.
				1. check the metadata.  (alwaysCheckMeta)
					1. if its there then (useDummyDefaults requires alwaysCheckMeta)
						1. return the default value
						2. return NULL
					2. if its not there
						1. throw and error
				2.	dont check the metadata (useDummyNulls requires !alwaysCheckMeta)
					1.	return null
					2.	throw an error

				trigger_error("the field: $field is not present in memory and this object is not yet bound to a database row");
				*/

				return NULL;
			}

			$this->loadScalars();
		}
		
		if(!array_key_exists($field, $this->scalars))
			trigger_error("the field $field is present neither in memory nor in the cooresponding database table");

		return $this->scalars[$field];
	}

	/**
	 * Assigns a value to the specified field
	 *
	 * @param string $field Field to change the value of
	 * @param mixed $value Value to assign to the specified field
	 */
	private function setScalar($field, $value)
	{
		$data[$field] = $value;
		$this->assignScalars($data);
	}

	/*
	private function setScalars($data)
	{
		foreach($data as $field => $value)
		{
			$this->scalars[$field] = $value;
		}
	}
	*/

	/**
	 * Accepts a $field => $value array containing fields and their values to be set in this DbObject
	 *
	 * @param assoc_array $data $field => $value associative array with data to be stored in this object
	 */
	private function assignScalars($data)
	{
		foreach($data as $member => $value)
		{
			if(!isset($this->scalars[$member]) && in_array($member, $this->primaryKey))
			{
				$this->missingKeyFields--;
				if($this->missingKeyFields == 0)
					$this->bound = 1;
			}

			$this->scalars[$member] = $value;
		}
	}

	/**
	 * Loads values into the fields of the DbObject
	 *
	 */
	private function loadScalars()
	{
		assert($this->bound);
		$row = $this->fetchPersisted();
		$this->assignPersisted($row);
	}

	private function assignPersisted($row)
	{
		//	if they manually set a field don't write over it just because they loaded one scalar
		foreach($row as $field => $value)
		{
			if(!isset($this->scalars[$field]))
				$this->scalars[$field] = $value;
		}
	}

	/**
	 * Retrieves field values from the database using primary key as lookup fields
	 *
	 * @return unknown
	 */
	private function fetchPersisted()
	{
		$wheres = array();
		$whereValues = array();
		foreach($this->primaryKey as $keyField)
		{
			$wheres[] = ":fld_$keyField:identifier = :$keyField";
			$whereValues["fld_$keyField"] = $keyField;
			$whereValues[$keyField] = $this->scalars[$keyField];
		}
		$whereClause = implode(' and ', $wheres);
		$row = self::_getConnection(get_class($this))->fetchRow("select * from $this->tableName where $whereClause", $whereValues);
		if($row)
			$this->persisted = true;
		else
			$this->persisted = false;
		return $row;
	}

	/**
	 * Returns true if this DbObject is (and can be) saved in the database
	 *
	 * @return boolean True if the DbObject is/can be saved in the DB
	 */
	private function _persisted()
	{
		if(!$this->bound)
			return false;

		if($this->keyAssignedBy == self::keyAssignedBy_db)
			return true;
		else
		{
			$row = $this->fetchPersisted();
			if($row)
			{
				//	we might as well save the results
				$this->assignPersisted();
				return true;
			}

			return false;
		}
	}

	/**
	 * Returns true if this DbObject is (and can be) saved in the database
	 *
	 * @return boolean True if the DbObject is/can be saved in the DB
	 */
	public function persisted()
	{
		if($this->persisted !== NULL)
			return $this->persisted;
		else
			return $this->persisted = $this->_persisted();
	}

	/**
	 * Saves the record in memory
	 *
	 */
	public function save()
	{
		if(!$this->bound)
		{
			if($this->keyAssignedBy == self::keyAssignedBy_db)
				$this->setScalar($this->primaryKey[0], self::_getConnection(get_class($this))->insertArray($this->tableName, $this->scalars));
			else
				trigger_error("you must define all foreign key fields in order by save this object");
		}
		else
		{
			if($this->keyAssignedBy == self::keyAssignedBy_db)
			{
				$updateInfo = DbConnection::generateUpdateInfo($this->tableName, $this->getKeyConditions(), $this->scalars);
				self::_getConnection(get_class($this))->updateRow($updateInfo['sql'], $updateInfo['params']);
			}
			else
			{
				if(!$this->persisted())
					self::_getConnection(get_class($this))->insertArray($this->tableName, $this->scalars, false);
				else
				{
					$updateInfo = DbConnection::generateUpdateInfo($this->tableName, $this->getKeyConditions(), $this->scalars);
					self::_getConnection(get_class($this))->updateRow($updateInfo['sql'], $updateInfo['params']);
				}
			}
		}
	}

	/**
	 * Returns an array containing all primary key fields that have a value assigned to them in this DbObject instance
	 *
	 * @return array of fields
	 */
	private function getKeyConditions()
	{
		assert($this->bound);
		return array_intersect_key($this->scalars, array_flip($this->primaryKey));
	}

	/**
	 * Deletes the record from the database, deletes all fields and values from memory, and unbinds the DbObject
	 *
	 */
	public function destroy()
	{
		//	have a way to destroy any existing vector fields or refuse to continue (destroy_r)
		$deleteInfo = DbConnection::generateDeleteInfo($this->tableName, $this->getKeyConditions());
		self::_getConnection(get_class($this))->deleteRow($deleteInfo['sql'], $deleteInfo['params']);
		$this->bound = false;
		$this->scalars = array();
		$this->persisted = false;
	}

	//
	//	end of scalar handlers
	//


	//
	//	vector handlers
	//

	private function addRelationship($name, $relationship)
	{
		$this->relationships[$name] = $relationship;
	}

	private function hasRelationship($name)
	{
		return isset($this->relationships[$name]) ? true : false;
	}

	private function getRelationshipInfo($name)
	{
		return $this->relationships[$name]->getInfo();
	}

	protected function hasMany($name, $params = array())
	{
		if(isset($params['through']) && $params['through'])
			$this->addRelationship($name, new DbRelationshipHasManyThrough($name, $params, $this));
		else
			$this->addRelationship($name, new DbRelationshipHasMany($name, $params, $this));
	}

	protected function hasOne($name, $params = array())
	{
		$this->addRelationship($name, new DbRelationshipHasOne($name, $params, $this));
	}

	protected function belongsTo($name, $params = array())
	{
		$this->addRelationship($name, new DbRelationshipBelongsTo($name, $params, $this));
	}

	protected function fieldOptions($name, $params = array())
	{
		$this->addRelationship($name, new DbRelationshipOptions($name, $params, $this));
	}
	
	public function getFieldOptions($field)
	{
		foreach($this->relationships as $thisRelationship)
			if($thisRelationship instanceof DbRelationshipOptions && $thisRelationship->isTiedToField($field))
				return $thisRelationship;
		
		return false;
	}

	//
	//	end vector handlers
	//

	//
	//	begin magic functions
	//

	/**
	 * Automatic getter: maps unknown variables to database fields
	 *
	 * @param string $varname Name of the database field to get the value of
	 * @return mixed Value of the given database field
	 */
	function __get($varname)
	{
		if($this->hasRelationship($varname))
			return $this->getRelationshipInfo($varname);

		return $this->getScalar($varname);
	}

	/**
	 * Automatic setter: maps unknown variables to database fields
	 *
	 * @param string $varname Name of the database field to set the value of  
	 * @param mixed $value New value for the given database field
	 */
	function __set($varname, $value)
	{
		$this->setScalar($varname, $value);
	}

	//
	//	end magic functions
	//

	//
	//	begin iterator functions
	//

	/**
	 * Resets the internal pointer to the first column
	 *
	 */
	public function rewind()
	{
		reset($this->scalars);
	}

	/**
	 * Returns the value of the column that the internal pointer is at
	 *
	 * @return mixed
	 */
	public function current()
	{
		$var = current($this->scalars);
		return $var;
	}

	/**
	 * returns the name of the column the internal pointer is at
	 *
	 * @return string Column Name
	 */
	public function key()
	{
		$var = key($this->scalars);
		return $var;
	}

	/**
	 * Moves the internal pointer to the next column and returns the value of that column
	 *
	 * @return mixed Value of the next column
	 */
	public function next()
	{
		$var = next($this->scalars);
		return $var;
	}

	/**
	 * Returns true if this DbObject is successfully bound to a row in the database
	 *
	 * @return boolean True if the object is bound to a row in the database
	 */
	public function valid()
	{
		$var = $this->current() !== false;
		return $var;
	}

	//
	//	end iterator functions
	//


	//
	//	static methods
	//

	/**
	 * Returns the name of the default connection to be used with this DbObject (override in child class to default to a different connection)
	 *
	 * @param string $className Name of the DbObject to get the default connection name for
	 * @return string Name of the default database connection for this object
	 */
	static private function _getConnectionName($className)
	{
		return 'default';
	}

	/**
	 * Static method to return the database connection associated with a given DbObject
	 *
	 * @param string $className Name of the DbObject to retreive the default connection of
	 * @return DbConnection object
	 */
	static private function _getConnection($className)
	{
		return DbModule::getConnection(call_user_func(array($className, '_getConnectionName'), $className));
	}

	/**
	 * Returns the name of the SQL table based on the name of the DbObject class
	 *
	 * @param string $className
	 * @return string Name of the SQL table to link to
	 */
	static public function _getTableName($className)
	{
		//	work around lack of "late static binding"
		$dummy = new $className();
		return $dummy->getTableName();
	}

	/**
	 * Static method creates a new DbObject by name.  There must be a class that inherits from DbObject of the name "className" for this to work.
	 *
	 * @param string $className Name of the DbObject class to use
	 * @param array $values Associative array of $fieldName => $value to store in the table
	 * @return DbObject
	 */
	static public function _create($className, $values)
	{
		$object = new $className($values);
		$object->save();
		return $object;
	}

	/**
	 * Inserts a row into the table associated with the given DbObject class
	 *
	 * @param string $className Name of the DbObject class
	 * @param assoc_array $values $field => $value array to be inserted into the database (must contain all required fields or a SQL error will be generated)
	 */
	static public function _insert($className, $values)
	{
		self::_getConnection($className)->insertArray(self::_getTableName($className), $values, false);
	}

	/**
	 * Returns an array of DbObjects each representing a row in the database returned by the given SQL statement
	 *
	 * @param string $className Name of the DbObject class to retreive
	 * @param string $sql SQL select statement to use to search
	 * @param assoc_array $params $param => $value array with parameters to be substituted into the SQL statement
	 * @return array of DbObject
	 */
	static public function _findBySql($className, $sql, $params)
	{
		$res = self::_getConnection($className)->query($sql, $params);

		if(!$res->valid())
			return array();

		$objects = array();
		for($row = $res->current(); $res->valid(); $row = $res->next())
		{
			$objects[] = new $className($row);
		}

		return $objects;
	}

	/**
	 * Returns an array of DbObjects each representing a row in the database returned by selecting on the DbObject specified with the given WHERE clause
	 *
	 * @param string $className Name of the DbObject class to retreive
	 * @param string $where SQL "where" clause (minus the "where " at the beginning) to select on
	 * @param assoc_array $params $param => $value array with parameters to be substituted into the WHERE clause
	 * @return array of DbObject
	 */
	static public function _findByWhere($className, $where, $params)
	{
		$tableName = DbObject::_getTableName($className);
		return self::_findBySql($className, "select * from $tableName where $where", $params);
	}

	/**
	 * Searches the database and returns an array of DbObjects each representing a record in the resultset
	 *
	 * @param string $className Name of the DbObject class to retreive
	 * @param assoc_array $conditions $field => $value array of conditions to search on (currently only "=" operator is supported)
	 * @param assoc_array $params $param => $value array of parameters to be passed to generateSelectInfo
	 * @return array of DbObject (s)
	 */
	
	static public function _find($className, $conditions = NULL, $params = NULL)
	{
		$tableName = DbObject::_getTableName($className);
		$selectInfo = self::_getConnection($className)->generateSelectInfo($tableName, '*', $conditions, $params);
		return self::_findBySql($className, $selectInfo['sql'], $selectInfo['params']);
	}

	/**
	 * Retrieve one object from the database and map it to an object.  Throws an error if more than one row is returned.
	 *
	 * @param string $className The name of the class corresponding to the table in the database
	 * @param array $conditions Key value pair for the fields you want to look up
	 * @return DbObject
	 */
	static public function _findOne($className, $conditions = NULL)
	{
		$a = DbObject::_find($className, $conditions);
		if(!$a)
			return false;

		assert(is_array($a));
		assert(count($a) == 1);

		return current($a);
	}

	/**
	 * Retrieves a DbObject from the given table with a row in it, creating the row if neccesary
	 *
	 * @param string $className name of the DbObject class to return an instance of
	 * @param assoc_array $conditions $field => $value for generating the where clause to
	 * @return DbObject
	 */
	static public function _getOne($className, $conditions = NULL)
	{
		$tableName = DbObject::_getTableName($className);
		$row = self::_getConnection($className)->selsertRow($tableName, "*", $conditions);
		return new $className($row);
	}

	//
	//	end static methods
	//
}
