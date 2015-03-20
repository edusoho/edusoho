<?php
/**
 * 配置解析的接口定义
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: IWindConfigParser.php 2973 2011-10-15 19:22:48Z yishuo $
 * @package parser
 */
interface IWindConfigParser {

	/**
	 * 解析组件的配置文件
	 * 
	 * 根据配置文件路径$configPath解析配置返回一个数组，
	 * 如果设置了$cache,则将解析出来的数据保存到$cache中，保存规则如下:
	 * <ul>
	 * <li>如果没有设置$alias或是没有设置$cache，则将不保存数据</li>
	 * <li>如果没有设置$append: 则将会以$alias为名将$data保存在缓存$cache中</li>
	 * <li>如果设置了$append和$alias: 则先去从$cache中获得名为$append的缓存内容，并且将$data以$alias为键名保存到该缓存内容中,
	 * 然后仍然以$append之名写回到$cache中</li>
	 * </ul>
	 * 如果没有设置$cache，将直接返回解析结果.
	 * 
	 * @param string $configPath 待解析的文件路径
	 * @param string $alias 解析后保存的key名,默认为空,及不保存
	 * @param string $append 追加的文件,默认为空
	 * @param AbstractWindCache $cache  缓存策略默认为null及不保存
	 * @return array 解析结果
	 */
	public function parse($configPath, $alias = '', $append = '',  AbstractWindCache $cache = null);

}