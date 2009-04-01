<?php
/*
Renderable Class File

@package Sandstone
@subpackage Application
*/

class AJAXprocessor extends Renderable
{

	protected $_target;
	protected $_method;

	protected $_eventParameters;

	protected $_page;

	public function __construct($Page, $Target, $Method, $EventParameters)
	{

		parent::__construct();

		$this->_page = $Page;
		$this->_target = $Target;
		$this->_method = $Method;
		$this->_eventParameters = $EventParameters;

		$this->_template->RequestFileType = "ajax";
//		$this->_template->IsMasterLayoutUsed = true;

	}

	/*
	Page property

	@return PageObject
	 */
	public function getPage()
	{
		return $this->_page;
	}

	/*
	Target property

	@return object
	 */
	public function getTarget()
	{
		return $this->_target;
	}

	/*
	Method property

	@return string
	 */
	public function getMethod()
	{
		return $this->_method;
	}

	/*
	EventParameters property

	@return array
	@param array $Value
	 */
	public function getEventParameters()
	{
		return $this->_eventParameters;
	}

	public function setEventParameters($Value)
	{
		$this->_eventParameters = $Value;
	}

	public function Render()
	{

		if (is_set($this->_target) > 0 && strlen($this->_method) > 0)
		{

			$methodFunctionName = "AJAX_{$this->_method}";

			if (method_exists($this->_target, $methodFunctionName))
			{

				//Make the actual method call
				try
				{
					$this->_target->$methodFunctionName($this);
				}
                catch (Exception $e)
				{
					$this->_template->FileName = "error";

					$errorMessage = $e->__toString();

					$search= Array("'", "\t", "\n", chr(10), chr(13));
					$replace = Array("\"", " ", " ", " ", " ");

					$errorMessage = str_replace($search, $replace, $errorMessage);

					$this->_template->ErrorMessage = $errorMessage;
				}
			}
			else
			{
				$this->_template->FileName = "error";
				$this->_template->ErrorMessage = "Method Not Found";
			}
		}
		else
		{
			$this->_template->FileName = "error";
			$this->_template->ErrorMessage = "No Target or No Method Set";
		}

		//Render the template
		$returnValue = parent::Render();

		return $returnValue;
	}

}
?>