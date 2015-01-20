define("chineserp/1.0.0/chineserp-debug", ["$"], function(require, exports, module) {
  var $ = require("$");
  /*! Chinese region picker - v0.0.1 - 2013-10-24
   * https://github.com/xixilive/chineserp
   * Copyright (c) 2013 xixilive; Licensed MIT */
  'use strict';
  //ECMA262-5 methods: Array#indexOf
  if (!('indexOf' in Array.prototype)) {
    Array.prototype.indexOf = function(find, i) {
      if (i === undefined) {
        i = 0;
      }
      if (i < 0) {
        i += this.length;
      }
      if (i < 0) {
        i = 0;
      }
      for (var n = this.length; i < n; i++) {
        if (i in this && this[i] === find) {
          return i;
        }
      }
      return -1;
    };
  }
  //ECMA262-5 methods: Array#forEach
  if (!('forEach' in Array.prototype)) {
    Array.prototype.forEach = function(action, that) {
      for (var i = 0, n = this.length; i < n; i++) {
        if (i in this) {
          action.call(that, this[i], i, this);
        }
      }
    };
  }
  //ECMA262-5 methods: Array#map
  if (!('map' in Array.prototype)) {
    Array.prototype.map = function(mapper, that) {
      var other = new Array(this.length);
      for (var i = 0, n = this.length; i < n; i++) {
        if (i in this) {
          other[i] = mapper.call(that, this[i], i, this);
        }
      }
      return other;
    };
  }
  //ECMA262-5 methods: Array#filter
  if (!('filter' in Array.prototype)) {
    Array.prototype.filter = function(filter, that) {
      var other = [],
        v;
      for (var i = 0, n = this.length; i < n; i++) {
        if (i in this && filter.call(that, v = this[i], i, this)) {
          other.push(v);
        }
      }
      return other;
    };
  }
  var root = this;
  var ChineseRegion = root.ChineseRegion = {};
  ChineseRegion.$ = $;
  ChineseRegion._caches = {};
  // Prototype getJSON wrapper
  if (ChineseRegion.$ && !ChineseRegion.$.getJSON && root.Ajax) {
    ChineseRegion.$.getJSON = function() {
      var url = arguments[0],
        data, callback;
      switch (arguments.length) {
        case 2:
          data = null;
          callback = arguments[1];
          break;
        case 3:
          data = arguments[1];
          callback = arguments[2];
          break;
      }
      if (typeof callback !== 'function') {
        callback = function() {};
      }
      var request = new root.Ajax.Request(url, {
        method: 'get',
        parameters: data,
        evalJSON: 'force',
        onSuccess: function(t) {
          callback(t.responseJSON);
        }
      });
      return request.transport;
    };
  }
  ChineseRegion.getJSON = function() {
    return ChineseRegion.$.getJSON.apply(ChineseRegion.$, arguments);
  };
  // return true given argument is a function, otherwise false
  ChineseRegion.ifn = function(f) {
    return typeof f === 'function';
  };
  /**
    A collection that contains all cities and suburbs of a province
    @param {Array} data, the data to be stored
    @param {String} name, use as collection name
    @return RegionCollection object
    @example: new RegionCollection([...], 'collection1')
  */
  var RegionCollection = ChineseRegion.RegionCollection = function(data, name) {
    this.collection = data || [];
    this.name = name;
    return this;
  };
  RegionCollection.prototype = {
    constructor: RegionCollection,
    select: function(f) {
      return this.collection.filter(f);
    },
    map: function(f) {
      return this.collection.map(f);
    },
    first: function(f) {
      return this.select(f)[0];
    },
    /**
      Find regions by region names in current RegionCollection object
      by default, this function will select the first element in array when expect region is missing.
      @param {String} a, the first region name
      @param {String} b, the second region name
      @param {String} options, your can specify which key to find in object
      @return {Array}
      @exapmle: findByNames('上海市','杨浦区') should return [{n:上海市,...}, {n:杨浦区,...}],
                findByNames('江苏省','南京市','玄武区') should return [{n:江苏省,...}, {n:南京市,...}, {n:玄武区,...}],
                findByNames('上海','崇明', {key: 'a'}) should return [{n:上海市,...}, {n:崇明县,...}],
                findByNames('上海市','卢湾区') should return [{n:上海市,...}, {n:黄浦区,...}], because '卢湾区' is not exists
    */
    findByNames: function(a, b, options) {
      options = options || {};
      options.key = options.key || 'n'; //default to find by region name
      var arr = [];
      if (a) {
        arr[0] = this.first(function(d) {
          return d[options.key] === a;
        }) || this.collection[0];
      }
      if (b && arr[0] && arr[0].c) {
        arr[1] = arr[0].c.filter(function(d) {
          return d[options.key] === b;
        })[0] || arr[0].c[0];
      }
      return arr;
    },
    /**
      Find regions by region ID in current RegionCollection object
      by default, this function will select the first element in array when expect region is missing.
      @param {String} id
      @return {Array}
      @exapmle: findById('310105') should return [{i:310000,...}, {i:310105,...}] of Shanghai city,
                findById('310103') should return [{i:310000,...}, {i:310101,...}], because '310103' is not exists
    */
    findById: function(id) {
      var arr = [];
      arr[0] = this.first(function(d) {
        return d.i === id;
      }) || this.first(function(d) {
        return d.i === id.substr(0, 4) + '00';
      }) || this.collection[0];
      if (arr[0] && arr[0].c) {
        arr[1] = arr[0].c.filter(function(d) {
          return d.i === id;
        })[0] || arr[0].c[0];
      }
      return arr;
    }
  };
  /*
    DataProxy, load data from remote json files, and cache these files
    the callback function will be call with 2 arguments, the first one is DataProxy object,
    and another one is RegionCollection object
    @param {String} remote, the URI path of remote files
    @param {Function} init, callback function
    @return {Object} DataProxy object
    @example: new DataProxy('/remote/', function(proxy, collection){})
  */
  var DataProxy = ChineseRegion.DataProxy = function(remote, init) {
    this._remote = (remote + '/').replace(/\/+/g, '/');
    this.load('index', function(collection, proxy) {
      if (ChineseRegion.ifn(init)) {
        init(proxy, collection);
      }
    });
    return this;
  };
  DataProxy.prototype = {
    /**
      Convert region ID to special format, in order to match the json filename
      @param {String} id
      @return {String}
      @example: _index('310105') should get '310000'
    */
    _index: function(id) {
      return id.replace(/\d{4}$/, '0000');
    },
    /**
      Convert region ID to special cache-id format, in order to cache a collection totally
      @param {String} id
      @return {String}
      @example: _cacheid('310105') should get 'cached_310000'
    */
    _cacheid: function(id) {
      return 'cached_' + this._index(id);
    },
    /**
      Convert region ID to related json-file's url
      @param {String} id
      @return {String}
      @example: _url('310105') should get '/a_remote_path/310105.json'
    */
    _url: function(id) {
      return this._remote + this._index(id) + '.json';
    },
    /**
      Load data asynchoronize, the callback function will call with 2 arguments, 
      the first is RegionCollection object, 
      and the second is DataProxy object
      @param {String} id
      @param {Function} callback function, 
      @exapmle load('310105', function(collection, proxy){})
    */
    load: function(id, f) {
      var self = this,
        cache_id = this._cacheid(id);
      if (!ChineseRegion.ifn(f)) {
        f = function() {};
      }
      if (ChineseRegion._caches[cache_id]) {
        f(new RegionCollection(ChineseRegion._caches[cache_id], cache_id), self);
        return;
      }
      ChineseRegion.getJSON(this._url(id), function(data) {
        ChineseRegion._caches[cache_id] = data;
        f(new RegionCollection(data, cache_id), self);
      });
    },
    /**
      Provide cached collections
      @return {Array}
    */
    collections: function() {
      var coll = [];
      for (var i in ChineseRegion._caches) {
        coll.push(new RegionCollection(ChineseRegion._caches[i], i));
      }
      return coll;
    },
    /**
      Get a collection named the specified argument
      @param {String} value
      @return {Array}
      @example: collection('index'), collection('310105')
    */
    collection: function(value) {
      var cid = this._cacheid(value);
      return this.collections().filter(function(c) {
        return c.name === cid;
      })[0];
    },
    /**
      Provide the index collection
      @return {Array}
    */
    indices: function() {
      return this.collection('index');
    }
  };
  /*
    RegionPicker, the initialized callback will be call with RegionPicker object
    @param {Object} options
    @example:  new RegionPicker({remote: '/', initialized: function(picker){
      picker.pick('310102', function(regions){ console.log( regions.map(function(d){ return d.n; }).join(" > "); ); })
      picker.pick('PROVINCE,CITY,SUBURB', function(regions){ console.log( regions.map(function(d){ return d.n; }).join(" > "); ); })
    }})
    @return RegionPicker object
  */
  var RegionPicker = ChineseRegion.RegionPicker = function(options) {
    var self = this;
    this.options = options;
    new DataProxy(options.remote || '', function(proxy) {
      self.initialize(proxy);
    });
    return self;
  };
  RegionPicker.prototype = {
    initialize: function(proxy) {
      this.proxy = proxy;
      if (ChineseRegion.ifn(this.options.initialized)) {
        this.options.initialized(this);
      }
    },
    /**
      pick up a specified region
      @param {String | Array} value
      @param {Object} options
      @param {Function} callback
      @return undefined
      @example pick(value, callback)
      @example pick(value, options, callback)
    */
    pick: function() {
      var value = arguments[0],
        options = {},
        f;
      switch (arguments.length) {
        case 2:
          f = arguments[1];
          break;
        case 3:
          options = arguments[1];
          f = arguments[2];
          break;
      }
      if (!ChineseRegion.ifn(f)) {
        f = function() {};
      }
      var proxy = this.proxy;
      if (!this.proxy) {
        f([]);
        return;
      }
      if (value === null || value === '') {
        value = proxy.indices().collection[0].i;
      }
      if (/^\d+$/.test(value)) { //pickById
        this._pickById(value, f);
        return;
      } else { //pickByNames
        if (typeof value === 'string') {
          value = value.split(/[,\s]+/, 3);
        }
        this._pickByNames(value, options, f);
        return;
      }
      f([]);
    },
    /**
      Provide array that is the super-collection of regions
      @param {Array} regions
      @return {Array} a array contains 3 or less elements, in same order as the elements in regions argument
    */
    _pickedCollections: function(regions) {
      if (!regions || !regions.map) {
        return [];
      }
      var collections = [];
      if (regions[0]) {
        collections.push(this.proxy.collection('index').collection);
      }
      if (regions[1]) {
        collections.push(this.proxy.collection(regions[1].i).collection);
      }
      if (regions[2] && regions[1].c) {
        collections.push(regions[1].c);
      }
      return collections;
    },
    /**
      Pickup regions via specified ID
      @param {String} id
      @param {Function} f, a callback function that will be call when regions have picked
    */
    _pickById: function(id, f) {
      var self = this,
        proxy = this.proxy,
        regions = [];
      regions[0] = proxy.indices().first(function(r) {
        return r.i === id.substr(0, 2) + '0000';
      });
      proxy.load(id, function(c) {
        regions = regions.concat(c.findById(id));
        f(regions, self._pickedCollections(regions));
      });
    },
    /**
      Pickup a region via specified string seperated with comma or space
      @param {String} names
      @param {Object} options
      @param {Function} f, a callback function that will be call when regions have picked
    */
    _pickByNames: function(names, options, f) {
      var self = this,
        proxy = this.proxy,
        regions = [];
      regions[0] = proxy.indices().first(function(r) {
        return r.n === names[0];
      });
      if (!regions[0]) {
        f([]);
        return;
      }
      proxy.load(regions[0].i, function(c) {
        regions = regions.concat(c.findByNames(names[1], names[2], options));
        f(regions, self._pickedCollections(regions));
      });
    }
  };
  module.exports = ChineseRegion;
});