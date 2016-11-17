/*=========================================
=         Register Service Worker         =
=========================================*/
// check if Service Worker is supported
if ('serviceWorker' in navigator) {
  // register the Service Worker, must be in the root directory to have site-wide scope...
  navigator.serviceWorker.register('/serviceworker.js')
  .then(function(registration) {
    // registration succeeded :-)
    console.log('ServiceWorker registration succeeded, with this scope: ', registration.scope);
  }).catch(function(err) {
    // registration failed :-(
    console.log('ServiceWorker registration failed: ', err);
  });
}

/*=========================================
=            Show Comment Form            =
=========================================*/
$('#comment-button').on('click', function(e){
  $('#disqus_thread').load('/wp-content/themes/base/partials/disqus.php');
  $(this).remove();
  e.preventDefault();
});
