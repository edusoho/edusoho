/**
 * Created by hn on 14-4-11.
 */

define( function ( require, exports, module ) {

    var kity = require( "kity" ),

        // 光标定位
        LocationComponent = require( "control/location" ),

        // 输入控制组件
        InputComponent = require( "control/input" ),

        // 选区
        SelectionComponent = require( "control/selection" );

    return kity.createClass( "MoveComponent", {

        constructor: function ( parentComponent, kfEditor ) {

            this.parentComponent = parentComponent;
            this.kfEditor = kfEditor;

            this.components = {};

            this.initComponents();

        },

        initComponents: function () {

            this.components.location= new LocationComponent( this, this.kfEditor );
            this.components.selection = new SelectionComponent( this, this.kfEditor );
            this.components.input = new InputComponent( this, this.kfEditor );

        }

    } );

} );