/*!
 * 分割符
 */

define( function ( require ) {

    var kity = require( "kity" ),

        PREFIX = "kf-editor-ui-",

        // UiUitls
        $$ = require( "ui/ui-impl/ui-utils" ),

        Delimiter = kity.createClass( "Delimiter", {

            constructor: function ( doc ) {

                this.doc = doc;
                this.element = this.createDilimiter();

            },

            setToolbar: function ( toolbar ) {
            // do nothing
            },

            createDilimiter: function () {

                var dilimiterNode = $$.ele( this.doc, "div", {
                    className: PREFIX + "delimiter"
                } );

                dilimiterNode.appendChild( $$.ele( this.doc, "div", {
                    className: PREFIX + "delimiter-line"
                } ) );

                return dilimiterNode;

            },

            attachTo: function ( container ) {

                container.appendChild( this.element );

            }

        });

    return Delimiter;

} );