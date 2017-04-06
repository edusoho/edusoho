/*!
 * UI定义
 */

define( function ( require ) {

    return {
        // 视窗状态
        VIEW_STATE: {
            // 内容未超出画布
            NO_OVERFLOW: 0,
            // 内容溢出
            OVERFLOW: 1
        },
        scrollbar: {
            step: 50,
            thumbMinSize: 50
        }
    };

} );
