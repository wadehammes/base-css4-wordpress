/**
 *
 * Navigation
 *
 */
 var menuToggle = $('.menu-toggle, .menu-close'),
     siteSlide = $('.site-slide'),
     navInner = $('.nav-inner');

 menuToggle.on('click', function(e) {
   $('.arrow').toggleClass('open');
   siteSlide.toggleClass('peek');
   navInner.toggleClass('fade');
   e.preventDefault();
 });

 /*=============================================>>>>>
 = Waypoints =
 ===============================================>>>>>*/
 var waypoints = $('.nav-on').waypoint({
  handler: function(direction) {
    $('.header-nav').toggleClass('on')
  },
  offset: '150px'
});

var waypoints = $('.video').waypoint({
  handler: function(direction) {
    $('.header-nav').toggleClass('slide')
  },
  offset: '-1em'
});


/*=============================================>>>>>
= Processes =
===============================================>>>>>*/
/*
function animateItems(items, index) {
  index = index % items.length;
  items.eq(index).addClass('animated fadeIn');
  setTimeout(function() {animateItems(items, index + 1)}, 250);
}

var waypoints = $('#process').waypoint({
  handler: function(direction) {
    if(direction === 'down') {
      animateItems($('.process'), 0);
    }
  },
  offset: '45%'
});
*/

/*=============================================>>>>>
= Scrolling =
===============================================>>>>>*/
$(function() {
  $('a[href*="#"]:not([href="#"])').click(function() {
    if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
      var target = $(this.hash);
      target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
      if (target.length) {
        $('html, body').animate({
          scrollTop: target.offset().top - 85
        }, 1000);
        return false;
      }
    }
  });
});

/*=================================
=            Instafeed            =
=================================*/
// Pull Instagram images
var feed = new Instafeed({
  clientId: '47da14e8e9a54eefacdf9ac9300f7609',
  get: 'user',
  userId: '2894746024',
  accessToken: '2894746024.47da14e.355006956663490d9b6c38afc0fab732',
  target: 'instagram',
  sortBy: 'random',
  resolution: 'standard_resolution',
  limit: 20,
  template: '<div class="instagram-image relative"><a id="{{id}}" class="inline-block" data-orientation="{{orientation}}" href="{{link}}" target="_blank"><img src="{{image}}" alt="{{caption}}" /><figcaption><span><i class="block text-large fa fa-instagram mb1"></i><br />{{caption}}</span></figcaption></a></div>',
  after: function() {
    $('.instagram').owlCarousel({
      autoplay:true,
      autoplayHoverPause: true,
      loop:true,
      margin:0,
      nav:false,
      dots: false,
      responsive:{
        0:{
          items:1
        },
        641:{
          items:2
        },
        769:{
          items:3
        },
        1000:{
          items:4
        },
        1600:{
          items:6
        }
      }
    });
  }
});
feed.run();
