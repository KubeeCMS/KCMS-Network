/* global wu_create_cookie, wu_sso_config, wu_read_cookie, detectIncognito */
(function(o) {

  window.wu = window.wu || {};

  window.is_incognito = false;

  window.wu.sso_denied = function() {

    wu_create_cookie('wu_sso_denied', 1, o.expiration_in_minutes);

  }; // end if;

  window.wu.check_for_incognito_window = function() {

    detectIncognito(results => window.is_incognito = results.isPrivate);

  } // end is_incognito_window;

  window.wu.check_for_incognito_window();

  window.addEventListener('error', wu.sso_denied, true);

  const w = document.createElement('script');

  w.type = 'text/javascript';

  w.async = true;

  w.defer = true;

  w.src = o.server_url + '?_jsonp=1';

  const denied = wu_read_cookie('wu_sso_denied');

  document.head.insertAdjacentHTML('beforeend', `
    <style>
      @keyframes fade_in {
        from { opacity: 0; }
        to   { opacity: 1; }
      }
      body.sso-loading {
        overflow: hidden;
      }
      body.sso-loading .sso-overlay {
        background: rgba(0, 0, 0, 0.7);
        width: 100vw;
        height: 100vh;
        position: absolute;
        top: 0;
        -webkit-animation: fade_in 300ms;
        -moz-animation: fade_in 300ms;
        -ms-animation: fade_in 300ms;
        -o-animation: fade_in 300ms;
        animation: fade_in 300ms;
      }
      body.sso-loading .sso-overlay::before {
        content: "";
        display: block;
        width: 20px;
        height: 20px;
        position: absolute;
        left: 50%;
        top: 50%;
        margin: -10px 0 0 -10px;
        transform: translateZ(0);
        background: transparent url(${ o.img_folder }/spinner.gif) no-repeat center center;
        background-image: url(${ o.img_folder }/loader.svg);
        background-size: 20px 20px;
      }
    </style>
  `);

  if (! o.is_user_logged_in && ! denied) {

    const s = document.getElementsByTagName('script')[0];

    s.parentNode.insertBefore(w, s);

    document.body.insertAdjacentHTML('beforeend', '<div class="sso-overlay">&nbsp;</div>');

  } // end if;

  window.wu.sso = function(payload) {

    if (payload.code === 200) {

      if (o.use_overlay) {

        document.body.classList.add('sso-loading');

      } // end if;

      /**
       * In case we're dealing with http (without ssl),
       * we force a redirect to bypass browser cookie
       * limitations.
       *
       * Otherwise, on the else block,
       * we redirect with the verification code attached,
       * to perform a regular SSO flow.
       */
      if (payload.verify === 'must-redirect') {

        window.location.replace(`${ o.server_url }?return_url=${ window.location.href }`);

      } else {

        window.location.replace(`${ o.server_url }?sso_verify=${ payload.verify }&return_url=${ window.location.href }`);

      } // end if;

    } else {

      /**
       * If we are in a incognito window,
       * we give it another try with a full redirect,
       * as chrome settings might be blocking
       * cookies from being sent anyways.
       */
      if (window.is_incognito) {

        if (o.use_overlay) {

          document.body.classList.add('sso-loading');

        } // end if;

        window.location.replace(`${o.server_url}?return_url=${window.location.href}`);

        return;

      } // end if;

      window.wu.sso_denied();

      document.body.classList.remove('sso-loading');

    } // end if;

  }; // end sso;

  (function clean_up_query_args() {

    if (window.history.replaceState) {

      window.history.replaceState(null, null, o.filtered_url + window.location.hash);

    } // end if;

  }());

}(wu_sso_config));
