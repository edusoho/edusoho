App._Stack = function (window, document, App, Utils, Scroll, Pages) {
	var STACK_KEY  = '__APP_JS_STACK__' + window.location.pathname,
		STACK_TIME = '__APP_JS_TIME__'  + window.location.pathname;

	var stack = [];

	App.getStack = function () {
		return fetchStack();
	};

	App.getPage = function (index) {
		var stackSize = stack.length - 1;
		switch (typeof index) {
			case 'undefined':
				index = stackSize;
				break;
			case 'number':
				if (Math.abs(index) > stackSize) {
					throw TypeError('absolute index cannot be greater than stack size, got ' + index);
				}
				if (index < 0) {
					index = stackSize + index;
				}
				break;
			default:
				throw TypeError('page index must be a number if defined, got ' + index);
		}
		return fetchPage(index);
	};

	App.removeFromStack = function (startIndex, endIndex) {
		// minus 1 because last item on stack is current page (which is untouchable)
		var stackSize = stack.length - 1;
		switch (typeof startIndex) {
			case 'undefined':
				startIndex = 0;
				break;
			case 'number':
				if (Math.abs(startIndex) > stackSize) {
					throw TypeError('absolute start index cannot be greater than stack size, got ' + startIndex);
				}
				if (startIndex < 0) {
					startIndex = stackSize + startIndex;
				}
				break;
			default:
				throw TypeError('start index must be a number if defined, got ' + startIndex);
		}
		switch (typeof endIndex) {
			case 'undefined':
				endIndex = stackSize;
				break;
			case 'number':
				if (Math.abs(endIndex) > stackSize) {
					throw TypeError('absolute end index cannot be greater than stack size, got ' + endIndex);
				}
				if (endIndex < 0) {
					endIndex = stackSize + endIndex;
				}
				break;
			default:
				throw TypeError('end index must be a number if defined, got ' + endIndex);
		}
		if (startIndex > endIndex) {
			throw TypeError('start index cannot be greater than end index');
		}

		removeFromStack(startIndex, endIndex);
	};

	App.addToStack = function (index, newPages) {
		// minus 1 because last item on stack is current page (which is untouchable)
		var stackSize = stack.length - 1;
		switch (typeof index) {
			case 'undefined':
				index = 0;
				break;
			case 'number':
				if (Math.abs(index) > stackSize) {
					throw TypeError('absolute index cannot be greater than stack size, got ' + index);
				}
				if (index < 0) {
					index = stackSize + index;
				}
				break;
			default:
				throw TypeError('index must be a number if defined, got ' + index);
		}
		if ( !Utils.isArray(newPages) ) {
			throw TypeError('added pages must be an array, got ' + newPages);
		}
		newPages = newPages.slice();
		Utils.forEach(newPages, function (page, i) {
			if (typeof page === 'string') {
				page = [page, {}];
			} else if ( Utils.isArray(page) ) {
				page = page.slice();
			} else {
				throw TypeError('page description must be an array (page name, arguments), got ' + page);
			}
			if (typeof page[0] !== 'string') {
				throw TypeError('page name must be a string, got ' + page[0]);
			}
			switch (typeof page[1]) {
				case 'undefined':
					page[1] = {};
				case 'object':
					break;
				default:
					throw TypeError('page arguments must be an object if defined, got ' + page[1]);
			}
			switch (typeof page[2]) {
				case 'undefined':
					page[2] = {};
				case 'object':
					break;
				default:
					throw TypeError('page options must be an object if defined, got ' + page[2]);
			}
			newPages[i] = page;
		});

		addToStack(index, newPages);
	};

	App.saveStack = function () {
		saveStack();
	};

	App.destroyStack = function () {
		destroyStack();
	};

	App.restore = setupRestoreFunction();

	return {
		get        : fetchStack ,
		getCurrent : fetchLastStackItem ,
		getPage    : fetchPage ,
		pop        : popLastStackItem ,
		push       : pushNewStackItem ,
		size       : fetchStackSize ,
		save       : saveStack ,
		destroy    : destroyStack
	};



	function saveStack () {
		try {
			var storedStack = [];
			for (var i=0, l=stack.length; i<l; i++) {
				if (stack[i][4].restorable === false) {
					break;
				}
				storedStack.push([stack[i][0], stack[i][3], stack[i][2]]);
			}
			localStorage[STACK_KEY] = JSON.stringify(storedStack);
			localStorage[STACK_TIME] = +new Date() + '';
		}
		catch (err) {}
	}

	function destroyStack () {
		delete localStorage[STACK_KEY];
		delete localStorage[STACK_TIME];
	}

	function fetchStack () {
		return stack.slice().map(reorganisePageData);
	}

	function fetchStackSize () {
		return stack.length;
	}

	function fetchLastStackItem () {
		var pageData = stack[stack.length-1];
		if (pageData) {
			return reorganisePageData(pageData);
		}
	}

	function popLastStackItem () {
		var pageData = stack.pop();
		if (pageData) {
			return reorganisePageData(pageData);
		}
	}

	function pushNewStackItem (pageData) {
		stack.push([pageData[0], pageData[3], pageData[4], pageData[1], pageData[2]]);
	}

	function fetchPage (index) {
		var pageData = stack[index];
		if (pageData) {
			return pageData[1];
		}
	}

	function reorganisePageData (pageData) {
		var pageArgs = {};
		for (var key in pageData[3]) {
			pageArgs[key] = pageData[3][key];
		}
		return [ pageData[0], pageArgs, pageData[4], pageData[1], pageData[2] ];
	}



	// you must manually save the stack if you choose to use this method
	function removeFromStackNow (startIndex, endIndex) {
		var deadPages = stack.splice(startIndex, endIndex - startIndex);

		Utils.forEach(deadPages, function (pageData) {
			Pages.startDestruction(pageData[0], pageData[4], pageData[1], pageData[3]);
			Pages.finishDestruction(pageData[0], pageData[4], pageData[1], pageData[3]);
		});
	}

	function removeFromStack (startIndex, endIndex) {
		App._Navigation.enqueue(function (finish) {
			removeFromStackNow(startIndex, endIndex);
			finish();
		});
	}

	// you must manually save the stack if you choose to use this method
	function addToStackNow (index, newPages, restored) {
		var pageDatas = [],
			lastPage;

		Utils.forEach(newPages, function (pageData) {
			var pageManager = Pages.createManager(true),
				page        = Pages.startGeneration(pageData[0], pageManager, pageData[1]);

			if (!pageData[2].transition && pageManager.transition) {
				pageData[2].transition = pageManager.transition;
			}

			Pages.populateBackButton(page, lastPage);

			Pages.finishGeneration(pageData[0], pageManager, page, pageData[1]);

			Scroll.saveScrollPosition(page);
			Scroll.saveScrollStyle(page);

			pageDatas.push([pageData[0], page, pageData[2], pageData[1], pageManager]);

			lastPage = page;
		});

		pageDatas.unshift(0);
		pageDatas.unshift(index);
		Array.prototype.splice.apply(stack, pageDatas);
	}

	function addToStack (index, newPages) {
		App._Navigation.enqueue(function (finish) {
			addToStackNow(index, newPages);
			finish();
		});
	}

	function setupRestoreFunction (options) {
		var storedStack, lastPage;

		try {
			storedStack = JSON.parse( localStorage[STACK_KEY] );
			storedTime  = parseInt( localStorage[STACK_TIME] );
			lastPage    = storedStack.pop();
		}
		catch (err) {
			return;
		}

		if ( !lastPage ) {
			return;
		}

		return function (options, callback) {
			switch (typeof options) {
				case 'function':
					callback = options;
				case 'undefined':
					options = {};
				case 'object':
					if (options !== null) {
						break;
					}
				default:
					throw TypeError('restore options must be an object if defined, got ' + options);
			}

			switch (typeof callback) {
				case 'undefined':
					callback = function () {};
				case 'function':
					break;
				default:
					throw TypeError('restore callback must be a function if defined, got ' + callback);
			}

			if (+new Date()-storedTime >= options.maxAge) {
				throw TypeError('restore content is too old');
			}

			if ( !Pages.has(lastPage[0]) ) {
				throw TypeError(lastPage[0] + ' is not a known page');
			}

			Utils.forEach(storedStack, function (pageData) {
				if ( !Pages.has(pageData[0]) ) {
					throw TypeError(pageData[0] + ' is not a known page');
				}
			});

			try {
				addToStackNow(0, storedStack, true);
			}
			catch (err) {
				removeFromStackNow(0, stack.length);
				throw Error('failed to restore stack');
			}

			saveStack();

			try {
				App.load(lastPage[0], lastPage[1], lastPage[2], callback);
			}
			catch (err) {
				removeFromStackNow(0, stack.length);
				throw Error('failed to restore stack');
			}
		};
	}
}(window, document, App, App._Utils, App._Scroll, App._Pages);
