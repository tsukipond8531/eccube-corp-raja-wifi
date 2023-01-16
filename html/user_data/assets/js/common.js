/* GLOBAL */
var viewportW = jQuery(window).width(),
  viewportH = jQuery(window).height(),
  documentH = 0,
  viewportSP = 639,
  opacity = "opacity:0";

$(document).ready(function () {
  //FIX IE
  0 <
    (function () {
      var a = window.navigator.userAgent,
        b = a.indexOf("MSIE");
      return 0 < b
        ? parseInt(a.substring(b + 5, a.indexOf(".", b)))
        : navigator.userAgent.match(/Trident\/7\./)
        ? 11
        : 0;
    })() && $("html").addClass("fixie");

  //DETECT
  var userAgent = window.navigator.userAgent;
  userAgent.match(/iPhone/i) && $("body").addClass("ios");
  "6" === iPhoneVersion() && $("body").addClass("iphone6");
  "X" === iPhoneVersion() && $("body").addClass("iphoneX");
  "Plus" === iPhoneVersion() && $("body").addClass("iphonePlus");
  var isChrome = !!window.chrome,
    isFirefox = userAgent.toLowerCase().indexOf("firefox") > -1;
  isSafari = !!window.safari;
  isEdge = userAgent.indexOf("Edge") > -1;
  isAndroid = navigator.userAgent.toLowerCase().indexOf("android") > -1;
  isChrome && !$("body").hasClass("ios") && $("body").addClass("chrome");
  isSafari && !$("body").hasClass("android") && $("body").addClass("safari");
  isFirefox && !$("body").hasClass("android") && $("body").addClass("firefox");
  if (isEdge && $("body").hasClass("chrome")) {
    $("body").removeClass("chrome");
    $("body").addClass("edge");
  }
  if (isAndroid) {
    $("body").addClass("android");
  }
  //END DETECT

  //LOAD FUNCTION
  load_function();
  jQuery(window)
    .resize(function () {
      viewportW = jQuery(window).width();
      viewportH = jQuery(window).height();
    })
    .resize();
  //END LOAD FUNCTION

  //WINDOW SCROLL ADD CLASS
  $(window).scroll(function () {
    var a = $(window).scrollTop();
    150 < a
      ? $("body").addClass("over_150")
      : $("body").removeClass("over_150");
    1400 > $(window).width()
      ? 250 < a
        ? $("body").addClass("over_500")
        : $("body").removeClass("over_500")
      : 250 < a
      ? $("body").addClass("over_500")
      : $("body").removeClass("over_500");
  });
  //END WINDOW SCROLL ADD CLASS

  //ICON NAV MENU
  $(".js-hamburger").click(function (a) {
    a.stopPropagation();
    $('.blockuser').removeClass('show');
    $("body").toggleClass("open--menu");
    $(".header__nav").toggleClass("show");
    $(this).toggleClass("active");
    $("body").hasClass("open--menu")
      ? ($(document).height(),
        $("body.open--menu").css({
          height: "100%",
        }))
      : ($("body").css({
          height: "auto",
        }));
  });

  // HAMBEGER BUTTON MENU SP CLICK
  // $('.hamburger').click(function() {
  //   if ($(window).width() <= 768) {
  //     $('#header .mainMenu').stop().slideToggle(800);
  //     $('body').toggleClass('fixed');
  //     $(this).toggleClass('change');
  //   }
  // });
  $('.mainMenuWrap .btnclose a').click(function() {
      if ($(window).width() <= 1000) {
        $('.header .header__nav').removeClass('show');
        $('body').removeClass('open--menu');
        $('.js-hamburger').removeClass('active');
      }
  });

  $('.icon--user').click(function (e) {
    $('.blockuser').toggleClass('show');
    if($('body').hasClass('open--menu')) {
      $('body').removeClass('open--menu');
      $(".header__nav").removeClass("show");
    }
  })

  //FIX SCROLL WHEN HEADER FIX
  var heightHD = $("header").outerHeight();
  $(".js-archorlink").each(function () {
    $(this).css({
      "padding-top": heightHD + 10,
      "margin-top": -heightHD,
    });
  });
  //END FIX SCROLL WHEN HEADER FIX

  //BACKTO TOP
  if ($(".backtop").length) {
    var scrollTrigger = 300, // px
      // scroll to display and automatic hide BACK TO TOP after (x) second
      hideTimeout = 0,
      backToTop = function (second) {
        var scrollTop = $(window).scrollTop();
        if (scrollTop > scrollTrigger) {
          if (second && second > 0) {
            clearTimeout(hideTimeout);
            hideTimeout = setTimeout(function () {
              $(".backtop").fadeOut();
            }, second * 1000);
          }
          $(".backtop").fadeIn();
        } else {
          $(".backtop").fadeOut();
        }
      };
    backToTop(5);
    $(window).on("scroll", function () {
      backToTop(5);
    });
    $(".backtop").on("click", function (e) {
      e.preventDefault();
      $("html,body").animate(
        {
          scrollTop: 0,
        },
        700
      );
    });
  }
  //END BACKTO TOP
  if ($('.slick-slider > div').length) {
    $('.slick-slider').slick({
      slidesToShow: 1,
      centerMode: false,
      arrows: true
    });
  }

  if(viewportW < 769) {
    if ($('.slick2').length) {
      $('.slick2').slick({
        centerMode: true,
        centerPadding: '20px',
        slidesToShow: 1,
        arrows: false,
        autoplay: true,
      });
    }
    if ($('.slick3').length) {
      $('.slick3').slick({
        centerMode: true,
        centerPadding: '20px',
        slidesToShow: 1,
        arrows: false,
        autoplay: true,
        variableWidth: true,
      });
    }
  }

  // $(document).on('click', function(e) {
  //   if (jQuery(window).width() < 769 ){
  //     var el = $(".icon--user");
  //     if (!el.is(event.target) && !el.has(event.target).length) {
  //       // el.removeClass('show');
  //       $('.blockuser').removeClass('show');
  //     } 
  //   }
  // });
});

//LOAD FUNCTION ------------------------------------------------
function load_function() {
  pageReload();
  // smartRollover();
  accordion();
  linkAnchor();
}

//CREAT FUNCTION ------------------------------------------------

//RELOAD PAGE WHEN CHANGE VIEWPORT PC <=> SP
function pageReload() {
  var a;
  var b = viewportW > viewportSP ? "is_pc" : "is_smp";
  jQuery(window)
    .resize(function () {
      viewportW = jQuery(window).width();
      a = viewportW > viewportSP ? "is_pc" : "is_smp";
      b != a && (window.location.href = window.location.href);
    })
    .resize();
}
//END RELOAD PAGE WHEN CHANGE VIEWPORT PC <=> SP

//TOGGLE TEXT
$.fn.extend({
  toggleText: function (a, b) {
    return this.text(this.text() == b ? a : b);
  },
});
//END TOGGLE TEXT

//LINK ANCHOR
function linkAnchor() {
  $("a[href*=#]:not([href=#])").click(function () {
    if (
      location.pathname.replace(/^\//, "") ==
        this.pathname.replace(/^\//, "") &&
      location.hostname == this.hostname
    ) {
      var a = $(this.hash);
      a = a.length ? a : $("[name=" + this.hash.slice(1) + "]");
      if (a.length)
        return (
          $("html,body").animate(
            {
              scrollTop: a.offset().top,
            },
            300
          ),
          !1
        );
    }
  });
}
//END LINK ANCHOR

//ACCORDION BOX
function accordion() {
  $(".acr_title").on("click", function (a) {
    a.preventDefault();
    a = $(this);
    var b = a.next(".acr_con");
    $(".acr_title").not($(this)).removeClass("open");
    $(".acr_con").not($(this).next()).slideUp("fast");
    a.toggleClass("open");
    b.slideToggle(250);
  });
}
//END ACCORDION BOX

//FIX HEIGHT ELEMENT
//use element.tile(columns)
(function (a) {
  a.fn.tile = function (b) {
    var c,
      e,
      f,
      g,
      h = this.length - 1,
      d;
    b || (b = this.length);
    this.each(function () {
      d = this.style;
      d.removeProperty && d.removeProperty("height");
      d.removeAttribute && d.removeAttribute("height");
    });
    return this.each(function (d) {
      f = d % b;
      0 == f && (c = []);
      c[f] = a(this);
      g = c[f].height();
      if (0 == f || g > e) e = g;
      (d != h && f != b - 1) ||
        a.each(c, function () {
          this.height(e);
        });
    });
  };
})(jQuery);
//END FIX HEIGHT ELEMENT

//DETECT IHONE VERSION
function iPhoneVersion() {
  var a = window.screen.height,
    b = window.screen.width;
  return 320 === b && 480 === a
    ? "4"
    : 320 === b && 568 === a
    ? "5"
    : 375 === b && 667 === a
    ? "6"
    : 414 === b && 736 === a
    ? "Plus"
    : 375 === b && 812 === a
    ? "X"
    : "none";
}
//END DETECT IHONE VERSION

//Disable double on Ipad, Iphone For Zooming
(function ($) {
  $.fn.nodoubletapzoom = function () {
    $(this).bind("touchstart", function preventZoom(e) {
      var t2 = e.timeStamp,
        t1 = $(this).data("lastTouch") || t2,
        dt = t2 - t1,
        fingers = e.originalEvent.touches.length;
      $(this).data("lastTouch", t2);
      if (!dt || dt > 500 || fingers > 1) return; // not double-tap

      e.preventDefault(); // double tap - prevent the zoom
      // also synthesize click events we just swallowed up
      $(this).trigger("click").trigger("click");
    });
  };
})(jQuery);

//TEL LINK
$(function () {
  var ua = navigator.userAgent;
  if (ua.indexOf('iPhone') > 0 && ua.indexOf('iPod') == -1 || ua.indexOf('Android') > 0 && ua.indexOf('Mobile') > 0) {
    $('.tel-link').each(function () {
      var str = $(this).text();
      $(this).html($('<a>').attr('href', 'tel:' + str.replace(/-/g, '')).append(str + '</a>'));
    });
  }
});
$(function () {
  var ua = navigator.userAgent;
  if (ua.indexOf('iPhone') > 0 && ua.indexOf('iPod') == -1 || ua.indexOf('Android') > 0 && ua.indexOf('Mobile') > 0 && ua.indexOf('SC-01C') == -1 && ua.indexOf('A1_07') == -1) {
    $('.tel-link-img img').each(function () {
      var alt = $(this).attr('alt');
      $(this).wrap($('<a>').attr('href', 'tel:' + alt.replace(/-/g, '')));
    });
  }
});
//HOVER CHANGE IMG
$(".js-hoverimg a")
  .mouseover(function (a) {
    $(this)
      .find("img")
      .attr("src", $(this).find("img").attr("src").replace("_off", "_on"));
  })
  .mouseout(function (a) {
    $(this)
      .find("img")
      .attr("src", $(this).find("img").attr("src").replace("_on", "_off"));
  });
//END HOVER CHANGE IMG