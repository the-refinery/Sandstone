<?php

class Controller extends Component
{
	protected $_entity;

	public function __construct()
	{
		$controllerClass = get_class($this);
		$entityName = str_replace('Controller', '', $controllerClass);

		if (class_exists($entityName))
		{
			$this->_entity = new $entityName();
		}
	}

	public function getEntityType()
	{
		return strtolower(get_class($this->_entity));
	}

	public function getIsEntityBased()
	{
		return isset($this->_entity);
	}

	public function getPrimaryKeyField()
	{
		return $this->EntityType . "id";
	}
}
