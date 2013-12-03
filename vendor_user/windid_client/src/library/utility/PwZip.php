<?php
defined('WEKIT_VERSION') || exit('Forbidden');

define('EOF_CENTRAL_DIRECTORY', 0x06054b50); //'end of central directory record'区块的标记
define('LOCAL_FILE_HEADER', 0x04034b50); //'Local file header'区块标记
define('CENTRAL_DIRECTORY', 0x02014b50); //'Central directory'区块标记

class PwZip {
	
	var $fileHeaderAndData = array();
	var $centralDirectory = array();
	var $localFileHeaderOffset = 0;
	var $fileHandle = '';
	
	/**
	 * 增加待压缩的文件
	 * @param $data string 待压缩的字符串
	 * @param $filename string 文件名
	 * @param $timestamp int 时间戳
	 * @return bool
	 */
	function addFile($data, $filename, $timestamp = 0){
		if (!$this->_checkZlib()) return false;
		
		list($modTime, $modDate) = $this->_getDosFormatTime($timestamp);
		$unCompressedSize = strlen($data);
		$crcValue = crc32($data);
		$compressedData = gzcompress($data);
		$compressedData = substr(substr($compressedData, 0, strlen($compressedData) - 4), 2); // crc problem
		$compressedSize = strlen($compressedData);
		$filenameLength = strlen($filename);
		
		$header    = pack('V', LOCAL_FILE_HEADER); 			// local file header signature
		$header   .= "\x14\x00";           					// version needed to extract
		$header   .= "\x00\x00";            				// general purpose bit flag  
		$header   .= "\x08\x00";           					// compression method, deflated used here
		$header   .= pack('vv', $modTime, $modDate);        // last mod file time, last mod file date
		$header   .= pack('V', $crcValue);       		 	// crc-32
		$header   .= pack('V', $compressedSize);       		// compressed size
		$header   .= pack('V', $unCompressedSize);       	// uncompressed size
		$header   .= pack('v', $filenameLength);       		// file name length
		$header   .= pack('v', 0);       					// extra field length
		$header   .= $filename;      						// filename
		$header   .= $compressedData;      					// file data
		$header   .= $this->_getDataDescriptor($crcValue, $compressedSize, $unCompressedSize); // Data descriptor
		$this->fileHeaderAndData[] = $header;
		//central directory
		$this->centralDirectory[] = $this->_getCentralDirectory($modTime, $modDate, $crcValue, $compressedSize, $unCompressedSize, $filenameLength, strlen($header), $filename);
		return true;
	}
	
	/**
	 * 返回压缩后的数据
	 * @return string 压缩后的数据
	 */
	function getCompressedFile(){
		$fileHeaderAndData = implode('', $this->fileHeaderAndData);
		$centralDirectory = implode('', $this->centralDirectory);
		
		$return = $fileHeaderAndData . $centralDirectory;
		$return .= pack('V', EOF_CENTRAL_DIRECTORY); 			// end of central dir signature
		$return .= pack('v', 0); 								// Number of this disk
		$return .= pack('v', 0); 								// Disk where central directory starts
		$return .= pack('v', count($this->centralDirectory)); 	// central directory on this disk
		$return .= pack('v', count($this->centralDirectory)); 	// total number of entries in the central directory
		$return .= pack('V', strlen($centralDirectory));        // size of central dir
		$return .= pack('V', strlen($fileHeaderAndData));       // offset to start of central dir
		$return .= "\x00\x00";                            		// .zip file comment length
		return $return;
	}
	
	/**
	 * 解压缩一个文件
	 * @param $file string 文件名
	 * @return array 解压缩后的数据，其中包括时间、文件名、数据
	 */
	function extract($file) {
		$extractedData = array();
		if (!$file || !is_file($file)) return false;
		$filesize = sprintf('%u', filesize($file));
		
		$this->fileHandle = fopen($file, 'rb');
		$fileData = fread($this->fileHandle, $filesize);
		
		$EofCentralDirData = $this->_findEOFCentralDirectoryRecord($filesize); //获取'End of central directory record'区块的数据
		if (!is_array($EofCentralDirData)) return false;
		$centralDirectoryHeaderOffset = $EofCentralDirData['centraldiroffset'];
		for ($i = 0; $i < $EofCentralDirData['totalentries']; $i++) {
			rewind($this->fileHandle);
			fseek($this->fileHandle, $centralDirectoryHeaderOffset);
			$centralDirectoryData = $this->_readCentralDirectoryData(); // 获取'Central directory' 区块数据
			$centralDirectoryHeaderOffset += 46 + $centralDirectoryData['filenamelength'] + $centralDirectoryData['extrafieldlength'] + $centralDirectoryData['commentlength'];
			if (!is_array($centralDirectoryData) || substr($centralDirectoryData['filename'], -1) == '/') continue;
			$data = $this->_readLocalFileHeaderAndData($centralDirectoryData); // 获取压缩的数据
			if (!$data) continue;
			$extractedData[$i] = array(
				'filename' => $centralDirectoryData['filename'],
				'timestamp' => $centralDirectoryData['time'],
				'data' => $data,
			);
		}
		fclose($this->fileHandle);
		return $extractedData;
	}
	
	/**
	 * 初始化
	 */
	function init() {
		$this->fileHeaderAndData = $this->centralDirectory = array();
		$this->localFileHeaderOffset = 0;
		return true;
	}
	
	/**
	 * 取得压缩数据中的'Local file header'区块跟压缩的数据
	 * @param $centralDirectoryData array 'Central directory' 区块数据
	 * @return array
	 */
	function _readLocalFileHeaderAndData($centralDirectoryData) {
		fseek($this->fileHandle, $centralDirectoryData['localheaderoffset']);
		$localFileHeaderSignature = unpack('Vsignature', fread($this->fileHandle, 4)); // 'Local file header' 区块的标记
		if ($localFileHeaderSignature['signature'] != 0x04034b50) return false;
		$localFileHeaderData = fread($this->fileHandle, 26); // 'Local file header' 除标记, file name, extra field 外的数据
		$localFileHeaderData = unpack('vextractversion/vflag/vcompressmethod/vmodtime/vmoddate/Vcrc/Vcompressedsize/Vuncompressedsize/vfilenamelength/vextrafieldlength', $localFileHeaderData);
		$localFileHeaderData['filenamelength'] && $localFileHeaderData['filename'] = fread($this->fileHandle, $localFileHeaderData['filenamelength']); //读取文件名
		$localFileHeaderData['extrafieldlength'] && $localFileHeaderData['extrafield'] = fread($this->fileHandle, $localFileHeaderData['extrafieldlength']); //读取extra field
		if (!$this->_checkLocalFileHeaderAndCentralDir($localFileHeaderData, $centralDirectoryData)) return false;
		
		if ($localFileHeaderData['flag'] & 1) return false; //文件加密过
		$compressedData = fread($this->fileHandle, $localFileHeaderData['compressedsize']);
		$data = $this->_unCompressData($compressedData, $localFileHeaderData['compressmethod']);
		
		if (crc32($data) != $localFileHeaderData['crc'] || strlen($data) != $localFileHeaderData['uncompressedsize']) return false; //crc32 校验不一致或长度不一致
		return $data;
	}
	
	/**
	 * 解压被压缩的数据
	 * @param $data string 被压缩的数据
	 * @param $compressMethod int 压缩的方式
	 * @return string 解压后的数据
	 */
	function _unCompressData($data, $compressMethod) { // 根据具体的压缩方式解压缩，目前仅支持deflate 压缩方式有deflate, deflate64, bzip2 等
		if (!$compressMethod) return $data;
		switch ($compressMethod) {
			case 8 : // compressed by deflate
				$data = gzinflate($data);
				break;
			default :
				return false;
				break;
		}
		return $data;
	}
	
	/**
	 * 校验 'Local file header' 跟 'Central directory'
	 * @param unknown_type $localFileHeaderData
	 * @param unknown_type $centralDirectoryData
	 * @return bool
	 */
	function _checkLocalFileHeaderAndCentralDir($localFileHeaderData, $centralDirectoryData) { 
		return true; //暂时不验证，有需要时可扩展
	}
	
	/**
	 * 读取'Central directory' 区块数据
	 * @return string
	 */
	function _readCentralDirectoryData() {
		$centralDirectorySignature = unpack('Vsignature', fread($this->fileHandle, 4)); // 'Central directory' 区块的标记
		if ($centralDirectorySignature['signature'] != 0x02014b50) return false;
		$centralDirectoryData = fread($this->fileHandle, 42); // 'Central directory' 区块除标记, file name, extra field, file comment 外的数据
		$centralDirectoryData = unpack('vmadeversion/vextractversion/vflag/vcompressmethod/vmodtime/vmoddate/Vcrc/Vcompressedsize/Vuncompressedsize/vfilenamelength/vextrafieldlength/vcommentlength/vdiskstart/vinternal/Vexternal/Vlocalheaderoffset', $centralDirectoryData);
		$centralDirectoryData['filenamelength'] && $centralDirectoryData['filename'] = fread($this->fileHandle, $centralDirectoryData['filenamelength']); //读取文件名
		$centralDirectoryData['extrafieldlength'] && $centralDirectoryData['extrafield'] = fread($this->fileHandle, $centralDirectoryData['extrafieldlength']); //读取extra field
		$centralDirectoryData['commentlength'] && $centralDirectoryData['comment'] = fread($this->fileHandle, $centralDirectoryData['commentlength']); //读取 file comment
		$centralDirectoryData['time'] = $this->_recoverFromDosFormatTime($centralDirectoryData['modtime'], $centralDirectoryData['moddate']); //读取时间信息
		return $centralDirectoryData;
	}
	
	/**
	 * 读取'end of central directory record'区块数据
	 * @param $filesize int 文件大小
	 * @return string 
	 */
	function _findEOFCentralDirectoryRecord($filesize) {
		fseek($this->fileHandle, $filesize - 22); // 'End of central directory record' 一般在没有注释的情况下位于该位置
		$EofCentralDirSignature = unpack('Vsignature', fread($this->fileHandle, 4));
		if ($EofCentralDirSignature['signature'] != 0x06054b50) { // 'End of central directory record' 不在末尾22个字节的位置，即有注释的情况
			$maxLength = 65535 + 22; //'End of central directory record' 区块最大可能的长度，因为保存注释长度的区块的长度为2字节，2个字节最大可保存的长度是65535，即0xFFFF。22为'End of central directory record' 除去注释后的长度
			$maxLength > $filesize && $maxLength = $filesize; //最大不能超多整个文件的大小
			fseek($this->fileHandle, $filesize - $maxLength);
			$searchPos = ftell($this->fileHandle);
			while ($searchPos < $filesize) {
				fseek($this->fileHandle, $searchPos);
				$sigData = unpack('Vsignature', fread($this->fileHandle, 4));
				if ($sigData['signature'] == 0x06054b50) {
					break;
				}
				$searchPos++;
			}
		}
		$EofCentralDirData = unpack('vdisknum/vdiskstart/vcentraldirnum/vtotalentries/Vcentraldirsize/Vcentraldiroffset/vcommentlength', fread($this->fileHandle, 18)); // 'End of central directory record'区块除signature跟注释外的数据
		$EofCentralDirData['commentlength'] && $EofCentralDirData['comment'] = fread($this->fileHandle, $EofCentralDirData['commentlength']);
		return $EofCentralDirData;
	}
	
	/**
	 * 检查PHP zlib扩展有没有载入
	 * @return bool
	 */
	function _checkZlib() {
		return (extension_loaded('zlib') && function_exists('gzcompress')) ? true : false;
	}
	
	/**
	 * 组装 'Central directory' 区块数据
	 * @param $modTime
	 * @param $modDate
	 * @param $crc
	 * @param $compressedSize
	 * @param $unCompressedSize
	 * @param $filenameLength
	 * @param $fileHeaderLength
	 * @param $filename
	 * @return string
	 */
	function _getCentralDirectory($modTime, $modDate, $crc, $compressedSize, $unCompressedSize, $filenameLength, $fileHeaderLength, $filename) {
		$centralDirectory = pack('V', CENTRAL_DIRECTORY);			// central file header signature
		$centralDirectory .= "\x00\x00";							// version made by
		$centralDirectory .= "\x14\x00";							// version needed to extract
		$centralDirectory .= "\x00\x00";							// general purpose bit flag
		$centralDirectory .= "\x08\x00";							// compression method
		$centralDirectory .= pack('vv', $modTime, $modDate);		// last mod file time, last mod file date
		$centralDirectory .= pack('V', $crc);						// crc-32
		$centralDirectory .= pack('V', $compressedSize);			// compressed size
		$centralDirectory .= pack('V', $unCompressedSize);			// uncompressed size
		$centralDirectory .= pack('v', $filenameLength);			// file name length
		$centralDirectory .= pack('v', 0 );            				// extra field length
		$centralDirectory .= pack('v', 0 );             			// file comment length
		$centralDirectory .= pack('v', 0 );             			// disk number start
		$centralDirectory .= pack('v', 0 );             			// internal file attributes
		$centralDirectory .= pack('V', 32 );            			// external file attributes - 'archive' bit set
		$centralDirectory .= pack('V', $this->localFileHeaderOffset); 		// relative offset of local header
		$this->localFileHeaderOffset += $fileHeaderLength;
		$centralDirectory .= $filename;								// file name
		return $centralDirectory;
	}
	
	/**
	 * 组装 'Data descriptor' 区块数据
	 * @param $crc
	 * @param $compressedSize
	 * @param $unCompressedSize
	 * @return string
	 */
	function _getDataDescriptor($crc, $compressedSize, $unCompressedSize) {
		return '';	// return string only when bit 3 of the general purpose bit flag is set
		//return pack('VVV', $crc, $compressedSize, $unCompressedSize);
	}
	
	/**
	 * 格式化时间为DOS格式
	 * @param $timestamp
	 * @return array
	 */
	function _getDosFormatTime($timestamp = 0) {
		$timestamp = (int) $timestamp;
		$time = $timestamp === 0 ? getdate() : getdate($timestamp);
		if ($time['year'] < 1980) {
            $time['year']    = 1980;
            $time['mon']     = 1;
            $time['mday']    = 1;
            $time['hours']   = 0;
            $time['minutes'] = 0;
            $time['seconds'] = 0;
        }
		$modTime = ($time['hours'] << 11) + ($time['minutes'] << 5) + $time['seconds'] / 2;
		$modDate = (($time['year'] - 1980) << 9) + ($time['mon'] << 5) + $time['mday'];
		return array($modTime, $modDate);
	}
	
	/**
	 * 还原DOS格式的时间为时间戳
	 * @param $time
	 * @param $date
	 * @return int
	 */
	function _recoverFromDosFormatTime($time, $date) {
		$year = (($date & 0xFE00) >> 9) + 1980;
		$month = ($date & 0x01E0) >> 5;
		$day = $date & 0x001F;
		$hour = ($time & 0xF800) >> 11;
		$minutes = ($time & 0x07E0) >> 5;
		$seconds = ($time & 0x001F)*2;
		return mktime($hour, $minutes, $seconds, $month, $day, $year);
	}
}
?>