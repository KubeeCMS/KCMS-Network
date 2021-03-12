/* global wu_visits_counter */
/**
 * Handles Visit Counting.
 *
 * This scripts triggers a call to the counter 5 seconds after the page load is complete
 * OR when the user clicks to leave the window.
 * Passes security code to prevent DDoS.
 *
 */
(function($) {

  function get_cookie(cname) {

    const name = cname + '=';

    const ca = document.cookie.split(';');

    for (let i = 0; i < ca.length; i++) {

      let c = ca[i];

      while (c.charAt(0) === ' ') {

        c = c.substring(1);

      }

      if (c.indexOf(name) === 0) {

        return c.substring(name.length, c.length);

      }

    }

    return '';

  } // end get_cookie;

  const counted = get_cookie('WUVISIT');

  /*
   * This user was already counted. We need to bail.
   */
  if (parseInt(counted, 10) === 1) {

    return;

  } // end if;

  /**
   * Defines the jqHRX variable holder and the done controller.
   */
  let wu_sync_visits_count_call,
    done = false;

  /**
   * Sends the count visit request to the Server.
   */
  const wu_sync_visits_count = function() {

    return $.ajax({
      type: 'GET',
      url: wu_visits_counter.ajaxurl,
      data: {
        action: 'wu_count_visits',
        code: wu_visits_counter.code,
      },
    }).done(function() {

      /**
       * When done, we set the controller to true to prevent recounting...
       */
      done = true;

    });

  }; // end wu_sync_visits_count;

  /**
   * Triggers when the user navigates away after 3 seconds or more.
   */
  setTimeout(function() {

    $(window).on('unload', function() {

      /**
       * Abort ongoing call
       */
      // eslint-disable-next-line valid-typeof
      if (typeof wu_sync_visits_count_call === 'null') {

        if (! done) {

          wu_sync_visits_count_call = wu_sync_visits_count();

        } // end if;

      } // end if;

    });

  }, 3000);

  /**
   * Triggers when the document is ready and 5 seconds have passed.
   */
  $(document).ready(function() {

    setTimeout(function() {

      wu_sync_visits_count_call = wu_sync_visits_count();

    }, 10000);

  });

}(jQuery));
