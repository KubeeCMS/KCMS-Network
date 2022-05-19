/* global wu_support_vars, Beacon, wu_block_ui, _wu_block_ui_polyfill */

let wu_install_support_widget_done = false;

const wu_install_support_widget = function() {

  if (wu_install_support_widget_done === true) {

    return;

  } // end if;

  /* eslint-disable */
  !function (e, t, n) { function a() { var e = t.getElementsByTagName("script")[0], n = t.createElement("script"); n.type = "text/javascript", n.async = !0, n.src = "https://beacon-v2.helpscout.net", e.parentNode.insertBefore(n, e) } if (e.Beacon = n = function (t, n, a) { e.Beacon.readyQueue.push({ method: t, options: n, data: a }) }, n.readyQueue = [], "complete" === t.readyState) return a(); e.attachEvent ? e.attachEvent("onload", a) : e.addEventListener("load", a, !1) }(window, document, window.Beacon || function () { });

  window.Beacon('init', '687a385f-df79-4b37-b6a9-7114a7d3d586');

  /* eslint-enable */

  wu_install_support_widget_done = true;

};

const hs_beacon = document.getElementsByClassName('wu-trigger-support');

if (hs_beacon.length) {

  hs_beacon[0].addEventListener('click', function(a) {

    wu_install_support_widget();

    const blocked_ui = wu_support_vars.should_use_polyfills ? _wu_block_ui_polyfill('#wpcontent') : wu_block_ui('#wpcontent');

    a.preventDefault();

    setTimeout(function() {

      Beacon('identify', {
        avatar: wu_support_vars.avatar, // Avatar
        name: wu_support_vars.display_name, // Full name
        email: wu_support_vars.email, // Email address
        licenseKey: wu_support_vars.license_key, // License key
        signature: wu_support_vars.signature,
      });

      if (wu_support_vars.subject || wu_support_vars.message) {

        Beacon('prefill', {
          subject: wu_support_vars.subject,
          text: wu_support_vars.message,
        });

      } // end if;

      Beacon('on', 'open', function() {

        if (blocked_ui) {

          blocked_ui.unblock();

        } // end if;

      });

      Beacon('open');

    }, 5E3);

  }, false);

} // end if;
