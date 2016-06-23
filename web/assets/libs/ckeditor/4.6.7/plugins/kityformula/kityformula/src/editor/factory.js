/**
 * 编辑器工厂方法
 * 用于创建编辑器
 */

define( function ( require ) {

    var kity = require( "kity" ),
        KFEditor = require( "editor/editor" );

    /* ------------------------------- 编辑器装饰对象 */
    function EditorWrapper ( container, options ) {

        var _self = this;
        this._callbacks = [];

        this.editor = new KFEditor( container, options );

        this.editor.ready( function () {
            _self._trigger();
        } );

    }

    EditorWrapper.prototype._trigger = function () {

        var editor = this.editor;

        kity.Utils.each( this._callbacks, function ( cb ) {
            cb.call( editor, editor );
        } );

    };

    EditorWrapper.prototype.ready = function ( cb ) {

        if ( this.editor.isReady() ) {
            cb.call( this.editor, this.editor );
        } else {
            this._callbacks.push( cb );
        }

    };

    return {
        create: function ( container, options ) {

            return new EditorWrapper( container, options );

        }
    };

} );