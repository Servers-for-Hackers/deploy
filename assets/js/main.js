/*
 Telephasic by HTML5 UP
 html5up.net | @n33co
 Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
 */
(function($,sr, sc, sk){

    // debouncing function from John Hann
    // http://unscriptable.com/index.php/2009/03/20/debouncing-javascript-methods/
    var debounce = function (func, threshold, execAsap) {
        var timeout;

        return function debounced () {
            var obj = this, args = arguments;
            function delayed () {
                if (!execAsap)
                    func.apply(obj, args);
                timeout = null;
            };

            if (timeout)
                clearTimeout(timeout);
            else if (execAsap)
                func.apply(obj, args);

            timeout = setTimeout(delayed, threshold || 100);
        };
    }
    // smartresize
    jQuery.fn[sr] = function(fn, thresh){  return fn ? this.bind('resize', debounce(fn, thresh)) : this.trigger(sr); };

    // smartchange
    jQuery.fn[sc] = function(fn, thresh){  return fn ? this.bind('change', debounce(fn, thresh)) : this.trigger(sc); };

    // keyup
    jQuery.fn[sk] = function(fn, thresh){  return fn ? this.bind('keyup', debounce(fn, thresh)) : this.trigger(sk); };

})(jQuery,'smartresize', 'smartchange', 'smartkeyup');

(function ($) {
    hljs.initHighlightingOnLoad();


    skel.breakpoints({
        normal: '(max-width: 1280px)',
        narrow: '(max-width: 1080px)',
        narrower: '(max-width: 820px)',
        mobile: '(max-width: 736px)',
        mobilep: '(max-width: 480px)'
    });

    $(function () {

        // Video resizing
        _V_("vjs-video-current").ready(function () {

            var myPlayer = this;

            // Resizing
            var aspectRatio = 641 / 1140;

            function resizeVideoJS() {
                // Get the parent element's actual width
                var width = document.getElementById(myPlayer.id()).parentElement.offsetWidth;
                // Set width to fill parent element, Set height
                myPlayer.width(width).height(width * aspectRatio);
            }

            resizeVideoJS(); // Initialize the function
            $(window).smartresize(function () {
                resizeVideoJS();
            });

        });

        var $window = $(window),
            $body = $('body');

        // Disable animations/transitions until the page has loaded.
        $body.addClass('is-loading');

        $window.on('load', function () {
            $body.removeClass('is-loading');
        });

        // Fix: Placeholder polyfill.
        $('form').placeholder();

        // Prioritize "important" elements on narrower.
        skel.on('+narrower -narrower', function () {
            $.prioritize(
                '.important\\28 narrower\\29',
                skel.breakpoint('narrower').active
            );
        });

        // CSS polyfills (IE<9).
        if (skel.vars.IEVersion < 9)
            $(':last-child').addClass('last-child');

        // Dropdowns.
        $('#nav > ul').dropotron({
            mode: 'fade',
            speed: 300,
            alignment: 'center',
            noOpenerFade: true
        });

        // Off-Canvas Navigation.

        /*
         // Navigation Button.
         $(
         '<div id="navButton">' +
         '<a href="#navPanel" class="toggle"></a>' +
         '</div>'
         )
         .appendTo($body);

         // Navigation Panel.
         $(
         '<div id="navPanel">' +
         '<nav>' +
         '<a href="index.html" class="link depth-0">Home</a>' +
         $('#nav').navList() +
         '</nav>' +
         '</div>'
         )
         .appendTo($body)
         .panel({
         delay: 500,
         hideOnClick: true,
         resetScroll: true,
         resetForms: true,
         side: 'top',
         target: $body,
         visibleClass: 'navPanel-visible'
         });
         */
        // Fix: Remove navPanel transitions on WP<10 (poor/buggy performance).
        if (skel.vars.os == 'wp' && skel.vars.osVersion < 10)
            $('#navButton, #navPanel, #page-wrapper')
                .css('transition', 'none');

    });

})(jQuery);