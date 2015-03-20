App._Events = function (Utils) {
	var APPJS_EVENTS_VAR = '__appjsCustomEventing';

	var hasCustomEvents = supportsCustomEventing();

	return {
		init : setupCustomEventing ,
		fire : fireEvent
	};



	function supportsCustomEventing () {
		try {
			var elem = document.createElement('div'),
				evt  = document.createEvent('CustomEvent');
			evt.initEvent('fooBarFace', false, true);
			elem.dispatchEvent(evt);
			return true;
		}
		catch (err) {
			return false;
		}
	}

	function setupCustomEventing (elem, names) {
		if (hasCustomEvents) {
			return;
		}

		if ( elem[APPJS_EVENTS_VAR] ) {
			Utils.forEach(names, elem[APPJS_EVENTS_VAR].addEventType);
			return;
		}

		elem[APPJS_EVENTS_VAR] = {
			fire                : fireElemEvent ,
			addEventType        : addEventType ,
			addEventListener    : elem.addEventListener ,
			removeEventListener : elem.removeEventListener
		};

		var listeners = {};
		Utils.forEach(names, function (name) {
			listeners[name] = [];
		});

		function addEventType (name) {
			if (names.indexOf(name) !== -1) {
				return;
			}
			names.push(name);
			listeners[name] = [];
		}

		function fireElemEvent (name) {
			if (names.indexOf(name) === -1) {
				return false;
			}

			var prevented = false,
				evt       = { preventDefault: function(){ prevented=true }};

			Utils.forEach(listeners[name], function (listener) {
				setTimeout(function () {
					if (listener.call(elem, evt) === false) {
						prevented = true;
					}
				}, 0);
			});

			return !prevented;
		}

		elem.addEventListener = function (name, listener) {
			if (names.indexOf(name) === -1) {
				elem[APPJS_EVENTS_VAR].addEventListener.apply(this, arguments);
				return;
			}

			var eventListeners = listeners[name];

			if (eventListeners.indexOf(listener) === -1) {
				eventListeners.push(listener);
			}
		};

		elem.removeEventListener = function (name, listener) {
			if (names.indexOf(name) === -1) {
				elem[APPJS_EVENTS_VAR].removeEventListener.apply(this, arguments);
				return;
			}

			var eventListeners = listeners[name],
				index          = eventListeners.indexOf(listener);

			if (index !== -1) {
				eventListeners.splice(index, 1);
			}
		};
	}

	function fireEvent (elem, eventName) {
		if (elem[APPJS_EVENTS_VAR]) {
			return elem[APPJS_EVENTS_VAR].fire(eventName);
		}
		else {
			var evt = document.createEvent('CustomEvent');
			evt.initEvent(eventName, false, true);
			return elem.dispatchEvent(evt);
		}
	}
}(App._Utils);
