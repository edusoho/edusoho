<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * @author peihong <jhqblxt@gmail.com> Dec 26, 2011
 * @link
 * @copyright
 * @license
 */

class PwHighlight {
	
	protected $color;
	protected $italic;
	protected $bold;
	protected $underline;
	
	public function setColor($color){
		if (!$color || !preg_match('/^#[0-9a-f]{3}([0-9a-f]{3})?$/i',$color)) return false;
		$this->color = strtoupper($color);
	}
	
	public function setBold($bold){
		$this->bold = intval($bold);
	}
	
	public function setItalic($italic){
		$this->italic = intval($italic);
	}
	
	public function setUnderline($underline){
		$this->underline = intval($underline);
	}
	
	//end set method
	
	/**
	 * 
	 * get highlight string for database saving
	 */
	public function getHighlight(){
		$highlight = '';
		$highlight = sprintf(
			'%s~%s~%s~%s',
			$this->color ? $this->color : '',
			$this->bold ? 1 : '',
			$this->italic ? 1 : '',
			$this->underline ? 1 : ''
		);
		return $highlight;
	}
	
	/**
	 * 
	 * explode highlight format to array
	 * 
	 * @param string $highlight
	 * @return array
	 */
	public function parseHighlight($highlight){
		$value = array();
		if (preg_match('/^(#[0-9a-f]{3}([0-9a-f]{3})?)?~(1?)~(1?)~(1?)$/i', $highlight, $m)){
			$value = array(
				'color' => $m[1],
				'bold'	=> $m[3],
				'italic'=> $m[4],
				'underline'=>$m[5]
			);
		}
		return $value;
	}
	
	/**
	 * 
	 * explode highlight string to CSS
	 * @param string $highlight
	 */
	public function getStyle($highlight){
		$hightlightArray = $this->parseHighlight($highlight);
		$styleString = '';
		foreach ($hightlightArray as $k=>$v) {
			if (!$v) continue;
			switch ($k) {
				case 'color':
					$styleString .= ';color:' . $v;
				break;
				case 'bold' :
					$styleString .= ';font-weight:bold';
				break;
				case 'italic':
					$styleString .= ';font-style:italic';
				break;
				case 'underline':
					$styleString .= ';text-decoration:underline';
				break;
			}
		}
		return ltrim($styleString,';');
	}
}