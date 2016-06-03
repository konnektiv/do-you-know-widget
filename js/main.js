jQuery( document ).ready( function ( $ ) {
    var start = parseInt( $( '.dyk-next-time' ).text() ), timerId;

    if ( !start ) return;

    timerId = countdown(
        Date.now() + (start * 1000),
        function ( ts ) {
            $( '.dyk-next-time' ).html( ts.toHTML( "strong" ) );
            if ( !ts.hours && !ts.minutes && !ts.seconds ) {
                window.clearInterval( timerId );
                $( '.dyk-next-button' ).show();
                $( '.dyk-next-text' ).hide();
            }
        },
        countdown.HOURS | countdown.MINUTES | countdown.SECONDS, 2 );

} );