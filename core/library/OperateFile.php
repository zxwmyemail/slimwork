<?php
namespace core\library;
/***********************************************************************************************
 文件操作类
     实现了文件的建立，写入，删除，修改，复制，移动，创建目录，删除目录、列出目录里的文件等功能
 
 @author    iProg
 @version   1.0
 @date      2015-05-28
************************************************************************************************/

class OperateFile {

        private $fileList = array(); 

        /*-------------------------------------------------------------------------------------------
	| 浏览目录，获取文件，目录
	|--------------------------------------------------------------------------------------------
	| @param string $dir
	| @param string $type  1为选取所有目录，2为选取所有文件，3为选取目录和文件
	-------------------------------------------------------------------------------------------*/ 
	public function scanAllFiles ( $dir, $type=3 )
        {
            $this->fileList = array();
            $handle = opendir($dir);
            if ( $handle ){
                while ( ( $file = readdir ( $handle ) ) !== false ){
                    if ( $file != '.' && $file != '..'){
                        $cur_path = $dir . DIRECTORY_SEPARATOR . $file;

                        switch ($type) {
                    	    case 1:
                    		if ( is_dir ( $cur_path ) ){
		                    $this->fileList[$file] = $cur_path;
		                    $this->scanAllFiles ( $cur_path, $type );
		                }
                    		break;
                    	    case 2:
                    		if ( is_dir ( $cur_path ) ){
		                    $this->scanAllFiles ( $cur_path, $type );
		                }else{
		                    $this->fileList['file'][$file] = $cur_path;
		                }
                    		break;
                    	    case 3:
                    	    default:
                    		if ( is_dir ( $cur_path ) ){
		                    $this->fileList['dir'][$file] = $cur_path;
		                    $this->scanAllFiles ( $cur_path, $type );
		                }else{
		                    $this->fileList['file'][$file] = $cur_path;
		                }
                    		break;
                        }
                    }
                }
                closedir($handle);
            }

            return $this->fileList;
        }

 	/*-------------------------------------------------------------------------------------------
	| 创建目录
	|--------------------------------------------------------------------------------------------
	| @param string $dir
	-------------------------------------------------------------------------------------------*/ 
	public function createDir($dir) 
	{
	        $dir = str_replace('', '/', $dir);
	        $aimDir = '';
	        $dirArr = explode('/', $dir);
	
	        foreach ($dirArr as $d) {
	            $aimDir .= $d . '/';
	            if (!file_exists($aimDir)) {
	            	mkdir($aimDir);
			chmod($aimDir, 0777);
	            }
	        }
	        return;
	}

        /*-------------------------------------------------------------------------------------------
	| 删除目录
	|--------------------------------------------------------------------------------------------
	| @param string $dir
	-------------------------------------------------------------------------------------------*/ 
	public function deleteDir($dir) 
	{
	        $dir = str_replace('', '/', $dir);
	        $dir = substr($dir, -1) == '/' ? $dir : $dir . '/';
	        if (!is_dir($dir)) {
	            return false;
	        }
	        $dirHandle = opendir($dir);
	        while (false !== ($file = readdir($dirHandle))) {
	            if ($file == '.' || $file == '..') {
	                continue;
	            }
	            if (!is_dir($dir . $file)) {
	                $this->deleteFile($dir, $file);
	            } else {
	                $this->deleteDir($dir . $file);
	            }
	        }
	        closedir($dirHandle);
	        return rmdir($dir);
	}

        /*-------------------------------------------------------------------------------------------
	| 本方法用来在path目录下创建name文件
	|--------------------------------------------------------------------------------------------
	| @param string path         路径名称
	| @param string name         文件名字
	| @param string isOverWrite  是否覆盖源文件
	--------------------------------------------------------------------------------------------*/ 
	public function createFile($path, $name, $isOverWrite = false) 
	{
		$path = substr($path, -1) == '/' ? $path : $path . '/';
		$filename = $path . $name;

	        if (file_exists($filename) && $isOverWrite == false) {
	            return false;
	        } elseif (file_exists($filename) && $isOverWrite == true) {
	            $this->deleteFile($filename);
	        }
	
	        $this->createDir($path);
	        touch($filename);
	        return true;
    	}

	/*------------------------------------------------------------------------------------------
	| 删除文件
	|-------------------------------------------------------------------------------------------
	| @param string path         路径后面别忘了加"/"
	| @param string name         文件名字
	|
	| @return boolean
	------------------------------------------------------------------------------------------*/
	function deleteFile($path, $name) 
	{
		$path = substr($path, -1) == '/' ? $path : $path . '/';
	    	$filename = $path . $name;
	
	        if (file_exists($filename)) {
	            unlink($filename);
	            return true;
	        } else {
	            return false;
	        }
	}

        /*-----------------------------------------------------------------------------------------
	| 获取文件内容
	|------------------------------------------------------------------------------------------
	| @param string path        文件路径
	| @param string name        文件名字
	| @return string content    文件内容
	-----------------------------------------------------------------------------------------*/
	public function readFile($path, $name) 
	{
		$path = substr($path, -1) == '/' ? $path : $path . '/';
		return file_get_contents($path . $name);
	}

	/*-----------------------------------------------------------------------------------------
	| 本方法用来写文件，向path路径下name文件写入content内容
	|------------------------------------------------------------------------------------------
	| @param string path       文件路径
	| @param string name       文件名字
	| @param string content    文件内容
	| @param bool   bool       文件写入选项，1为尾部追加，2为替换原内容
	-----------------------------------------------------------------------------------------*/
	public function writeFile($path, $name, $content, $bool=2) 
	{
		$path = substr($path, -1) == '/' ? $path : $path . '/';
		$filename = $path . $name;

		$flag = false;
		if ($bool == 1) {
			$flag= file_put_contents($filename, $content);
		} elseif ($bool == 2) {
			$flag= file_put_contents($filename, $content, FILE_APPEND);
		}

		return $flag;
	}

	/*-----------------------------------------------------------------------------------------
	| 复制name文件从spath到dpath
	|------------------------------------------------------------------------------------------
	| @param string name         文件名
	| @param string spath        源文件目录
	| @param string dpath        目的目录
	| @param string isOverWrite  是否覆盖源文件
	-----------------------------------------------------------------------------------------*/
	public function copyFile($name, $spath, $dpath, $isOverWrite = false) 
	{
		$spath = substr($spath, -1) == '/' ? $spath : $spath . '/';
		$dpath = substr($dpath, -1) == '/' ? $dpath : $dpath . '/';
		$filename = $spath . $name;
		$aimUrl   = $dpath . $name;
	        if (!file_exists($filename)) {
	            return false;
	        }
	        if (file_exists($aimUrl) && $isOverWrite = false) {
	            return false;
	        } elseif (file_exists($aimUrl) && $isOverWrite = true) {
	            $this->deleteFile($aimUrl);
	        }
	
	        $this->createDir($dpath);
	        copy($filename, $aimUrl);
	        return true;
	}

	/*-----------------------------------------------------------------------------------------
	| 移动name文件从spath到dpath
	|------------------------------------------------------------------------------------------
	| @param string name         文件名
	| @param string spath        源文件目录
	| @param string dpath        目的目录
	| @param string isOverWrite  是否覆盖源文件
	-----------------------------------------------------------------------------------------*/
	public function moveFile($name, $spath, $dpath, $isOverWrite = false) 
	{
		$spath = substr($spath, -1) == '/' ? $spath : $spath . '/';
		$dpath = substr($dpath, -1) == '/' ? $dpath : $dpath . '/';
		$filename = $spath . $name;
		$aimUrl   = $dpath . $name;
        	if (!file_exists($filename)) {
            		return false;
        	}
	        if (file_exists($aimUrl) && $isOverWrite = false) {
	            return false;
	        } elseif (file_exists($aimUrl) && $isOverWrite = true) {
	            $this->deleteFile($aimUrl);
	        }

	        $this->createDir($dpath);
	        rename($filename, $aimUrl);
	        return true;
    	}

	/*----------------------------------------------------------------------------------------
	| 把filename文件重命名为newname文件
	|-----------------------------------------------------------------------------------------
	| @param string_type path
	| @param string_type oldname
	| @param string_type newname
	----------------------------------------------------------------------------------------*/
	public function renameFile($path, $oldname, $newname) 
	{ 
		$path = substr($path, -1) == '/' ? $path : $path . '/';
		$oldFileName = $path.$oldname;
		$newFileName = $path.$newname;

		$result = false;
		if (file_exists($oldFileName)) {
			$result = rename($oldFileName,$newFileName);	
		}
	}

	/*---------------------------------------------------------------------------------------
	| 复制文件夹
        |----------------------------------------------------------------------------------------
	| @param string $oldDir
	| @param string $aimDir
	| @param boolean $overWrite 该参数控制是否覆盖原文件
	| @return boolean
	---------------------------------------------------------------------------------------*/
	public function copyDir($oldDir, $aimDir, $overWrite = false) 
	{
	        $aimDir = str_replace('', '/', $aimDir);
	        $aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir . '/';
	        $oldDir = str_replace('', '/', $oldDir);
	        $oldDir = substr($oldDir, -1) == '/' ? $oldDir : $oldDir . '/';
	        if (!is_dir($oldDir)) {
	            return false;
	        }
	        if (!file_exists($aimDir)) {
	            $this->createDir($aimDir);
	        }
	        $dirHandle = opendir($oldDir);
	        while (false !== ($file = readdir($dirHandle))) {
	            if ($file == '.' || $file == '..') {
	                continue;
	            }
	            if (!is_dir($oldDir . $file)) {
	                $this->copyFile($file, $oldDir, $aimDir, $overWrite);
	            } else {
	                $this->copyDir($oldDir . $file, $aimDir . $file, $overWrite);
	            }
	        }
	        return closedir($dirHandle);
	}

    	/*---------------------------------------------------------------------------------------
    	| 移动文件夹
    	|----------------------------------------------------------------------------------------
    	| @param string $oldDir
    	| @param string $aimDir
    	| @param boolean $overWrite 该参数控制是否覆盖原文件
    	| @return boolean
    	---------------------------------------------------------------------------------------*/
    	public function moveDir($oldDir, $aimDir, $overWrite = false) 
    	{
        	$aimDir = str_replace('', '/', $aimDir);
        	$aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir . '/';
        	$oldDir = str_replace('', '/', $oldDir);
        	$oldDir = substr($oldDir, -1) == '/' ? $oldDir : $oldDir . '/';
        	if (!is_dir($oldDir)) {
            		return false;
        	}
        	if (!file_exists($aimDir)) {
            		$this->createDir($aimDir);
        	}
        	@ $dirHandle = opendir($oldDir);
        	if (!$dirHandle) {
            		return false;
        	}
	        while (false !== ($file = readdir($dirHandle))) {
	            if ($file == '.' || $file == '..') {
	                continue;
	            }
	            if (!is_dir($oldDir . $file)) {
	                $this->moveFile($file, $oldDir, $aimDir, $overWrite);
	            } else {
	                $this->moveDir($oldDir . $file, $aimDir . $file, $overWrite);
	            }
	        }
	        
	        rmdir($oldDir);
	        return closedir($dirHandle);
        }

}

?>
