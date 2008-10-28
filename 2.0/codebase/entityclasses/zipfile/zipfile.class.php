<?php
/**
 * ZipFile Class
 * 
 * @package Sandstone
 * @subpackage ZipFile
 * 
 * @author Josh Walsh <jwalsh@designinginteractive.com>
 * @author Dave Goerlich <dgoerlich@designinginteractive.com>
 * 
 * @copyright 2007 Designing Interactive
 * 
 * 
 */

class ZipFile extends Module 
{

	protected $_fileName;	
	
	protected $_compressedData = array();
	protected $_centralDirectory = array(); // central directory   
	protected $_endOfCentralDirectory = "\x50\x4b\x05\x06\x00\x00\x00\x00"; //end of Central directory record
	protected $_oldOffset = 0;
	
	protected $_zipFileContents;

	public function __construct($FileSpec = null)
	{
		if (is_set($FileSpec))
		{
			$this->Load($FileSpec);
		}
		else 
		{
			$now = new Date();
			$this->_fileName = $now->FormatDate('Y-m-d-His') . ".zip";
		}
	}
	
	/**
	 * FileName property
	 * 
	 * @return string
	 * 
	 * @param string $Value
	 */
	public function getFileName()
	{
		return $this->_fileName;
	}
	
	public function setFileName($Value)
	{
		if (strlen($Value) > 0)
		{
			if (strtolower(substr($Value, -4, 4)) != ".zip")
			{
				$Value .= ".zip";
			}

			$this->_fileName = $Value;		
		}
	}
	
	public function Load($FileSpec)
	{
		if (file_exists($FileSpec))
		{

			$this->_compressedData = array();
			$this->_centralDirectory = array();   
			$this->_oldOffset = 0;			
			
			$this->_zipFileContents = file_get_contents($FileSpec);
			
			$this->_fileName = substr($FileSpec, strrpos($FileSpec, "/") + 1);
			
			$returnValue = true;
		}
		else 
		{
			$returnValue = false;
		}
		
		$this->_isLoaded = $returnValue;
		
		return $returnValue;
	}
	
	public function Save($FileSpec) 
	{
	
		//Only allow if working with a new file
		if ($this->_isLoaded == false)
		{
			if (is_set($this->_zipFileContents) == false)
			{
				$this->BuildZipFileContents();
			}
			
			$fileHandle = fopen($FileSpec, "wb");
			fwrite($fileHandle, $this->_zipFileContents);
			fclose($fileHandle);
			
			$this->_fileName = substr($FileSpec, strrpos($FileSpec, "/") + 1);
			
			$returnValue = true;
		}
		else 
		{
			$returnValue = false;
		}

		return $returnValue;
		
	
	}
	
	public function AddDirectory($DirectoryName) 
	{
		
		//Only allow this if we are working with a new file.
		if ($this->_isLoaded == false)
		{
			$DirectoryName = str_replace("\\", "/", $DirectoryName);  
			
			$feedArrayRow = "\x50\x4b\x03\x04";
			$feedArrayRow .= "\x0a\x00";    
			$feedArrayRow .= "\x00\x00";    
			$feedArrayRow .= "\x00\x00";    
			$feedArrayRow .= "\x00\x00\x00\x00";
			
			$feedArrayRow .= pack("V",0);
			$feedArrayRow .= pack("V",0);
			$feedArrayRow .= pack("V",0);
			$feedArrayRow .= pack("v", strlen($DirectoryName) );
			$feedArrayRow .= pack("v", 0 );
			$feedArrayRow .= $DirectoryName;  
			
			$feedArrayRow .= pack("V",0);
			$feedArrayRow .= pack("V",0);
			$feedArrayRow .= pack("V",0);
			
			$this->_compressedData[] = $feedArrayRow;
			
			$newOffset = strlen(implode("", $this->_compressedData));
			
			$addCentralRecord = "\x50\x4b\x01\x02";
			$addCentralRecord .="\x00\x00";    
			$addCentralRecord .="\x0a\x00";    
			$addCentralRecord .="\x00\x00";    
			$addCentralRecord .="\x00\x00";    
			$addCentralRecord .="\x00\x00\x00\x00";
			$addCentralRecord .= pack("V",0);
			$addCentralRecord .= pack("V",0);
			$addCentralRecord .= pack("V",0);
			$addCentralRecord .= pack("v", strlen($DirectoryName) );
			$addCentralRecord .= pack("v", 0 );
			$addCentralRecord .= pack("v", 0 );
			$addCentralRecord .= pack("v", 0 );
			$addCentralRecord .= pack("v", 0 );
			$ext = "\x00\x00\x10\x00";
			$ext = "\xff\xff\xff\xff";  
			$addCentralRecord .= pack("V", 16 );
			
			$addCentralRecord .= pack("V", $this->_oldOffset);
			$this->_oldOffset = $newOffset;
			
			$addCentralRecord .= $DirectoryName;  
			
			$this->_centralDirectory[] = $addCentralRecord;  		
				
			$returnValue = true;
		}
		else 
		{
			$returnValue = false;
		}

		return $returnValue;

	}    
	
	public function AddFile($Data, $DirectoryName)   
	{
			//Only allow this if we are working with a new file.
		if ($this->_isLoaded == false)
		{
			$DirectoryName = str_replace("\\", "/", $DirectoryName);  
			
			$feedArrayRow = "\x50\x4b\x03\x04";
			$feedArrayRow .= "\x14\x00";    
			$feedArrayRow .= "\x00\x00";    
			$feedArrayRow .= "\x08\x00";    
			$feedArrayRow .= "\x00\x00\x00\x00";
			
			$uncompressedLength = strlen($Data);  
			$compression = crc32($Data);  
			$gzCompressedData = gzcompress($Data);  
			$gzCompressedData = substr( substr($gzCompressedData, 0, strlen($gzCompressedData) - 4), 2);
			$compressedLength = strlen($gzCompressedData);  
			$feedArrayRow .= pack("V",$compression);
			$feedArrayRow .= pack("V",$compressedLength);
			$feedArrayRow .= pack("V",$uncompressedLength);
			$feedArrayRow .= pack("v", strlen($DirectoryName) );
			$feedArrayRow .= pack("v", 0 );
			$feedArrayRow .= $DirectoryName;  
			
			$feedArrayRow .= $gzCompressedData;  
			
			$feedArrayRow .= pack("V",$compression);
			$feedArrayRow .= pack("V",$compressedLength);
			$feedArrayRow .= pack("V",$uncompressedLength);
			
			$this->_compressedData[] = $feedArrayRow;
			
			$newOffset = strlen(implode("", $this->_compressedData));
			
			$addCentralRecord = "\x50\x4b\x01\x02";
			$addCentralRecord .="\x00\x00";    
			$addCentralRecord .="\x14\x00";    
			$addCentralRecord .="\x00\x00";    
			$addCentralRecord .="\x08\x00";    
			$addCentralRecord .="\x00\x00\x00\x00";
			$addCentralRecord .= pack("V",$compression);
			$addCentralRecord .= pack("V",$compressedLength);
			$addCentralRecord .= pack("V",$uncompressedLength);
			$addCentralRecord .= pack("v", strlen($DirectoryName) );
			$addCentralRecord .= pack("v", 0 );
			$addCentralRecord .= pack("v", 0 );
			$addCentralRecord .= pack("v", 0 );
			$addCentralRecord .= pack("v", 0 );
			$addCentralRecord .= pack("V", 32 );
			
			$addCentralRecord .= pack("V", $this->_oldOffset);
			$this->_oldOffset = $newOffset;
			
			$addCentralRecord .= $DirectoryName;  
			
			$this->_centralDirectory[] = $addCentralRecord;  
			
			$returnValue = true;
		}
		else 
		{
			$returnValue = false;
		}

		return $returnValue;
	}
	
	public function Download()
	{
							
		if (is_set($this->_zipFileContents) == false)
		{
			$this->BuildZipFileContents();
		}
		
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false);
		header("Content-Type: application/zip");
		header("Content-Disposition: attachment; filename={$this->_fileName};" );
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".strlen($this->_zipFileContents));
		echo $this->_zipFileContents;

	}
	
	protected function BuildZipFileContents()
	{
		$data = implode("", $this->_compressedData);  
		$controlDirectory = implode("", $this->_centralDirectory); 
		
		$this->_zipFileContents = $data;
		$this->_zipFileContents .= $controlDirectory;
		$this->_zipFileContents .= $this->_endOfCentralDirectory;
		$this->_zipFileContents .= pack("v", sizeof($this->_centralDirectory));
		$this->_zipFileContents .= pack("v", sizeof($this->_centralDirectory));     
		$this->_zipFileContents .= pack("V", strlen($controlDirectory));     
		$this->_zipFileContents .= pack("V", strlen($data));    
		$this->_zipFileContents .= "\x00\x00";
	}
     
}

?>