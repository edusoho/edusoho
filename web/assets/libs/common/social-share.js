define(function(require, exports, module) {


    function SocialShare(config) {
        this.config = config;
    }

    SocialShare.prototype.share = function(type, params) {

    	var url = '';
    	switch(type) {
    		case 'weibo':
    			url = weibo(this.config.weibo, params);
    			break;
			case 'qq':
    			url = qq(this.config.qq, params);
    			break;
    		case 'renren':
    			url = renren(this.config.renren, params);
    			break;
			case 'douban':
    			url = douban(this.config.douban, params);
    			break;
    	}

    	if (url) {
	    	window.open(url, 'Share', 'height=400, width=600');
    	}

    };

    function weibo(config, params)
    {
    	var query = {};
    	query.appKey = config.key;
    	query.url = params.url;
    	query.title = params.title;
    	query.pic = params.picture;
    	return 'http://service.weibo.com/share/share.php?' + buildUrlQuery(query);
    }

    function qq(config, params)
    {
    	var query = {};
        query.appKey = config.key;
        query.url = params.url;
        query.title = params.title;
        query.pics = params.picture;
        return 'http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?' + buildUrlQuery(query);
    }

    function renren(config, params)
    {
        var query = {};
        query.appKey = config.key;
        query.url = params.url;
        query.title = params.title;
        query.pic = params.picture;
        return 'http://www.connect.renren.com/share/sharer?' + buildUrlQuery(query);
    }

    function douban(config, params)
    {
        var query = {};
        query.appKey = config.key;
        query.href = params.url;
        query.name = params.title;
        query.text = params.title;
        query.image = params.picture;
        return 'http://shuo.douban.com/!service/share?' + buildUrlQuery(query);
    }

    function buildUrlQuery (query)
    {
    	var queryItems = [];
		for( var q in query ){
			queryItems.push(q + '=' + encodeURIComponent( query[q] || '' ) )
		}

		return queryItems.join('&');
    }

    module.exports = SocialShare;
});
