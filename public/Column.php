<?php

Class Column {
	private $nullable = 'not null';
	private $name = '';
	private $type = '';
	private $primary_key = '';
	private $unique = '';
	private $foreign_key = '';
	private $query = '';
	private $auto_increment = '';
	private $timestamp = '';


	public function __construct() {
		return $this;
	}

	public function setName($name) {
		$this->name = $name;
		return $this;
	} 

	public function getName() {
		return $this->name;
	}

	public function getQuery() {
		return $this->query;
	}

	public function setType($type) {
		$this->type = $type;
		return $this;
	}

	public function setNullable() {
		$this->nullable = '';
		return $this;
	}

	public function setUnique() {
		$this->unique = 'unique';
		return $this;
	}

	public function setPrimaryKey() {
		$this->primary_key = 'primary key';
		return $this;
	}

	public function setDefaultTimestamp() {
		$this->timestamp = 'default current_timestamp';
		return $this;
	}

	public function setAutoIncrement() {
		$this->auto_increment = 'auto_increment';
		return $this;
	}

	public function createQuery() {
		$this->query = "{$this->name} {$this->type} {$this->nullable} {$this->unique} {$this->auto_increment} {$this->primary_key} {$this->timestamp}";
		return $this->query;
	}
} 
