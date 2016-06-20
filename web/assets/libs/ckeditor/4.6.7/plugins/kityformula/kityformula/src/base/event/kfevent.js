/**
 * Created by hn on 14-3-17.
 */

define( function ( require ) {

    return {

        createEvent: function ( type, e ) {

            var evt = document.createEvent( 'Event' );

            evt.initEvent( type, true, true );

            return evt;

        }

    };

} );