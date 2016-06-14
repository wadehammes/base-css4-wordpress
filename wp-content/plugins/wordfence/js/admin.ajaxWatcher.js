(function($) {
	if (!window['wordfenceAJAXWatcher']) {
		window['wordfenceAJAXWatcher'] = {
			blockWarningOpen: false,
			
			init: function() {
				$(document).ajaxError(function (event, jqXHR, ajaxSettings, thrownError) {
					if (wordfenceAJAXWatcher.blockWarningOpen) {
						return;
					}
					
					var requestURL = ajaxSettings.url;
					if (requestURL.length > 63) {
						requestURL = requestURL.substring(0, 30) + '...' + requestURL.substring(requestURL.length - 30);
					}
					var requestURLEscaped = $('<div/>').text(requestURL).html();
					var responseDOM = $(jqXHR.responseText);
					var formAction = responseDOM.filter('#whitelist-form').add(responseDOM.find('#whitelist-form')).attr('action');
					var inputs = responseDOM.filter('input[name]').add(responseDOM.find('input[name]'));
					var queryParams = {}; 
					for (var i = 0; i < inputs.length; i++) {
						queryParams[inputs[i].name] = inputs[i].value;
					}
					
					if (!(typeof formAction === "string")) { //Only progress if it's our plugin doing the blocking
						return;
					}

					wordfenceAJAXWatcher.blockWarningOpen = true;
					$.colorbox({
						closeButton: false,
						width: '400px',
						html: "<h3>Background Request Blocked</h3><p>A background request to WordPress was just blocked for the URL <code>" + requestURLEscaped + "</code>. If this occurred as a result of an intentional action, you may consider whitelisting the request to allow it in the future.</p><p class=\"textright\"><a href=\"#\" class=\"button\" id=\"background-block-whitelist\">Whitelist this action</a> <a href=\"#\" class=\"button\" id=\"background-block-dismiss\">Dismiss</a></p>",
						onComplete: function() {
							$('#background-block-dismiss').click(function(event) {
								event.preventDefault();
								event.stopPropagation();
								$.colorbox.close();
							});
							
							$('#background-block-whitelist').click(function(event) {
								event.preventDefault();
								event.stopPropagation();

								if (confirm('Are you sure you want to whitelist this action?')) {
									$.ajax({
										method: 'POST',
										url: formAction,
										data: queryParams,
										global: false,
										success: function() {
											alert('The request has been whitelisted. Please try it again.');
											$.colorbox.close();
										},
										error: function() {
											alert('An error occurred when adding the request to the whitelist.');
											$.colorbox.close();
										}
									});
								}
							});
						},
						onClosed: function() {
							wordfenceAJAXWatcher.blockWarningOpen = false;
						}
					});
				});
			}
		}
	}
	$(function() {
		wordfenceAJAXWatcher.init();
	});
})(jQuery);