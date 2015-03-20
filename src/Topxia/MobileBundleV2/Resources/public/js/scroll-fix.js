// fixes ios bounce scrolling in mobile safari

(function (document, App, Utils) {
	var touches = {};

	if (App.platform === 'ios' && App.platformVersion >= 5 && !Utils.os.faked && (typeof kik !== 'object' || kik === null || !kik.enabled)) {
		bindListeners();
	}
	return;

	function bindListeners() {
		document.addEventListener('touchstart', function (e) {
			var target = getTargetScroller(e);
			if (target && !target._iScroll) {
				if ((target.scrollHeight-target.clientHeight > 1) && ((target.scrollTop <= 0) || (target.scrollTop+target.clientHeight >= target.scrollHeight))) {
					addTouches(e);
				}
			}
		});
		document.addEventListener('touchmove', function (e) {
			var target = getTargetScroller(e);
			if ( !target ) {
				e.preventDefault();
			} else if (target._iScroll) {
				e.preventDefault();
			} else if (e.changedTouches) {
				if (e.changedTouches.length === 1) {
					onMove(e);
				}
				updateTouches(e);
			}
		});
		document.addEventListener('touchcancel', function (e) {
			clearTouches(e);
		});
		document.addEventListener('touchend', function (e) {
			clearTouches(e);
		});
	}

	function getTargetScroller(e) {
		var target = e.target;
		if (target) {
			do {
				if (target._scrollable) {
					break;
				}
			} while (target = target.parentNode);
		}
		return target;
	}

	function onMove(e) {
		var target = getTargetScroller(e),
				touch  = e.changedTouches[0],
				y0     = touches[touch.identifier],
				y1     = touch.pageY;
		if (target && typeof y0 !== 'undefined') {
			if (target.scrollTop <= 0) {
				if (y0 > y1) {
					delete touches[touch.identifier];
				} else {
					e.preventDefault();
				}
			} else if (target.scrollTop+target.clientHeight >= target.scrollHeight) {
				if (y0 < y1) {
					delete touches[touch.identifier];
				} else {
					e.preventDefault();
				}
			} else {
				delete touches[touch.identifier];
			}
		}
	}

	function addTouches(e) {
		if (e.changedTouches) {
			for (var i=0, l=e.changedTouches.length; i<l; i++) {
				touches[ e.changedTouches[i].identifier ] = e.changedTouches[i].pageY;
			}
		}
	}
	function updateTouches(e) {
		if (e.changedTouches) {
			for (var i=0, l=e.changedTouches.length; i<l; i++) {
				if (e.changedTouches[i].identifier in touches) {
					touches[ e.changedTouches[i].identifier ] = e.changedTouches[i].pageY;
				}
			}
		}
	}
	function clearTouches(e) {
		if (e.changedTouches) {
			for (var i=0, l=e.changedTouches.length; i<l; i++) {
				delete touches[ e.changedTouches[i].identifier ];
			}
		}
		if (e.touches) {
			var ids = [];
			for (var i=0, l=e.touches.length; i<l; i++) {
				ids.push(e.touches[i].identifier+'');
			}
			for (var id in touches) {
				if (ids.indexOf(id) === -1) {
					delete touches[id];
				}
			}
		}
	}
})(document, App, App._Utils);
