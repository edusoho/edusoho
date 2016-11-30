/**
 * Created by hn on 14-4-1.
 */

define( function ( require ) {

    var $ = require( "jquery" ),
        kity = require( "kity" ),
        TOPIC_POOL = {};

    var Utils = {

        ele: function ( doc, name, options ) {

            var node = null;

            if ( name === "text" ) {
                return doc.createTextNode( options );
            }

            node =  doc.createElement( name );
            options.className && ( node.className = options.className );

            if ( options.content ) {
                node.innerHTML = options.content;
            }
            return node;
        },

        getRectBox: function ( node ) {
            return node.getBoundingClientRect();
        },

        on: function ( target, type, fn ) {
            $( target ).on( type, fn );
            return this;
        },

        delegate: function ( target, selector, type, fn ) {

            $( target ).delegate( selector, type, fn );
            return this;

        },

        publish: function ( topic, args ) {

            var callbackList = TOPIC_POOL[ topic ];

            if ( !callbackList ) {
                return;
            }

            args = [].slice.call( arguments, 1 );

            kity.Utils.each( callbackList, function ( callback ) {

                callback.apply( null, args );

            } );

        },

        subscribe: function ( topic, callback ) {

            if ( !TOPIC_POOL[ topic ] ) {

                TOPIC_POOL[ topic ] = [];

            }

            TOPIC_POOL[ topic ].push( callback );

        },

        getClassList: function ( node ) {

            return node.classList || new ClassList( node );

        }

    };


    //注意： 仅保证兼容IE9以上
    function ClassList ( node ) {

        this.node = node;
        this.classes = node.className.replace( /^\s+|\s+$/g, '' ).split( /\s+/ );

    }

    ClassList.prototype = {

        constructor: ClassList,

        contains: function ( className ) {

            return this.classes.indexOf( className ) !== -1;

        },

        add: function ( className ) {

            if ( this.classes.indexOf( className ) == -1 ) {
                this.classes.push( className );
            }

            this._update();

            return this;

        },

        remove: function ( className ) {

            var index = this.classes.indexOf( className );

            if ( index !== -1 ) {
                this.classes.splice( index, 1 );
                this._update();
            }

            return this;
        },

        toggle: function ( className ) {

            var method = this.contains( className ) ? 'remove' : 'add';

            return this[ method ]( className );

        },

        _update: function () {

            this.node.className = this.classes.join( " " );

        }

    };

    return Utils;

} );