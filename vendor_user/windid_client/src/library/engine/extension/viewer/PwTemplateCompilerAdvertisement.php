<?php
Wind::import('WIND:viewer.AbstractWindTemplateCompiler');
/**
 * 
 * 广告标签解析
 * <code>
 * <advertisement id="**" />
 * </code>
 *
 * @author Zhu Dong <zhudong0808@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: codetemplates(windframework_docs_zend_8.0).xml 2781 2011-09-22 03:59:17Z zhudong $
 * @package wind
 */
class PwTemplateCompilerAdvertisement extends AbstractWindTemplateCompiler {
	protected $id = ''; //广告位ID
	protected $sys = ''; //是否为默认广告位
	/* (non-PHPdoc)
	 * @see AbstractWindTemplateCompiler::compile()
	 */
	public function compile($key, $content) {
		$pwAdDs = Wekit::load('SRV:advertisement.PwAd');
		if($this->sys == 1){
			$ad = $pwAdDs->getByIdentifier($this->id);
		}else{
			$ad = $pwAdDs->getByPid($this->id);
		}
		if(!$ad) return '';
		$html = $this->_buildHtml($ad);
		return $html;
	}
	
	private function _buildHtml($ad){
		$pid  = $ad['pid'];
		/* @var $router WindRouter */
		$router = Wind::getComponent('router');
		$adSrc = $router->getModule() . '.' . $router->getController();
		$mode = $this->_getAdService()->getModeByMid($adSrc);
		$html = '<?php $pwAdService = Wekit::load("SRV:advertisement.srv.PwAdService");'."\r\n";
		$html .= '$ads = Wekit::cache()->get("advertisement");'."\r\n";
		$html .= '$currentAd = $ads['.$pid.'];'."\r\n";
		$html .= '$adShowResult = $pwAdService->getAdShowState($currentAd,\''.$mode.'\',$fid,\''.$adSrc.'\',$read[\'lou\'],$proid);'."\r\n";
		$html .= 'if($adShowResult){'."\r\n";
		$html .= 'if($currentAd[\'show_type\'] == 1){?>'."\r\n";
		$html .= '<script type="text/javascript" charset="gb2312" src="http://js.adm.cnzz.net/s.php?sid='.$pid.'&proid=<?php echo WindSecurity::escapeHTML($proid);?>&fid=<?php echo WindSecurity::escapeHTML($fid);?>&mid='.$mode.'&floorid=<?php echo WindSecurity::escapeHTML($read[lou])?>&pid='.$adSrc.'"></script>'."\r\n";
		$html .= '<?php }else{ ?>'."\r\n";
		$html .= '<div class="J_ad_iframes_div" data-src="http://js.adm.cnzz.net/pwaos.php?sid='.$pid.'&proid=<?php echo WindSecurity::escapeHTML($proid);?>&fid=<?php echo WindSecurity::escapeHTML($fid);?>&mid='.$mode.'&floorid=<?php echo WindSecurity::escapeHTML($read[lou])?>&pid='.$adSrc.'" data-width="<?php echo WindSecurity::escapeHTML($currentAd[width]);?>" data-height="<?php echo WindSecurity::escapeHTML($currentAd[height]);?>"></div>'."\r\n";
		$html .= '<?php }}?>'."\r\n";
		return $html;
	}

	
	/* (non-PHPdoc)
	 * @see AbstractWindTemplateCompiler::getProperties()
	 */
	public function getProperties() {
		return array('id','sys');
	}

	private function _getAdService(){
		return Wekit::load('SRV:advertisement.srv.PwAdService');
	}
}