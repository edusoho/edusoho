/**
 * @license Copyright (c) 2003-2021, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */

/**
 * @fileOverview This plugin allows to register new paste handlers.
 */

( function() {
  var loadedFilters = [],
    PasteTools = CKEDITOR.tools.createClass( {
      $: function() {
        this.handlers = [];
      },

      proto: {
        register: function( definition ) {
          if ( typeof definition.priority !== 'number' ) {
            definition.priority = 10;
          }

          this.handlers.push( definition );
        },

        addPasteListener: function( editor ) {
          editor.on( 'paste', function( evt ) {
            var handlers = getMatchingHandlers( this.handlers, evt ),
              filters,
              isLoaded;

            if ( handlers.length === 0 ) {
              return;
            }

            filters = getFilters( handlers );

            isLoaded = loadFilters( filters, function() {
              return editor.fire( 'paste', evt.data );
            } );

            if ( !isLoaded ) {
              return evt.cancel();
            }

            handlePaste( handlers, evt );
          }, this, null, 3 );
        }
      }
    } );

  CKEDITOR.plugins.add( 'pastetools', {
    requires: [ 'clipboard', 'ajax' ],
    beforeInit: function( editor ) {
      editor.pasteTools = new PasteTools();

      editor.pasteTools.addPasteListener( editor );
    }
  } );

  /**
   * A set of paste tools helpers.
   *
   * @class
   * @singleton
   * @member CKEDITOR.plugins
   * @since 4.13.0
   */
  CKEDITOR.plugins.pastetools = {
    /**
     * A collection of available filters.
     *
     * @member CKEDITOR.plugins.pastetools
     * @property {Object.<String,Object>}
     */
    filters: {},

    /**
     * Loads external scripts containing filter definitions in given order.
     *
     * @param {String[]} filters An array of filter URLs.
     * @param {Function} callback A callback that will be invoked after loading all scripts.
     * @returns {Boolean} Returns `true` when there is no filters to load, `false` otherwise.
     * @member CKEDITOR.plugins.pastetools
     */
    loadFilters: loadFilters,

    /**
     * Creates a filter based on passed rules.
     *
     * @param {Object} options
     * @param {Function} options.rules A function returning the filter's rules.
     * @param {Function}options.additionalTransforms A function transforming HTML before passing it to the filter.
     * @returns {Function} A function that wraps filter invocation.
     * @member CKEDITOR.plugins.pastetools
     */
    createFilter: function( options ) {
      var rules = CKEDITOR.tools.array.isArray( options.rules ) ? options.rules : [ options.rules ],
        additionalTransforms = options.additionalTransforms;

      return function( html, editor ) {
        var writer = new CKEDITOR.htmlParser.basicWriter(),
          filter = new CKEDITOR.htmlParser.filter(),
          fragment;

        if ( additionalTransforms ) {
          html = additionalTransforms( html, editor );
        }

        CKEDITOR.tools.array.forEach( rules, function( rule ) {
          filter.addRules( rule( html, editor, filter ) );
        } );

        fragment = CKEDITOR.htmlParser.fragment.fromHtml( html );

        filter.applyTo( fragment );
        fragment.writeHtml( writer );

        return writer.getHtml();
      };
    },

    /**
     * Gets clipboard data.
     *
     * @param {Object} data Paste event `data` property.
     * @param {String} type MIME type of the requested data.
     * @returns {String/Blob} Raw clipboard data.
     * @member CKEDITOR.plugins.pastetools
     */
    getClipboardData: function( data, type ) {
      var dataTransfer;

      if ( !CKEDITOR.plugins.clipboard.isCustomDataTypesSupported && type !== 'text/html' ) {
        return null;
      }

      dataTransfer = data.dataTransfer.getData( type, true );

      // Some commands fire paste event without setting dataTransfer property. In such case
      // dataValue should be used for retrieving HTML.
      if ( !dataTransfer && type === 'text/html' ) {
        return data.dataValue;
      }

      return dataTransfer;
    },

    /**
     * Gets the configuration value.
     *
     * This function allows to get the configuration value for Paste Tools from
     * the legacy Paste from Word configuration.
     *
     * @param {CKEDITOR.editor} editor The editor instance.
     * @param {String} configVariable The configuration variable name.
     * @returns {String/Boolean/Number/Object/Array} The configuration variable value.
     * @member CKEDITOR.plugins.pastetools
     */
    getConfigValue: function( editor, configVariable ) {
      if ( !editor || !editor.config ) {
        return;
      }

      var self = this;
      var tools = CKEDITOR.tools,
        config = editor.config,
        configVariables = this.keys( config ),
        names = [
          'pasteTools_' + configVariable,
          'pasteFromWord_' + configVariable,
          'pasteFromWord' + tools.capitalize( configVariable, true )
        ],
        found = this.find( names, function( name ) {
          return self.indexOf( configVariables, name ) !== -1;
        } );

      return config[ found ];
    },

    indexOf: function( array, value ) {
      if ( typeof value == 'function' ) {
        for ( var i = 0, len = array.length; i < len; i++ ) {
          if ( value( array[ i ] ) )
            return i;
        }
      } else if ( array.indexOf )
        return array.indexOf( value );
      else {
        for ( i = 0, len = array.length; i < len; i++ ) {
          if ( array[ i ] === value )
            return i;
        }
      }
      return -1;
    },

    find: function( array, fn, thisArg ) {
      var length = array.length,
        i = 0;

      while ( i < length ) {
        if ( fn.call( thisArg, array[ i ], i, array ) ) {
          return array[ i ];
        }
        i++;
      }

      return undefined;
    },

    keys: function(obj) {
      var hasOwnProperty = Object.prototype.hasOwnProperty,
        keys = [],
        dontEnums = CKEDITOR.tools.object.DONT_ENUMS,
        isNotObject = !obj || typeof obj !== 'object';

      // We must handle non-object types differently in IE 8,
      // due to the fact that it uses ES5 behaviour, not ES2015+ as other browsers (#3381).
      if ( CKEDITOR.env.ie && CKEDITOR.env.version < 9 && isNotObject ) {
        return createNonObjectKeys( obj );
      }

      for ( var prop in obj ) {
        keys.push( prop );
      }

      // Fix don't enum bug for IE < 9 browsers (#3120).
      if ( CKEDITOR.env.ie && CKEDITOR.env.version < 9 ) {
        for ( var i = 0; i < dontEnums.length; i++ ) {
          if ( hasOwnProperty.call( obj, dontEnums[ i ] ) ) {
            keys.push( dontEnums[ i ] );
          }
        }
      }

      return keys;

      function createNonObjectKeys( value ) {
        var keys = [],
          i;

        if ( typeof value !== 'string' ) {
          return keys;
        }

        for ( i = 0; i < value.length; i++ ) {
          keys.push( String( i ) );
        }

        return keys;
      }
    },

    /**
     * Gets the name of the generator used to create the given content.
     *
     * It returns `undefined` if the `<meta>` tag with the generator name was not present.
     * It returns `'unknown'` if the `<meta>` tag has a generator other than `'microsoft'` or `'libreoffice'`.
     *
     * @member CKEDITOR.plugins.pastetools
     * @param {String} content The clipboard data.
     * @returns {String/undefined} The name of the recognized content generator. Possible values: `'microsoft'`, `'libreoffice'`, `'unknown'`, `undefiend`.
     */
    getContentGeneratorName: function( content ) {
      var metaGeneratorTag = /<meta\s+name=["']?generator["']?\s+content=["']?(\w+)/gi,
        result = metaGeneratorTag.exec( content ),
        generatorName;

      if ( !result || !result.length ) {
        return;
      }

      generatorName = result[ 1 ].toLowerCase();

      if ( generatorName.indexOf( 'microsoft' ) === 0 ) {
        return 'microsoft';
      }

      if ( generatorName.indexOf( 'libreoffice' ) === 0 ) {
        return 'libreoffice';
      }

      return 'unknown';
    }
  };

  function getMatchingHandlers( handlers, evt ) {
    return CKEDITOR.tools.array.filter( handlers, function( handler ) {
      return handler.canHandle( evt );
    } ).sort( function( handler1, handler2 ) {
      if ( handler1.priority === handler2.priority ) {
        return 0;
      }

      return handler1.priority - handler2.priority;
    } );
  }

  function handlePaste( handlers, evt ) {
    var handler = handlers.shift();

    if ( !handler ) {
      return;
    }

    handler.handle( evt, function() {
      handlePaste( handlers, evt );
    } );
  }

  // Join all filters in one big array and then filter out duplicates.
  function getFilters( handlers ) {
    var filters = CKEDITOR.tools.array.reduce( handlers, function( filters, handler ) {
      if ( !CKEDITOR.tools.array.isArray( handler.filters ) ) {
        return filters;
      }

      return filters.concat( handler.filters );
    }, [] );

    return CKEDITOR.tools.array.filter( filters, function( filter, i ) {
      return CKEDITOR.tools.array.indexOf( filters, filter ) === i;
    } );
  }

  function loadFilters( filters, callback ) {
    var loaded = 0,
      toLoad,
      i;

    if ( !CKEDITOR.tools.array.isArray( filters ) || filters.length === 0 ) {
      return true;
    }

    toLoad = CKEDITOR.tools.array.filter( filters, function( filter ) {
      return CKEDITOR.tools.array.indexOf( loadedFilters, filter ) === -1;
    } );

    if ( toLoad.length > 0 ) {
      for ( i = 0; i < toLoad.length; i++ ) {
        ( function( current ) {
          CKEDITOR.scriptLoader.queue( current, function( isLoaded ) {
            if ( isLoaded ) {
              loadedFilters.push( current );
            }

            if ( ++loaded === toLoad.length ) {
              callback();
            }
          } );
        }( toLoad[ i ] ) );
      }
    }

    return toLoad.length === 0;
  }

  /**
   * See {@link CKEDITOR.plugins.pastetools.filters}.
   *
   * @property {Object} pasteFilters
   * @private
   * @since 4.13.0
   * @member CKEDITOR
   */
  CKEDITOR.pasteFilters = CKEDITOR.plugins.pastetools.filters;
} )();
