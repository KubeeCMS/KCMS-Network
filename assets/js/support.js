/* global wu_support_vars */
window.intercomSettings = {
  app_id: wu_support_vars.app_id, // App ID on Intercom
  name: wu_support_vars.display_name, // Full name
  email: wu_support_vars.user_email, // Email address
  user_id: wu_support_vars.license_key, // current_user_id
};

/* eslint-disable */
var event = document.createEvent("Event"); event.initEvent("openintercom", !0, !0); var intercom_trigger = document.getElementsByClassName("wu-trigger-support"); if (intercom_trigger.length) { intercom_trigger[0].addEventListener("click", function (a) { a.preventDefault(); this.dispatchEvent(event); setTimeout(function () { Intercom("show") }, 5E3) }, !1); (function () { var a = window, b = a.Intercom; if ("function" === typeof b) b("reattach_activator"), b("update", a.intercomSettings); else { var d = document, c = function () { c.c(arguments) }; c.q = []; c.c = function (a) { c.q.push(a) }; a.Intercom = c; b = function () { var a = d.createElement("script"); a.type = "text/javascript"; a.async = !0; a.src = "https://widget.intercom.io/widget/" + wu_support_vars.app_id; var b = d.getElementsByTagName("script")[0]; b.parentNode.insertBefore(a, b) }; a.attachEvent ? a.attachEvent("onload", b) : a.addEventListener("openintercom", b, !1) } })()};