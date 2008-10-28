<?php

class ErrorPage extends BasePage
{
	protected $_isLoginRequired = false;
	protected $_requiredRoleIDs = Array();
	protected $_isTrafficLogged = false;

	protected function Load_Handler($EventParameters)
	{
		
		$returnValue = new EventResults();
		
		$exception = $EventParameters['Exception'];
		
		echo $this->GetPageHeader(get_class($exception));
		echo $exception;
		echo $this->GetPageFooter();
				
		$returnValue->Value = true;
		
		return $returnValue;
	}
	
	protected function GetPageHeader($ExceptionType = null)
	{
		if ($ExceptionType == null)
		{
			$ExceptionType = "Generic Exception";
		}
		
		$returnValue = '
			<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
				"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

			<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

				<title>' . $ExceptionType . '!</title>

				<style type="text/css" media="screen">
					body
					{
						background:#ccc;
						font-family: Helvetica Narrow, sans-serif;
					}

					#MainContent
					{
						margin:0 auto;
						width: 850px;
						background: #fff;
						border:2px solid #900;
						padding:5px;
					}

					h1,h2,h3
					{
						margin:0;
						padding:0;
					}

					h1
					{
						font-family:Palatino, serif;
					}

					h2
					{
						color:#333;
						margin-top:20px;
						font-weight:normal;
						margin-left:10px;
						margin-bottom:10px;
						font-size:30px;
					}

					h3
					{
						color:#666;
						margin-top:5px;
						font-weight:normal;
						font-size:16px;
						font-style: italic;
						margin-bottom: 30px;
						margin-left:10px;
					}
					
					#Header
					{
						background:#900;
						color:#fff;
						padding:10px;
					}

					.CallStack
					{
						border-left: 4px #900 solid;
						padding-left: 8px;
						background: #FFE;
						font-family: Lucidatypewriter, monospace;
						font-size: 12px;
						margin:10px;
					}

					table 
					{
						width:100%;
					}

					table tr th
					{
						text-align:left;
						font-size:18px;
						padding:3px;
						border-bottom:1px solid #333;
					}

					table tr td
					{
						border-bottom:1px #666 dashed;
						padding:6px;
					}

					.stackorder
					{
						width:20px;
						vertical-align: top;
					}

					.linenumber
					{
						font-weight: bold;
						width:60px;
						vertical-align: top;
					}

				</style>
			</head>

			<body>
				<div id="MainContent">
					<div id="Header">
						<h1>' . $ExceptionType. '!</h1>
					</div>
			';
			
		return $returnValue;
	}
	
	protected function GetPageFooter()
	{
		$returnValue .= '</div></body></html>';
		
		return $returnValue;
	}
	
}

?>