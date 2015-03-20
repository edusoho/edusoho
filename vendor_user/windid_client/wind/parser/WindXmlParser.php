<?php
Wind::import("WIND:utility.WindConvert");
/**
 * xml文件解析
 *
 * @author Qian Su <aoxue.1988.su.qian@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindXmlParser.php 3904 2013-01-08 07:01:26Z yishuo $
 * @package parser
 */
class WindXmlParser {
	
	/**
	 * @var string 节点名称
	 */
	const NAME = 'name';
	
	/**
	 * @var Domdocument DOM解析器
	 */
	private $dom = null;

	/**
	 * 初始化xml解析器
	 * 
	 * @param string $version xml版本
	 * @param string $encode  xml编码
	 * @return void
	 */
	public function __construct($version = '1.0', $encode = 'utf-8') {
		if (!class_exists('DOMDocument')) throw new WindException('[parser.WindXmlParser] DOMDocument is not exist.');
		$this->dom = new DOMDocument($version, $encode);
	}

	/**
	 * 解析xml文件
	 * 
	 * @param string $filename 待解析文件名
	 * @param int $option 解析选项,默认为0
	 * @return array
	 */
	public function parse($filename, $option = 0) {
		if (!is_file($filename)) return array();
		$this->dom->load($filename, $option);
		return $this->getChilds($this->dom->documentElement);
	}

    /**
     * 将数据内容解析成数组格式
     *
     * @param string $stream 数据内容
	 * @param int $option 解析选项,默认为0
	 * @return array
     */
    public function parseXmlStream($stream, $option = 0) {
    	if (!$stream) return array();
		$this->dom->loadXML($stream, $option);
		return $this->getChilds($this->dom->documentElement);
    }
	
	/**
	 * 将数据转换成xml格式
	 * 
	 * <code>
	 * 数组中key为数值型时，则转为<item id=key>value</item>
	 * 普通string或其他基本类型，则转为<item>string</item>
	 * </code>
	 *
	 * @param mixed $source 待转换的数据
	 * @param string $charset 待转换数据的编码
	 * @return string
	 */
	public function parseToXml($source, $charset = 'utf8') {
		switch (gettype($source)) {
			case 'object':
				$source = get_object_vars($source);
			case 'array':
				$this->arrayToXml($source, $charset, $this->dom);
				break;
			case 'string':
				$source = WindConvert::convert($source, 'utf8', $charset);
			default:
				$item = $this->dom->createElement("item");
				$text = $this->dom->createTextNode($source);
				$item->appendChild($text);
				$this->dom->appendChild($item);
				break;
		}
		return $this->dom->saveXML();
	}

	/**
	 * 获得节点的所有子节点
	 * 
	 * 子节点包括属性和子节点（及文本节点),
	 * 子节点的属性将会根据作为该节点的一个属性元素存放，如果该子节点中含有标签列表，则会进行一次合并。
	 * 每个被合并的列表项都作为一个单独的数组元素存在。
	 * 
	 * @param DOMElement $node 要解析的XMLDOM节点
	 * @return array 返回解析后该节点的数组
	 */
	public function getChilds($node) {
		if (!$node instanceof DOMElement) return array();
		$childs = array();
		foreach ($node->childNodes as $_node) {
			$tempChilds = $attributes = array();
			$_node->hasAttributes() && $attributes = $this->getAttributes($_node);
			if (3 == $_node->nodeType) {
				$value = trim($_node->nodeValue);
				(is_numeric($value) || $value) && $childs['__value'] = $value; //值为0的情况
				$__tmp = strtolower($value);
				('false' === $__tmp) && $childs['__value'] = false; //为false的配置值
				('true' === $__tmp) && $childs['__value'] = true; //为false的配置值
			}
			if (1 !== $_node->nodeType) continue;
			
			$tempChilds = $this->getChilds($_node);
			$tempChilds = array_merge($attributes, $tempChilds);
			
			if (empty($tempChilds))
				$tempChilds = '';
			else
				$tempChilds = (isset($tempChilds['__value']) && count($tempChilds) == 1) ? $tempChilds['__value'] : $tempChilds;
			
			$nodeName = "" !== ($name = $_node->getAttribute(self::NAME)) ? $name : $_node->nodeName;
			if (!isset($childs[$nodeName]))
				$childs[$nodeName] = $tempChilds;
			else {
				$element = $childs[$nodeName];
				$childs[$nodeName] = (is_array($element) && !is_numeric(implode('', array_keys($element)))) ? array_merge(
					array($element), array($tempChilds)) : array_merge((array) $element, array($tempChilds));
			}
		}
		return $childs;
	}

	/**
	 * 获得节点的属性
	 * 
	 * 该属性将不包含属性为name的值--规则（name的值将作为解析后数组的key值索引存在）
	 * 
	 * @param DOMElement $node 节点
	 * @return array 返回属性数组
	 */
	public function getAttributes($node) {
		if (!$node instanceof DOMElement || !$node->hasAttributes()) return array();
		$attributes = array();
		foreach ($node->attributes as $attribute) {
			if (self::NAME != $attribute->nodeName) {
				$value = (string) $attribute->nodeValue;
				$__tmp = strtolower($value);
				$attributes[$attribute->nodeName] = 'false' === $__tmp ? false : ('true' === $__tmp ? true : $value);
			}
		}
		return $attributes;
	}

	/**
	 * 将一个数组转换为xml
	 *
	 * @param array $arr 待转换的数组
	 * @param string $charset 编码
	 * @param DOMDocument $dom 根节点
	 * @return DOMDocument
	 */
	protected function arrayToXml($arr, $charset, $dom = null) {
		foreach ($arr as $key => $val) {
			if (is_numeric($key)) {
				$itemx = $this->dom->createElement("item");
				$id = $this->dom->createAttribute("id");
				$id->appendChild($this->dom->createTextNode($key));
				$itemx->appendChild($id);
			} else {
				$itemx = $this->dom->createElement($key);
			}
			$dom->appendChild($itemx);
			if (is_string($val)) {
				$val = WindConvert::convert($val, 'utf8', $charset);
				$itemx->appendChild($this->dom->createTextNode($val));
			} elseif (is_object($val)) {
				$this->arrayToXml(get_object_vars($val), $charset, $itemx);
			} else {
				$this->arrayToXml($val, $charset, $itemx);
			}
		}
	}

}