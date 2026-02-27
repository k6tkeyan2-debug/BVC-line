// Sticky Contact Widget - shared script
(function() {
  document.addEventListener('DOMContentLoaded', function() {
    var widget = document.getElementById('sticky-contact-widget');
    var sidebar = document.getElementById('qodef-page-sidebar');
    var footer = document.getElementById('qodef-page-footer');
    if (!widget || !sidebar || !footer) return;

    var origOffset = widget.getBoundingClientRect().top + window.pageYOffset;
    var origWidth = widget.offsetWidth;

    function getStickyOffset() {
      return window.innerWidth <= 991 ? 60 : 90;
    }

    function onScroll() {
      var scrollY = window.pageYOffset || document.documentElement.scrollTop;
      var stickyOffset = getStickyOffset();
      var widgetHeight = widget.offsetHeight;
      var footerTop = footer.getBoundingClientRect().top + window.pageYOffset;
      var stopPoint = footerTop - widgetHeight - 80;

      if (scrollY + stickyOffset >= origOffset && scrollY < stopPoint) {
        widget.classList.add('sticky');
        widget.style.width = origWidth + 'px';
        widget.style.top = stickyOffset + 'px';
      } else {
        widget.classList.remove('sticky');
        widget.style.width = '';
        widget.style.top = '';
      }
    }

    var lastScrollY = window.scrollY;
    var ticking = false;
    var stickyClass = 'sticky-animated';

    function onScrollAnimation() {
        if (!ticking) {
            window.requestAnimationFrame(function() {
                var rect = widget.getBoundingClientRect();
                var isSticky = rect.top <= 30 && rect.bottom > 0;
                if (isSticky) {
                    widget.classList.add(stickyClass);
                } else {
                    widget.classList.remove(stickyClass);
                }
                ticking = false;
            });
            ticking = true;
        }
    }

    window.addEventListener('scroll', onScroll, {passive:true});
    window.addEventListener('scroll', onScrollAnimation, { passive: true });
    window.addEventListener('resize', function() {
      origWidth = widget.offsetWidth;
      onScroll();
    });
    onScroll();
  });
})();
