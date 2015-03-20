App._Pages = function (window, document, Clickable, Scrollable, App, Utils, Events, Metrics, Scroll) {
	var PAGE_NAME        = 'data-page',
		PAGE_CLASS       = 'app-page',
		APP_LOADED       = 'app-loaded',
		APP_STATUSBAR    = 'app-ios-statusbar',
		PAGE_READY_VAR   = '__appjsFlushReadyQueue',
		PAGE_MANAGER_VAR = '__appjsPageManager',
		EVENTS = {
			SHOW        : 'show'    ,
			HIDE        : 'hide'    ,
			BACK        : 'back'    ,
			FORWARD     : 'forward' ,
			BEFORE_BACK : 'beforeBack' ,
			READY       : 'ready'   ,
			DESTROY     : 'destroy' ,
			LAYOUT      : 'layout'  ,
			ONLINE      : 'online'  ,
			OFFLINE     : 'offline'
		};

	var preloaded        = false,
		forceIScroll     = !!window['APP_FORCE_ISCROLL'],
		pages            = {},
		controllers      = {},
		cleanups         = {},
		statusBarEnabled = false;

	setupPageListeners();
	if (window.APP_ENABLE_IOS_STATUSBAR) {
		enableIOSStatusBar();
	}


	App.add = function (pageName, page) {
		if (typeof pageName !== 'string') {
			page     = pageName;
			pageName = undefined;
		}

		if ( !Utils.isNode(page) ) {
			throw TypeError('page template node must be a DOM node, got ' + page);
		}

		addPage(page, pageName);
	};

	App.controller = function (pageName, controller, cleanup) {
		if (typeof pageName !== 'string') {
			throw TypeError('page name must be a string, got ' + pageName);
		}

		if (typeof controller !== 'function') {
			throw TypeError('page controller must be a function, got ' + controller);
		}

		switch (typeof cleanup) {
			case 'undefined':
				cleanup = function(){};
				break;

			case 'function':
				break;

			default:
				throw TypeError('page cleanup handler must be a function, got ' + cleanup);
		}

		if (controller) {
			addController(pageName, controller);
		}
		if (cleanup) {
			addCleanup(pageName, cleanup);
		}
	};
	App.populator = App.controller; // backwards compat

	App.generate = function (pageName, args) {
		if (typeof pageName !== 'string') {
			throw TypeError('page name must be a string, got ' + pageName);
		}

		switch (typeof args) {
			case 'undefined':
				args = {};
				break;

			case 'object':
				break;

			default:
				throw TypeError('page arguments must be an object if defined, got ' + args);
		}

		return generatePage(pageName, args);
	};

	App.destroy = function (page) {
		if ( !Utils.isNode(page) ) {
			throw TypeError('page node must be a DOM node, got ' + page);
		}

		return destroyPage(page);
	};

	App._layout             = triggerPageSizeFix;
	App._enableIOSStatusBar = enableIOSStatusBar;


	return {
		EVENTS                : EVENTS                 ,
		has                   : hasPage                ,
		createManager         : createPageManager      ,
		startGeneration       : startPageGeneration    ,
		finishGeneration      : finishPageGeneration   ,
		fire                  : firePageEvent          ,
		startDestruction      : startPageDestruction   ,
		finishDestruction     : finishPageDestruction  ,
		fixContent            : fixContentHeight       ,
		populateBackButton    : populatePageBackButton
	};



	/* Page elements */

	function preloadPages () {
		if (preloaded) {
			return;
		}
		preloaded = true;

		var pageNodes = document.getElementsByClassName(PAGE_CLASS);

		for (var i=pageNodes.length; i--;) {
			addPage( pageNodes[i] );
		}

		document.body.className += ' ' + APP_LOADED;
	}

	function addPage (page, pageName) {
		if ( !pageName ) {
			pageName = page.getAttribute(PAGE_NAME);
		}

		if ( !pageName ) {
			throw TypeError('page name was not specified');
		}

		page.setAttribute(PAGE_NAME, pageName);
		if (page.parentNode) {
			page.parentNode.removeChild(page);
		}
		pages[pageName] = page.cloneNode(true);
	}

	function hasPage (pageName) {
		preloadPages();
		return (pageName in pages);
	}

	function clonePage (pageName) {
		if ( !hasPage(pageName) ) {
			throw TypeError(pageName + ' is not a known page');
		}
		return pages[pageName].cloneNode(true);
	}



	/* Page controllers */

	function addController (pageName, controller) {
		controllers[pageName] = controller;
	}

	function addCleanup (pageName, cleanup) {
		cleanups[pageName] = cleanup;
	}

	function populatePage (pageName, pageManager, page, args) {
		var controller = controllers[pageName];
		if ( !controller ) {
			return;
		}
		for (var prop in controller) {
			pageManager[prop] = controller[prop];
		}
		for (var prop in controller.prototype) {
			pageManager[prop] = controller.prototype[prop];
		}
		pageManager.page = page; //TODO: getter
		pageManager.args = args; //TODO: getter (dont want this to hit localStorage)
		controller.call(pageManager, page, args);
	}

	function unpopulatePage (pageName, pageManager, page, args) {
		var cleanup = cleanups[pageName];
		if (cleanup) {
			cleanup.call(pageManager, page, args);
		}
		firePageEvent(pageManager, page, EVENTS.DESTROY);
	}



	/* Page generation */

	function createPageManager (restored) {
		var pageManager = {
			restored : restored ,
			showing  : false ,
			online   : navigator.onLine
		};

		var readyQueue = [];

		pageManager.ready = function (func) {
			if (typeof func !== 'function') {
				throw TypeError('ready must be called with a function, got ' + func);
			}

			if (readyQueue) {
				readyQueue.push(func);
			} else {
				func.call(pageManager);
			}
		};

		pageManager[PAGE_READY_VAR] = function () {
			Utils.ready(function () {
				if ( !readyQueue ) {
					return;
				}
				var queue = readyQueue.slice();
				readyQueue = null;
				if ( Utils.isNode(pageManager.page) ) {
					firePageEvent(pageManager, pageManager.page, EVENTS.READY);
				}
				Utils.forEach(queue, function (func) {
					func.call(pageManager);
				});
			});
		};

		return pageManager;
	}

	function generatePage (pageName, args) {
		var pageManager = {},
			page        = startPageGeneration(pageName, pageManager, args);

		finishPageGeneration(pageName, pageManager, page, args);

		return page;
	}

	function destroyPage (page) {
		var pageName = page.getAttribute(PAGE_NAME);
		startPageDestruction(pageName, {}, page, {});
		finishPageDestruction(pageName, {}, page, {});
	}

	function startPageGeneration (pageName, pageManager, args) {
		var page = clonePage(pageName);

		var eventNames = [];
		for (var evt in EVENTS) {
			eventNames.push( eventTypeToName(EVENTS[evt]) );
		}
		Events.init(page, eventNames);
		Metrics.watchPage(page, pageName, args);

		page[PAGE_MANAGER_VAR] = pageManager;

		fixContentHeight(page);

		Utils.forEach(
			page.querySelectorAll('.app-button'),
			function (button) {
				if (button.getAttribute('data-no-click') !== null) {
					return;
				}
				Clickable(button);
				button.addEventListener('click', function () {
					var target     = button.getAttribute('data-target'),
						targetArgs = button.getAttribute('data-target-args'),
						back       = (button.getAttribute('data-back') !== null),
						manualBack = (button.getAttribute('data-manual-back') !== null),
						args;

					try {
						args = JSON.parse(targetArgs);
					} catch (err) {}
					if ((typeof args !== 'object') || (args === null)) {
						args = {};
					}

					if (!back && !target) {
						return;
					}
					if (back && manualBack) {
						return;
					}

					var clickableClass = button.getAttribute('data-clickable-class');
					if (clickableClass) {
						button.disabled = true;
						button.classList.add(clickableClass);
					}

					if (back) {
						App.back(finish);
					}
					else if (target) {
						App.load(target, args, {}, finish);
					}

					function finish () {
						if (clickableClass) {
							button.disabled = false;
							button.classList.remove(clickableClass);
						}
					}
				}, false);
			}
		);

		populatePage(pageName, pageManager, page, args);

		page.addEventListener(eventTypeToName(EVENTS.SHOW), function () {
			setTimeout(function () {
				if (typeof pageManager[PAGE_READY_VAR] === 'function') {
					pageManager[PAGE_READY_VAR]();
				}
			}, 0);
		}, false);

		return page;
	}

	function firePageEvent (pageManager, page, eventType) {
		var eventName = eventTypeToName(eventType),
			funcName  = eventTypeToFunctionName(eventType),
			success   = true;
		if ( !Events.fire(page, eventName) ) {
			success = false;
		}
		if (typeof pageManager[funcName] === 'function') {
			if (pageManager[funcName]() === false) {
				success = false;
			}
		}
		return success;
	}

	function eventTypeToName (eventType) {
		return 'app' + eventType[0].toUpperCase() + eventType.slice(1);
	}

	function eventTypeToFunctionName (eventType) {
		return 'on' + eventType[0].toUpperCase() + eventType.slice(1);
	}

	function finishPageGeneration (pageName, pageManager, page, args) {
		Scroll.setup(page);
	}

	function startPageDestruction (pageName, pageManager, page, args) {
		if (!Utils.os.ios || Utils.os.version < 6) {
			Scroll.disable(page);
		}
		if (typeof pageManager.reply === 'function') {
			pageManager._appNoBack = true;
			pageManager.reply();
		}
	}

	function finishPageDestruction (pageName, pageManager, page, args) {
		unpopulatePage(pageName, pageManager, page, args);
	}



	/* Page layout */

	function setupPageListeners () {
		window.addEventListener('orientationchange', triggerPageSizeFix);
		window.addEventListener('resize'           , triggerPageSizeFix);
		window.addEventListener('load'             , triggerPageSizeFix);
		setTimeout(triggerPageSizeFix, 0);

		window.addEventListener('online', function () {
			if (App._Stack) {
				Utils.forEach(App._Stack.get(), function (pageInfo) {
					pageInfo[2].online = true;
					firePageEvent(pageInfo[2], pageInfo[3], EVENTS.ONLINE);
				});
			}
		}, false);
		window.addEventListener('offline', function () {
			if (App._Stack) {
				Utils.forEach(App._Stack.get(), function (pageInfo) {
					pageInfo[2].online = false;
					firePageEvent(pageInfo[2], pageInfo[3], EVENTS.OFFLINE);
				});
			}
		}, false);
	}

	function triggerPageSizeFix () {
		fixContentHeight();
		var pageData;
		if (App._Stack) {
			pageData = App._Stack.getCurrent();
		}
		if (pageData) {
			firePageEvent(pageData[2], pageData[3], EVENTS.LAYOUT);
		}

		//TODO: turns out this isnt all that expensive, but still, lets kill it if we can
		setTimeout(fixContentHeight,   0);
		setTimeout(fixContentHeight,  10);
		setTimeout(fixContentHeight, 100);
	}

	function fixContentHeight (page) {
		if ( !page ) {
			if (App._Navigation) {
				page = App._Navigation.getCurrentNode();
			}
			if ( !page ) {
				return;
			}
		}

		var topbar  = page.querySelector('.app-topbar'),
			content = page.querySelector('.app-content'),
			height  = window.innerHeight;

		if ( !content ) {
			return;
		}
		if ( !topbar ) {
			content.style.height = height + 'px';
			return;
		}

		var topbarStyles = document.defaultView.getComputedStyle(topbar, null),
			topbarHeight = Utils.os.android ? 48 : 44;
		if (topbarStyles.height) {
			topbarHeight = (parseInt(topbarStyles.height) || 0);
			if ((topbarStyles.boxSizing || topbarStyles.webkitBoxSizing) !== 'border-box') {
				topbarHeight += (parseInt(topbarStyles.paddingBottom) || 0) + (parseInt(topbarStyles.paddingTop) || 0);
				topbarHeight += (parseInt(topbarStyles.borderBottomWidth) || 0) + (parseInt(topbarStyles.borderTopWidth) || 0);
			}
		}
		content.style.height = (height - topbarHeight) + 'px';
	}

	function populatePageBackButton (page, oldPage) {
		if ( !oldPage ) {
			return;
		}
		var backButton = page.querySelector('.app-topbar .left.app-button'),
			oldTitle   = oldPage.querySelector('.app-topbar .app-title');
		if (!backButton || !oldTitle || (backButton.getAttribute('data-autotitle') === null)) {
			return;
		}
		var oldText = oldTitle.textContent,
			newText = backButton.textContent;
		if (!oldText || newText) {
			return;
		}
		if (oldText.length > 13) {
			oldText = oldText.substr(0, 12) + '..';
		}
		backButton.textContent = oldText;
	}

	function enableIOSStatusBar () {
		if (statusBarEnabled) {
			return;
		}
		statusBarEnabled = true;
		document.body.className += ' ' + APP_STATUSBAR;
		Utils.ready(function () {
			setTimeout(triggerPageSizeFix, 6);
		});
	}
}(window, document, Clickable, Scrollable, App, App._Utils, App._Events, App._Metrics, App._Scroll);
