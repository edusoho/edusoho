App._Utils = function (window, document, App) {
	var query = function (queryString) {
		var re           = /([^&=]+)=([^&]+)/g,
			decodedSpace = /\+/g;

		var result = {},
			m, key, value;

		if (queryString) {
			queryString = queryString.replace(decodedSpace, '%20');

			while ((m = re.exec(queryString))) {
				key   = decodeURIComponent( m[1] );
				value = decodeURIComponent( m[2] );
				result[ key ] = value;
			}
		}

		return result;
	}( window.location.href.split('?')[1] );

	var os = function (userAgent) {
		var faked = false,
			name, version, match;

		if (query['_app_platform'] === 'android') {
			faked   = true;
			name    = 'android';
			version = '4.4';
		}
		else if (query['_app_platform'] === 'ios') {
			faked   = true;
			name    = 'ios';
			version = '7.0';
		}
		else if (match = /\bCPU.*OS (\d+(_\d+)?)/i.exec(userAgent)) {
			name    = 'ios';
			version = match[1].replace('_', '.');
		}
		else if (match = /\bAndroid (\d+(\.\d+)?)/.exec(userAgent)) {
			name    = 'android';
			version = match[1];
		}

		var data = {
			faked         : faked   ,
			name          : name    ,
			versionString : version ,
			version       : version && parseFloat(version)
		};

		data[ name ] = true;

		if (data.ios) {
			document.body.className += ' app-ios app-ios-'+parseInt(version);
		}
		else if (data.android) {
			document.body.className += ' app-android app-android-'+parseInt(version);
		}
		if (data.faked || !data.ios) {
			document.body.className += ' app-no-scrollbar';
		}

		return data;
	}(navigator.userAgent);

	var forEach = function (forEach) {
		if (forEach) {
			return function (arr, callback, self) {
				return forEach.call(arr, callback, self);
			};
		}
		else {
			return function (arr, callback, self) {
				for (var i=0, len=arr.length; i<len; i++) {
					if (i in arr) {
						callback.call(self, arr[i], i, arr);
					}
				}
			};
		}
	}(Array.prototype.forEach);

	function isArray (arr) {
		if (Array.isArray) {
			return Array.isArray(arr);
		}
		else {
			return Object.prototype.toString.call(arr) !== '[object Array]';
		}
	}

	function isNode (elem) {
		if ( !elem ) {
			return false;
		}

		try {
			return (elem instanceof Node) || (elem instanceof HTMLElement);
		} catch (err) {}

		if (typeof elem !== 'object') {
			return false;
		}

		if (typeof elem.nodeType !== 'number') {
			return false;
		}

		if (typeof elem.nodeName !== 'string') {
			return false;
		}

		return true;
	}

	function isjQueryElem($elem) {
		if (typeof $elem !== 'object' || $elem === null) {
			return false;
		} else {
			return ($elem.val && $elem.addClass && $elem.css && $elem.html && $elem.show);
		}
	}

	function onReady (func) {
		if (document.readyState === 'complete') {
			setTimeout(function () {
				func();
			}, 0);
			return;
		}

		window.addEventListener('load', runCallback, false);

		function runCallback () {
			window.removeEventListener('load', runCallback);

			setTimeout(function () {
				func();
			}, 0);
		}
	}

	function setTransform (elem, transform) {
		elem.style['-webkit-transform'] = transform;
		elem.style[   '-moz-transform'] = transform;
		elem.style[    '-ms-transform'] = transform;
		elem.style[     '-o-transform'] = transform;
		elem.style[        'transform'] = transform;
	}

	function setTransition (elem, transition) {
		if (transition) {
			elem.style['-webkit-transition'] = '-webkit-'+transition;
			elem.style[   '-moz-transition'] =    '-moz-'+transition;
			elem.style[    '-ms-transition'] =     '-ms-'+transition;
			elem.style[     '-o-transition'] =      '-o-'+transition;
			elem.style[        'transition'] =            transition;
		}
		else {
			elem.style['-webkit-transition'] = '';
			elem.style[   '-moz-transition'] = '';
			elem.style[    '-ms-transition'] = '';
			elem.style[     '-o-transition'] = '';
			elem.style[        'transition'] = '';
		}
	}

	function getStyles (elem, notComputed) {
		var styles;

		if (notComputed) {
			styles = elem.style;
		}
		else {
			styles = document.defaultView.getComputedStyle(elem, null);
		}

		return {
			display          : styles.display          ,
			opacity          : styles.opacity          ,
			paddingRight     : styles.paddingRight     ,
			paddingLeft      : styles.paddingLeft      ,
			marginRight      : styles.marginRight      ,
			marginLeft       : styles.marginLeft       ,
			borderRightWidth : styles.borderRightWidth ,
			borderLeftWidth  : styles.borderLeftWidth  ,
			top              : styles.top              ,
			left             : styles.left             ,
			height           : styles.height           ,
			width            : styles.width            ,
			position         : styles.position
		};
	}

	function isVisible (elem) {
		var styles = getStyles(elem);
		return (styles.display !== 'none' && styles.opacity !== '0');
	}

	// this is tuned for use with the iOS transition
	// be careful if using this elsewhere
	function transitionElems (transitions, timeout, easing, callback) {
		if (typeof transitions.length !== 'number') {
			transitions = [ transitions ];
		}

		var opacities = transitions.map(function (transition) {
			return transition.elem.style.opacity;
		});

		setInitialStyles(function () {
			animateElems(function () {
				restoreStyles(function () {
					callback();
				});
			});
		});

		function setInitialStyles (callback) {
			forEach(transitions, function (transition) {
				if (typeof transition.transitionStart !== 'undefined') {
					setTransform(transition.elem, transition.transitionStart);
				}
				if (typeof transition.opacityStart !== 'undefined') {
					transition.elem.style.opacity = transition.opacityStart + '';
				}
			});

			setTimeout(function () {
				forEach(transitions, function (transition) {
					var e                = transition.easing||easing,
						transitionString = 'transform '+(timeout/1000)+'s '+e+', opacity '+(timeout/1000)+'s '+e;
					setTransition(transition.elem, transitionString);
				});

				setTimeout(callback, 0);
			}, 0);
		}

		function animateElems (callback) {
			forEach(transitions, function (transition) {
				if (typeof transition.transitionEnd !== 'undefined') {
					setTransform(transition.elem, transition.transitionEnd);
				}
				if (typeof transition.opacityEnd !== 'undefined') {
					transition.elem.style.opacity = transition.opacityEnd + '';
				}
			});

			var lastTransition = transitions[transitions.length-1];
			lastTransition.elem.addEventListener('webkitTransitionEnd' , transitionFinished , false);
			lastTransition.elem.addEventListener('transitionend'       , transitionFinished , false);
			lastTransition.elem.addEventListener('onTransitionEnd'     , transitionFinished , false);
			lastTransition.elem.addEventListener('ontransitionend'     , transitionFinished , false);
			lastTransition.elem.addEventListener('MSTransitionEnd'     , transitionFinished , false);
			lastTransition.elem.addEventListener('transitionend'       , transitionFinished , false);

			var done = false;

			function transitionFinished (e) {
				if (done || (e.target !== lastTransition.elem)) {
					return;
				}
				done = true;

				forEach(transitions, function (transition) {
					lastTransition.elem.removeEventListener('webkitTransitionEnd' , transitionFinished);
					lastTransition.elem.removeEventListener('transitionend'       , transitionFinished);
					lastTransition.elem.removeEventListener('onTransitionEnd'     , transitionFinished);
					lastTransition.elem.removeEventListener('ontransitionend'     , transitionFinished);
					lastTransition.elem.removeEventListener('MSTransitionEnd'     , transitionFinished);
					lastTransition.elem.removeEventListener('transitionend'       , transitionFinished);
				});

				callback();
			}
		}

		function restoreStyles (callback) {
			forEach(transitions, function (transition) {
				setTransition(transition.elem, '');
			});

			setTimeout(function () {
				forEach(transitions, function (transition, i) {
					setTransform(transition.elem, '');
					transition.elem.style.opacity = opacities[i];
				});

				callback();
			}, 0);
		}
	}



	App.platform        = os.name;
	App.platformVersion = os.version;

	return {
		query         : query         ,
		os            : os            ,
		ready         : onReady       ,
		forEach       : forEach       ,
		isArray       : isArray       ,
		isNode        : isNode        ,
		isjQueryElem  : isjQueryElem  ,
		setTransform  : setTransform  ,
		setTransition : setTransition ,
		animate       : transitionElems ,
		getStyles     : getStyles     ,
		isVisible     : isVisible
	};
}(window, document, App);
