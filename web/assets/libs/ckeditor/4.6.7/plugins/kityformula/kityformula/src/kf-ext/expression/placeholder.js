/**
 * 占位符表达式， 扩展KF自有的Empty表达式
 */


define( function ( require, exports, module ) {

    var kity = require( "kity" ) ,

        kf = require( "kf" ),

        PlaceholderOperator = require( "kf-ext/operator/placeholder" );

    return kity.createClass( 'PlaceholderExpression', {

        base: kf.CompoundExpression,

        constructor: function () {

            this.callBase();

            this.setFlag( "Placeholder" );

            this.label = null;

            this.box.setAttr( "data-type", null );
            this.setOperator( new PlaceholderOperator() );

        },

        setLabel: function ( label ) {
            this.label = label;
        },

        getLabel: function () {
            return this.label;
        },

        // 重载占位符的setAttr， 以处理根占位符节点
        setAttr: function ( key, val ) {

            if ( key === "label" ) {
                this.setLabel( val );
            } else {

                if ( key.label ) {
                    this.setLabel( key.label );
                    // 删除label
                    delete key.label;
                }
                // 继续设置其他属性
                this.callBase( key, val );

            }

        },

        select: function () {

            this.getOperator().select();

        },

        selectAll: function () {

            this.getOperator().selectAll();

        },

        unselect: function () {
            this.getOperator().unselect();
        }

    } );

} );