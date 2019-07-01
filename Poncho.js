( function ( mw, $ ) {

var Poncho = {

    /**
     * Initialization script
     */
    init: function () {
        Poncho.bind();
    },

    /**
     * Bind events
     */
    bind: function () {
       $( '#bell-icon' ).mouseenter( Poncho.readNotifications ); 
    },

    /**
     * Mark all notifications of the current user as read
     * Also change the bell icon
     */
    readNotifications: function () {
    	new mw.Api().postWithEditToken({
    		'action': 'echomarkread',
    		'all': true,
    	});
    },
};

mw.loader.using([
	'mediawiki.api',
], Poncho.init );

}( mw, jQuery ) );