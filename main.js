(function ($) {
    "use strict";

    // Spinner
    var spinner = function () {
        setTimeout(function () {
            if ($('#spinner').length > 0) {
                $('#spinner').removeClass('show');
            }
        }, 1);
    };
    spinner(0);
    
    // Initiate the wowjs (guard if not present)
    if (typeof WOW !== 'undefined') {
      new WOW().init();
    }

    // Sticky Navbar
    $(window).scroll(function () {
        if ($(this).scrollTop() > 45) {
            $('.navbar').addClass('sticky-top shadow-sm');
        } else {
            $('.navbar').removeClass('sticky-top shadow-sm');
        }
    });

    // Modal Video
    $(document).ready(function () {
        var $videoSrc;
        $('.btn-play').click(function () {
            $videoSrc = $(this).data("src");
        });
        $('#videoModal').on('shown.bs.modal', function (e) {
            $("#video").attr('src', $videoSrc + "?autoplay=1&amp;modestbranding=1&amp;showinfo=0");
        })
        $('#videoModal').on('hide.bs.modal', function (e) {
            $("#video").attr('src', $videoSrc);
        })
    });
    
   // Back to top button
   $(window).scroll(function () {
    if ($(this).scrollTop() > 300) {
        $('.back-to-top').fadeIn('slow');
    } else {
        $('.back-to-top').fadeOut('slow');
    }
    });
    $('.back-to-top').click(function () {
        $('html, body').animate({scrollTop: 0}, 1500, 'easeInOutExpo');
        return false;
    });

    // Team Carousel Logic
    document.addEventListener('DOMContentLoaded', function () {
      const flex = document.getElementById('teamCarouselFlex');
      const items = flex ? Array.from(flex.querySelectorAll('.item')) : [];
      const dotsContainer = document.getElementById('teamDots');
      const wrapper = document.querySelector('.team-carousel-wrapper');
      let current = 0;
      let teamAutoInterval = null;

      function checkVisible() {
        if (window.innerWidth < 600) return 1;
        if (window.innerWidth < 1000) return 2;
        return 4;
      }
      let visible = checkVisible();

      function clampIndex() {
        const maxIndex = Math.max(0, items.length - visible);
        if (current < 0) current = 0;
        if (current > maxIndex) current = maxIndex;
      }

      function renderDots() {
        if (!dotsContainer) return;
        dotsContainer.innerHTML = '';
        const maxIndex = Math.max(0, items.length - visible);
        for (let i = 0; i <= maxIndex; i++) {
          const dot = document.createElement('span');
          dot.className = 'team-dot' + (i === current ? ' active' : '');
          dot.onclick = () => { current = i; updateCarousel(); };
          dotsContainer.appendChild(dot);
        }
      }

      function updateCarousel() {
        if (!flex || items.length === 0) return;
        clampIndex();
        const itemWidth = items[0].offsetWidth;
        flex.style.transform = `translateX(-${current * itemWidth}px)`;
        renderDots();
      }

      window.moveTeamCarousel = function (dir) {
        if (!flex) return;
        current += dir;
        const maxIndex = Math.max(0, items.length - visible);
        if (current > maxIndex) current = 0;
        if (current < 0) current = maxIndex;
        updateCarousel();
      };

      function onResize() {
        visible = checkVisible();
        updateCarousel();
      }
      window.addEventListener('resize', onResize);
      function startTeamAutoRotate() {
        if (teamAutoInterval) clearInterval(teamAutoInterval);
        teamAutoInterval = setInterval(function () {
          window.moveTeamCarousel(1);
        }, 3500);
      }
      function stopTeamAutoRotate() {
        if (teamAutoInterval) clearInterval(teamAutoInterval);
      }
      if (wrapper) {
        wrapper.addEventListener('mouseenter', stopTeamAutoRotate);
        wrapper.addEventListener('mouseleave', startTeamAutoRotate);
      }
      updateCarousel();
      // Recompute sizes after images load
      window.addEventListener('load', function(){
        updateCarousel();
        startTeamAutoRotate();
      });
    });

    // Video Carousel JS
    let videoIndex = 0;
    let videoVisible = 3;
    let videoAutoInterval = null;
    function updateVideoVisible() {
      if(window.innerWidth <= 600) videoVisible = 1;
      else if(window.innerWidth <= 1000) videoVisible = 2;
      else videoVisible = 3;
    }
    function updateVideoCarousel() {
      const videoCards = document.querySelectorAll('.video-carousel-flex .item');
      const videoFlex = document.getElementById('videoCarouselFlex');
      const videoDots = document.getElementById('videoDots');
      updateVideoVisible();
      const total = videoCards.length;
      if(videoIndex > total-videoVisible) videoIndex = total-videoVisible;
      if(videoIndex < 0) videoIndex = 0;
      const percent = -(100/videoVisible)*videoIndex;
      if(videoFlex) videoFlex.style.transform = `translateX(${percent}%)`;
      // Dots
      let dots = '';
      for(let i=0; i<=total-videoVisible; i++) {
        dots += `<span class="video-dot${i===videoIndex?' active':''}" data-dot="${i}"></span>`;
      }
      if(videoDots) videoDots.innerHTML = dots;
      // Add click event listeners to dots
      if(videoDots) {
        Array.from(videoDots.querySelectorAll('.video-dot')).forEach((dot, i) => {
          dot.onclick = () => {
            videoIndex = i;
            updateVideoCarousel();
          };
        });
      }
    }
    window.moveVideoCarousel = function(dir) {
      const videoCards = document.querySelectorAll('.video-carousel-flex .item');
      updateVideoVisible();
      const total = videoCards.length;
      videoIndex += dir;
      if(videoIndex > total-videoVisible) videoIndex = 0;
      if(videoIndex < 0) videoIndex = total-videoVisible;
      updateVideoCarousel();
    }
    function startVideoAutoRotate() {
      if(videoAutoInterval) clearInterval(videoAutoInterval);
      videoAutoInterval = setInterval(() => {
        const videoCards = document.querySelectorAll('.video-carousel-flex .item');
        updateVideoVisible();
        const total = videoCards.length;
        if(videoIndex < total-videoVisible) {
          videoIndex++;
        } else {
          videoIndex = 0;
        }
        updateVideoCarousel();
      }, 3500);
    }
    function stopVideoAutoRotate() {
      if(videoAutoInterval) clearInterval(videoAutoInterval);
    }
    window.addEventListener('resize', updateVideoCarousel);
    document.addEventListener('DOMContentLoaded', function() {
      updateVideoCarousel();
      startVideoAutoRotate();
      const wrapper = document.querySelector('.video-carousel-wrapper');
      if(wrapper) {
        wrapper.addEventListener('mouseenter', stopVideoAutoRotate);
        wrapper.addEventListener('mouseleave', startVideoAutoRotate);
      }
    });

    // --- Dance Style Page Card Animation ---
    function revealOnScroll() {
      const reveals = document.querySelectorAll('.style-card, .testimonial-card');
      for (let el of reveals) {
        const windowHeight = window.innerHeight;
        const elementTop = el.getBoundingClientRect().top;
        if (elementTop < windowHeight - 60) {
          el.style.opacity = 1;
          el.style.transform = 'none';
        } else {
          el.style.opacity = 0;
          el.style.transform = 'translateY(40px)';
        }
      }
    }
    window.addEventListener('scroll', revealOnScroll);
    window.addEventListener('DOMContentLoaded', revealOnScroll);

    // === Theme Toggle ===
    function applyTheme(theme) {
      const root = document.documentElement;
      if (theme === 'dark') {
        root.classList.add('theme-dark');
      } else {
        root.classList.remove('theme-dark');
      }
      const btn = document.getElementById('themeToggleBtn');
      if (btn) {
        // swap icon: sun for dark mode active, moon otherwise
        btn.innerHTML = theme === 'dark'
          ? '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M6.76 4.84l-1.8-1.79-1.41 1.41 1.79 1.8 1.42-1.42zm10.48 0l1.79-1.8-1.41-1.41-1.8 1.79 1.42 1.42zM12 4a1 1 0 001-1V1h-2v2a1 1 0 001 1zm7 7a1 1 0 001-1h2v2h-2a1 1 0 00-1-1zM4 12a1 1 0 00-1-1H1v2h2a1 1 0 001-1zm8 7a1 1 0 00-1 1v2h2v-2a1 1 0 00-1-1zm7.24.16l1.8 1.79 1.41-1.41-1.79-1.8-1.42 1.42zM4.84 17.24l-1.79 1.8 1.41 1.41 1.8-1.79-1.42-1.42zM12 6a6 6 0 100 12A6 6 0 0012 6z"/></svg>'
          : '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M21.64 13A9 9 0 1111 2.36 7 7 0 1021.64 13z"/></svg>';
      }
    }
    function initThemeToggle() {
      const saved = localStorage.getItem('theme') || 'light';
      applyTheme(saved);
      const btn = document.getElementById('themeToggleBtn');
      if (!btn) return;
      btn.addEventListener('click', function(){
        const current = document.documentElement.classList.contains('theme-dark') ? 'dark' : 'light';
        const next = current === 'dark' ? 'light' : 'dark';
        localStorage.setItem('theme', next);
        applyTheme(next);
      });
    }
    document.addEventListener('DOMContentLoaded', initThemeToggle);
})(jQuery);

