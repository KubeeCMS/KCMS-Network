/*
 * Thickbox 3.1 - One Box To Rule Them All.
 * By Cody Lindley (http://www.codylindley.com)
 * Copyright (c) 2007 cody lindley
 * Licensed under the MIT License: http://www.opensource.org/licenses/mit-license.php
*/

if (typeof wutb_pathToImage != 'string') {
  var wutb_pathToImage = wuboxL10n.loadingAnimation;
}

/*!!!!!!!!!!!!!!!!! edit below this line at your own risk !!!!!!!!!!!!!!!!!!!!!!!*/

//on page load call wutb_init
jQuery(document).ready(function () {
  wutb_init('a.wubox, area.wubox, input.wubox');//pass where to apply wubox
  imgLoader = new Image();// preload image
  imgLoader.src = wutb_pathToImage;
});

/*
 * Add wubox to href & area elements that have a class of .wubox.
 * Remove the loading indicator when content in an iframe has loaded.
 */
function wutb_init(domChunk) {
  jQuery('body')
    .on('click', domChunk, wutb_click)
    .on('wubox:iframe:loaded', function () {
      jQuery('#WUB_window').removeClass('wubox-loading');
    });
}

function wutb_click() {
  var t = this.title || this.name || null;
  var a = this.href || this.alt;
  var g = this.rel || false;
  wutb_show(t, a, g);
  this.blur();
  return false;
}

function wutb_show(caption, url, imageGroup) {//function called when the user clicks on a wubox link

  var $closeBtn;

  try {
    if (typeof document.body.style.maxHeight === "undefined") {//if IE 6
      jQuery("body", "html").css({ height: "100%", width: "100%" });
      jQuery("html").css("overflow", "hidden");
      if (document.getElementById("WUB_HideSelect") === null) {//iframe to hide select elements in ie6
        jQuery("body").append("<iframe id='WUB_HideSelect'>" + wuboxL10n.noiframes + "</iframe><div id='WUB_overlay'></div><div id='WUB_window' class='wubox-loading'></div>");
        jQuery("#WUB_overlay").click(wutb_remove);
      }
    } else {//all others
      if (document.getElementById("WUB_overlay") === null) {
        jQuery("body").append("<div id='WUB_overlay'></div><div id='WUB_window' class='wubox-loading'></div>");
        jQuery("#WUB_overlay").click(wutb_remove);
        jQuery('body').addClass('modal-open');
      }
    }

    if (wutb_detectMacXFF()) {
      jQuery("#WUB_overlay").addClass("WUB_overlayMacFFBGHack");//use png overlay so hide flash
    } else {
      jQuery("#WUB_overlay").addClass("WUB_overlayBG");//use background and opacity
    }

    if (caption === null) { caption = ""; }
    jQuery("body").append("<div id='WUB_load'></div>");//add loader to the page
    jQuery('#WUB_load').show();//show loader

    var baseURL;
    if (url.indexOf("?") !== -1) { //ff there is a query string involved
      baseURL = url.substr(0, url.indexOf("?"));
    } else {
      baseURL = url;
    }

    var urlString = /\.jpg$|\.jpeg$|\.png$|\.gif$|\.bmp$/;
    var urlType = baseURL.toLowerCase().match(urlString);

    if (urlType == '.jpg' || urlType == '.jpeg' || urlType == '.png' || urlType == '.gif' || urlType == '.bmp') {//code to show images

      WUB_PrevCaption = "";
      WUB_PrevURL = "";
      WUB_PrevHTML = "";
      WUB_NextCaption = "";
      WUB_NextURL = "";
      WUB_NextHTML = "";
      WUB_imageCount = "";
      WUB_FoundURL = false;
      if (imageGroup) {
        WUB_TempArray = jQuery("a[rel=" + imageGroup + "]").get();
        for (WUB_Counter = 0; ((WUB_Counter < WUB_TempArray.length) && (WUB_NextHTML === "")); WUB_Counter++) {
          var urlTypeTemp = WUB_TempArray[WUB_Counter].href.toLowerCase().match(urlString);
          if (!(WUB_TempArray[WUB_Counter].href == url)) {
            if (WUB_FoundURL) {
              WUB_NextCaption = WUB_TempArray[WUB_Counter].title;
              WUB_NextURL = WUB_TempArray[WUB_Counter].href;
              WUB_NextHTML = "<span id='WUB_next'>&nbsp;&nbsp;<a href='#'>" + wuboxL10n.next + "</a></span>";
            } else {
              WUB_PrevCaption = WUB_TempArray[WUB_Counter].title;
              WUB_PrevURL = WUB_TempArray[WUB_Counter].href;
              WUB_PrevHTML = "<span id='WUB_prev'>&nbsp;&nbsp;<a href='#'>" + wuboxL10n.prev + "</a></span>";
            }
          } else {
            WUB_FoundURL = true;
            WUB_imageCount = wuboxL10n.image + ' ' + (WUB_Counter + 1) + ' ' + wuboxL10n.of + ' ' + (WUB_TempArray.length);
          }
        }
      }

      imgPreloader = new Image();
      imgPreloader.onload = function () {
        imgPreloader.onload = null;

        // Resizing large images - original by Christian Montoya edited by me.
        var pagesize = wutb_getPageSize();
        var x = pagesize[0] - 150;
        var y = pagesize[1] - 150;
        var imageWidth = imgPreloader.width;
        var imageHeight = imgPreloader.height;
        if (imageWidth > x) {
          imageHeight = imageHeight * (x / imageWidth);
          imageWidth = x;
          if (imageHeight > y) {
            imageWidth = imageWidth * (y / imageHeight);
            imageHeight = y;
          }
        } else if (imageHeight > y) {
          imageWidth = imageWidth * (y / imageHeight);
          imageHeight = y;
          if (imageWidth > x) {
            imageHeight = imageHeight * (x / imageWidth);
            imageWidth = x;
          }
        }
        // End Resizing

        WUB_WIDTH = imageWidth;
        WUB_HEIGHT = imageHeight;
        jQuery("#WUB_window").append("<a href='' id='WUB_ImageOff'><span class='screen-reader-text'>" + wuboxL10n.close + "</span><img id='WUB_Image' src='" + url + "' width='" + imageWidth + "' height='" + imageHeight + "' alt='" + caption + "'/></a>" + "<div id='WUB_caption'>" + caption + "<div id='WUB_secondLine'>" + WUB_imageCount + WUB_PrevHTML + WUB_NextHTML + "</div></div><div id='WUB_closeWindow'><button type='button' id='WUB_closeWindowButton'><span class='screen-reader-text'>" + wuboxL10n.close + "</span><span class='wutb-close-icon'></span></button></div>");

        jQuery("#WUB_closeWindowButton").click(wutb_remove);

        if (!(WUB_PrevHTML === "")) {
          function goPrev() {
            if (jQuery(document).unbind("click", goPrev)) { jQuery(document).unbind("click", goPrev); }
            jQuery("#WUB_window").remove();
            jQuery("body").append("<div id='WUB_window'></div>");
            wutb_show(WUB_PrevCaption, WUB_PrevURL, imageGroup);
            return false;
          }
          jQuery("#WUB_prev").click(goPrev);
        }

        if (!(WUB_NextHTML === "")) {
          function goNext() {
            jQuery("#WUB_window").remove();
            jQuery("body").append("<div id='WUB_window'></div>");
            wutb_show(WUB_NextCaption, WUB_NextURL, imageGroup);
            return false;
          }
          jQuery("#WUB_next").click(goNext);

        }

        jQuery(document).bind('keydown.wubox', function (e) {
          if (e.which == 27) { // close
            wutb_remove();

          } else if (e.which == 190) { // display previous image
            if (!(WUB_NextHTML == "")) {
              jQuery(document).unbind('wubox');
              goNext();
            }
          } else if (e.which == 188) { // display next image
            if (!(WUB_PrevHTML == "")) {
              jQuery(document).unbind('wubox');
              goPrev();
            }
          }
          return false;
        });

        wutb_position();
        jQuery("#WUB_load").remove();
        jQuery("#WUB_ImageOff").click(wutb_remove);
        jQuery("#WUB_window").css({ 'visibility': 'visible' }); //for safari using css instead of show
      };

      imgPreloader.src = url;
    } else {//code to show html

      var queryString = url.replace(/^[^\?]+\??/, '');
      var params = wutb_parseQuery(queryString);

      WUB_WIDTH = (params['width'] * 1) || 630; //defaults to 630 if no parameters were added to URL
      WUB_HEIGHT = (params['height'] * 1) || 440; //defaults to 440 if no parameters were added to URL
      ajaxContentW = WUB_WIDTH;
      ajaxContentH = WUB_HEIGHT;

      if (url.indexOf('WUB_iframe') != -1) {// either iframe or ajax window
        urlNoQuery = url.split('WUB_');
        jQuery("#WUB_iframeContent").remove();
        if (params['modal'] != "true") {//iframe no modal
          jQuery("#WUB_window").append("<div id='WUB_title'><div id='WUB_ajaxWindowTitle'>" + caption + "</div><div id='WUB_closeAjaxWindow'><button type='button' id='WUB_closeWindowButton'><span class='screen-reader-text'>" + wuboxL10n.close + "</span><span class='wutb-close-icon'></span></button></div></div><iframe frameborder='0' hspace='0' allowtransparency='true' src='" + urlNoQuery[0] + "' id='WUB_iframeContent' name='WUB_iframeContent" + Math.round(Math.random() * 1000) + "' onload='wutb_showIframe()' style='width:" + (ajaxContentW + 29) + "px;height:" + (ajaxContentH + 17) + "px;' >" + wuboxL10n.noiframes + "</iframe>");
        } else {//iframe modal
          jQuery("#WUB_overlay").unbind();
          jQuery("#WUB_window").append("<iframe frameborder='0' hspace='0' allowtransparency='true' src='" + urlNoQuery[0] + "' id='WUB_iframeContent' name='WUB_iframeContent" + Math.round(Math.random() * 1000) + "' onload='wutb_showIframe()' style='width:" + (ajaxContentW + 29) + "px;height:" + (ajaxContentH + 17) + "px;'>" + wuboxL10n.noiframes + "</iframe>");
        }
      } else {// not an iframe, ajax
        if (jQuery("#WUB_window").css("visibility") != "visible") {
          if (params['modal'] != "true") {//ajax no modal
            jQuery("#WUB_window").append("<div id='WUB_title'><div id='WUB_ajaxWindowTitle'>" + caption + "</div><div id='WUB_closeAjaxWindow'><button type='button' id='WUB_closeWindowButton'><span class='screen-reader-text'>" + wuboxL10n.close + "</span><span class='wutb-close-icon'></span></button></div></div><div id='WUB_ajaxContent' style='width:" + ajaxContentW + "px;height:" + ajaxContentH + "px'></div>");
          } else {//ajax modal
            jQuery("#WUB_overlay").unbind();
            jQuery("#WUB_window").append("<div id='WUB_ajaxContent' class='WUB_modal' style='width:" + ajaxContentW + "px;height:" + ajaxContentH + "px;'></div>");
          }
        } else {//this means the window is already up, we are just loading new content via ajax
          jQuery("#WUB_ajaxContent")[0].style.width = ajaxContentW + "px";
          jQuery("#WUB_ajaxContent")[0].style.height = ajaxContentH + "px";
          jQuery("#WUB_ajaxContent")[0].scrollTop = 0;
          jQuery("#WUB_ajaxWindowTitle").html(caption);
        }
      }

      jQuery("#WUB_closeWindowButton").click(wutb_remove);

      if (url.indexOf('WUB_inline') != -1) {
        jQuery("#WUB_ajaxContent").append(jQuery('#' + params['inlineId']).children());
        jQuery("#WUB_window").bind('wutb_unload', function () {
          jQuery('#' + params['inlineId']).append(jQuery("#WUB_ajaxContent").children()); // move elements back when you're finished
        });
        wutb_position();
        jQuery("#WUB_load").remove();
        jQuery("#WUB_window").css({ 'visibility': 'visible' });
        jQuery('body').trigger('wubox:load');
      } else if (url.indexOf('WUB_iframe') != -1) {
        wutb_position();
        jQuery("#WUB_load").remove();
        jQuery("#WUB_window").css({ 'visibility': 'visible' });
        jQuery('body').trigger('wubox:load');
      } else {
        var load_url = url;
        load_url += -1 === url.indexOf('?') ? '?' : '&';
        jQuery("#WUB_ajaxContent").load(load_url += "random=" + (new Date().getTime()), function () {//to do a post change this load method
          wutb_position();
          jQuery("#WUB_load").remove();
          wutb_init("#WUB_ajaxContent a.wubox");
          jQuery("#WUB_window").css({ 'visibility': 'visible' });
          jQuery('body').trigger('wubox:load');
        });
      }

    }

    if (!params['modal']) {
      jQuery(document).bind('keydown.wubox', function (e) {
        if (e.which == 27) { // close
          wutb_remove();
          return false;
        }
      });
    }

    $closeBtn = jQuery('#WUB_closeWindowButton');
		/*
		 * If the native Close button icon is visible, move focus on the button
		 * (e.g. in the Network Admin Themes screen).
		 * In other admin screens is hidden and replaced by a different icon.
		 */
    if ($closeBtn.find('.wutb-close-icon').is(':visible')) {
      $closeBtn.focus();
    }

  } catch (e) {
    //nothing here
  }
}

//helper functions below
function wutb_showIframe() {
  jQuery("#WUB_load").remove();
  jQuery("#WUB_window").css({ 'visibility': 'visible' }).trigger('wubox:iframe:loaded');
}

function wutb_remove() {
  jQuery("#WUB_imageOff").unbind("click");
  jQuery("#WUB_closeWindowButton").unbind("click");
  jQuery('#WUB_window').fadeOut('fast', function () {
    jQuery('#WUB_window, #WUB_overlay, #WUB_HideSelect').trigger('wutb_unload').unbind().remove();
    jQuery('body').trigger('wubox:removed');
  });
  jQuery('body').removeClass('modal-open');
  jQuery("#WUB_load").remove();
  if (typeof document.body.style.maxHeight == "undefined") {//if IE 6
    jQuery("body", "html").css({ height: "auto", width: "auto" });
    jQuery("html").css("overflow", "");
  }
  jQuery(document).unbind('.wubox');
  return false;
}

function wutb_position() {
  var isIE6 = typeof document.body.style.maxHeight === "undefined";
  jQuery("#WUB_window").css({ marginLeft: '-' + parseInt((WUB_WIDTH / 2), 10) + 'px', width: WUB_WIDTH + 'px' });
  if (!isIE6) { // take away IE6
    jQuery("#WUB_window").css({ marginTop: '-' + parseInt((WUB_HEIGHT / 2), 10) + 'px' });
  }
}

function wutb_parseQuery(query) {
  var Params = {};
  if (!query) { return Params; }// return empty object
  var Pairs = query.split(/[;&]/);
  for (var i = 0; i < Pairs.length; i++) {
    var KeyVal = Pairs[i].split('=');
    if (!KeyVal || KeyVal.length != 2) { continue; }
    var key = unescape(KeyVal[0]);
    var val = unescape(KeyVal[1]);
    val = val.replace(/\+/g, ' ');
    Params[key] = val;
  }
  return Params;
}

function wutb_getPageSize() {
  var de = document.documentElement;
  var w = window.innerWidth || self.innerWidth || (de && de.clientWidth) || document.body.clientWidth;
  var h = window.innerHeight || self.innerHeight || (de && de.clientHeight) || document.body.clientHeight;
  arrayPageSize = [w, h];
  return arrayPageSize;
}

function wutb_detectMacXFF() {
  var userAgent = navigator.userAgent.toLowerCase();
  if (userAgent.indexOf('mac') != -1 && userAgent.indexOf('firefox') != -1) {
    return true;
  }
}
