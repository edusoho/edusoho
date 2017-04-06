/**
 * 公式扩展接口
 */

define( function ( require ) {

    var kf = require( "kf" ),
        SELECT_COLOR = require( "kf-ext/def" ).selectColor,
        ALL_SELECT_COLOR = require( "kf-ext/def" ).allSelectColor;

    function ext ( parser ) {

        kf.PlaceholderExpression = require( "kf-ext/expression/placeholder" );

        kf.Expression.prototype.select = function () {

            this.box.fill( SELECT_COLOR );

        };

        kf.Expression.prototype.selectAll = function () {
            this.box.fill( ALL_SELECT_COLOR );
        };

        kf.Expression.prototype.unselect = function () {

            this.box.fill( "transparent" );

        };

        // 扩展解析和逆解析
        parser.getKFParser().expand( {

            parse: {
                "placeholder": {
                    name: "placeholder",
                    handler: function ( info ) {

                        delete info.handler;
                        info.operand = [];

                        return info;

                    },
                    sign: false
                }
            },

            reverse: {

                "placeholder": function () {

                    return "\\placeholder ";

                }

            }

        } );

    }

    return {
        ext: ext
    };

} );
