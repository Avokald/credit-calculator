<?php

include('./Column.php');

Class Table {
	private $columns = [];
	private $name = '';
	private $creation_query = '';
	private $foreign_key = '';
	private $index = '';
	private $insertion_query = '';
	private $connection = null;

	public function __construct($conn, string $table_name, Closure $myfunc) {
		$this->connection = $conn;
		$this->name = $table_name;
		call_user_func($myfunc, $this);
    	$this->makeCreationQuery();
		return $this;
	}
	
	public function createColumn($name) {
		$this->columns["{$name}"] = new Column;
		return $this->columns["{$name}"]->setName($name);
	}

	public function getColumn($column_name) {
		return $this->columns["{$column_name}"];
	}

	public function getColumns() {
		return $this->columns;
	}

	public function makeCreationQuery() {
		$this->creation_query = "create table {$this->name} (";
		$this->creation_query .= "{$this->foreign_key} ";
		$this->creation_query .= "{$this->index} ";

		foreach(array_slice($this->columns, 0, count($this->columns) - 1) as $name => $column) {
			$this->creation_query .= "{$column->createQuery()}, ";
		}
		
		$this->creation_query .= end($this->columns)->createQuery();
		$this->creation_query .= '); ';
		return $this->creation_query;
	}

	public function setIndex($column_name) {
		$this->index .= "index({$column_name}),";
	}

	public function setForeignKey($column_name, $references_table, $references_column) {
		$this->foreign_key .= "foreign key ({$column_name}) references {$references_table}({$references_column}),";
	}

	public function initializeTable() {
		$this->connection->exec($this->creation_query);
	}


    // https://stackoverflow.com/questions/60174/how-can-i-prevent-sql-injection-in-php
	public function insertValues($values) {
		$this->insertion_query = "insert into {$this->name} (";
		$column_names = '';
		$column_values = 'values (';

		foreach (array_slice(array_keys($values), 0, count($values) - 1) as $key) {
			$column_names .= "{$key}, ";
			$column_values .= ":{$key}, ";
		}
		$last_value = end($values);
		$last_key = key($values);

		$column_names .= "{$last_key}) ";
		$column_values .= ":{$last_key}); ";
		$this->insertion_query .= $column_names . $column_values;
		$prepared_insertion_query = $this->connection->prepare($this->insertion_query);

		$prepared_insertion_query->execute($values);
		return;
	}

	public function select($values = ['*', ]) {
		$this->selection_query = 'select ';
		foreach (array_slice($values, 0, count($values) - 1) as $value) {
			$this->selection_query .= "{$value}, ";
		}
		$this->selection_query .= end($values) . ' ';
		return $this;
	}

	public function from($values = []) {
		array_push($values, $this->name);
		$this->selection_query .= "from ";
		foreach (array_slice($values, 0, count($values) - 1) as $value) {
			$this->selection_query .= "{$value}, ";
		}
		$this->selection_query .= end($values) . ' ';
		return $this;
	}

	public function where($values = []) {
		$this->selection_query .= "where ";
		foreach (array_slice($values, 0, count($values) - 1) as $value) {
			$this->selection_query .= "{$value}, ";
		}
		$this->selection_query .= end($values) . ' ';
		return $this;
	}

	public function getSelection() {
		return $this->connection->query($this->selection_query)->fetchAll();
	}

	public function __toString() {
		return $this->creation_query;
	}
}

