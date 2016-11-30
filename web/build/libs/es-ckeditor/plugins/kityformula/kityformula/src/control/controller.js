/**
 * Created by hn on 14-4-11.
 */

define( function ( require ) {

    var kity = require( "kity" ),

        ListenerComponent = require( "control/listener" ),

        ControllerComponent = kity.createClass( 'ControllerComponent', {

            constructor: function ( kfEditor ) {

                this.kfEditor = kfEditor;

                this.components = {};

                this.initComponents();

            },

            initComponents: function () {

                this.components.listener = new ListenerComponent( this, this.kfEditor );

            }


        } );

    return ControllerComponent;

} );