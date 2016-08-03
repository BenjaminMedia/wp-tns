<?php 

require_once(__DIR__.'/admin.php');

class Tns {
	config = array(
			's' => TnsAdmin::getTnsOption('s', false),
			'cp' => TnsAdmin::getTnsOption('cp', false),
			'url' => home_url()
		);
	unispringJsFile = __DIR__.'/js/unispring.js';

	function implementTns(){
		// load the unispring script first
		wp_enqueue_script( 'tns-norway' , $this->unispringJsFile, array(), '', 'all', true);

		if($this->config['cp'],false)){

			echo <<<HTML
				<script src="http://www.cathinthecity.com/wp-content/themes/stylista/js/unispring.js"></script>
				<script type="text/javascript">
				var sp_e0 = {
				 "s":$this->config['s'],
				 "cp":$this->config['cp'],
				 "url": window.location.toString()
				}
				unispring.c(sp_e0);
				</script><noscript> <img
				src="http://mmk.tns-cs.net/j0=,,,;+,cp=$this->config['cp']+url=$this->config['url'];;;" alt="tns-tracking"> </noscript>
HTML
		}
	}

	function __construct(){
		add_action('wp_footer',array($this, 'implementTns'));
	}
}

new Tns();