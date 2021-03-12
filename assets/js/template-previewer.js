/* eslint-disable no-unused-vars */
/**************************************************************
 * included from http://www.quirksmode.org/js/cookies.html
 *************************************************************/

jQuery(window).on('beforeunload', function() {

  // Notify top window of the unload event
  window.top.postMessage('wu_preview_changed', '*');

});

/**
 * Prevent CORS
 */
// document.domain = wu_template_previewer.domain;

// create the cookie
wuCreateCookie('wuTemplate', false);

function is_iOS() {

  window.addEventListener('touchstart', {}); // in top window

  const iDevices = [
    'iPad Simulator',
    'iPhone Simulator',
    'iPod Simulator',
    'iPad',
    'iPhone',
    'iPod',
  ];

  if (!! navigator.platform) {

    while (iDevices.length) {

      if (navigator.platform === iDevices.pop()) {

        return true;

      }

    }

  }

  return false;

} // end is IOS;

(function($) {

  $(document).ready(function() {

    if (document.getElementById('iframe')) {

      /**
       * Send anti-cors message
       */
      // get reference to window inside the iframe
      const wn = document.getElementById('iframe').contentWindow;

      // postMessage arguments: data to send, target origin
      wn.postMessage('Hello to iframe from parent!', 'https://' + document.domain);

    }

    /**
     * Template button, should select the template and close the window
     */
    $('#action-select, #action-select2').on('click', function() {

      wuCreateCookie('wuTemplate', $('#template-selector').val());

      window.close();

    });

    /**
     * Fix on the iPhone previews
     *
     * @since 2.0.0
     */
    $('#iframe iframe').load(function() {

      if (is_iOS()) {

        $('#iframe iframe').contents().find('body').addClass('wu-fix-safari-preview').css({
          position: 'fixed',
          top: 0,
          right: 0,
          bottom: 0,
          left: 0,
          'overflow-y': 'scroll',
          '-webkit-overflow-scrolling': 'touch',
        });

      }

    });

  });

}(jQuery));

function wuCreateCookie(name, value, days) {

  let expires;

  if (days) {

    const date = new Date();

    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));

    expires = '; expires=' + date.toGMTString();

  } else {

    expires = '';

  }

  document.cookie = name + '=' + value + expires + '; path=/';

}

function wuReadCookie(name) {

  const nameEQ = name + '=';

  const ca = document.cookie.split(';');

  for (let i = 0; i < ca.length; i++) {

    let c = ca[i];

    while (c.charAt(0) === ' ') {

      c = c.substring(1, c.length);

    }

    if (c.indexOf(nameEQ) === 0) {

      return c.substring(nameEQ.length, c.length);

    }

  }

  return null;

}

// function wuEraseCookie(name) {

//   wuCreateCookie(name, '', -1);

// }

/**************************************************************
 * Actual code below
 *************************************************************/

const cookieRegistry = [];

function wuListenCookieChange(cookieName, callback) {

  setInterval(function() {

    if (cookieRegistry[cookieName]) {

      if (wuReadCookie(cookieName) !== cookieRegistry[cookieName]) {

        // update registry so we dont get triggered again
        cookieRegistry[cookieName] = wuReadCookie(cookieName);

        return callback();

      }

    } else {

      cookieRegistry[cookieName] = wuReadCookie(cookieName);

    }

  }, 100);

}

// FBar PHP & JS Theme Demo Bar Version v1.0
let theme_list_open = false;

(function($) {

  $(document).ready(function() {

    function e() {

      const ee = $('#switcher').outerHeight();

      $('#iframe').css('height', $(window).outerHeight() - ee + 'px');

    }

    const IS_IPAD = navigator.userAgent.match(/iPad/i) !== null;

    $(window).resize(function() {

      e();

    }).resize();

    $('#template_selector').click(function() {

      // eslint-disable-next-line eqeqeq
      if (theme_list_open == true) {

        $('.center ul li ul').hide();

        theme_list_open = false;

      } else {

        $('.center ul li ul').show();

        theme_list_open = true;

      }

      return false;

    });

    $('#theme_list ul li a').click(function() {

      const eee = $(this).attr('rel').split(',');

      $('li.purchase a').attr('href', eee[1]);

      $('li.remove_frame a').attr('href', eee[0]);

      $('#iframe').attr('src', eee[0]);

      $('#theme_list a#template_selector').text($(this).text());

      $('.center ul li ul').hide();

      theme_list_open = false;

      return false;

    });

    $('#header-bar').hide();

    const clicked = 'desktop';

    const t = {
      desktop: '100%',
      tabletlandscape: 1040,
      tabletportrait: 788,
      mobilelandscape: 500,
      mobileportrait: 340,
      placebo: 0,
    };

    jQuery('.responsive a').on('click', function() {

      const that = jQuery(this);

      for (const device in t) {

        if (that.hasClass(device)) {

          const _clicked = device;

          jQuery('#iframe').width(t[device]);

          // eslint-disable-next-line eqeqeq
          if (_clicked == device) {

            jQuery('.responsive a').removeClass('active');

            that.addClass('active');

          }

        }

      }

      return false;

    });

    if (IS_IPAD) {

      $('#iframe').css('padding-bottom', '60px');

    }

  });

}(jQuery));

if (top !== self) {

  // window.open(self.location.href, '_top');

}
