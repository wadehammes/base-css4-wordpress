		<footer class="footer bg-white align-left pt4 pb3" role="contentinfo">
			<p>&copy;2015 - <?php echo date('Y'); ?> Base Wordress, by Wade Hammes</p>
		</footer>

	</div>
	<!-- END SITE -->

	<!-- WP FOOTER ENQUEUES -->
	<?php wp_footer(); ?>

	<script type="text/javascript">
		// Add SVG Sprite symbols to document
		var site = document.querySelector(".bs-site");
		var xhr = new XMLHttpRequest();
		xhr.onload = function () {
			var div = document.createElement("div")
			div.innerHTML = this.responseText;
			div.classList.add("bs-svg-symbols");
			div.style.display = "none";
			site.parentNode.insertBefore(div, site.nextSibling);
		}
		xhr.open("get", "<?php echo get_template_directory_uri(); ?>/library/svg/sprite.svg", true);
		xhr.send();
	</script>

</body>
</html>
