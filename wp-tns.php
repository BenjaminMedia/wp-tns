<?php 

/*
    Plugin Name: TNS for WordPress
    Description: Norway TNS tracking script for WordPress
    Author: Frederik Rabøl of Bonnier Interactive
    Version: 0.2
    Author URI: http://bonnierpublications.com
*/

class TnsTracking {
	var $config;

	var $jsFolder = '';

	const PLUGIN_OPTION_NAMESPACE = 'tracking';
	const PLUGIN_OPTION_DIVIDER = '-';
	const PLUGIN_OPTION_ACTIVE_STRING = 'active';
	var $activeTracking;

	var $trackingOptions = [
			'tns-dk' => [
					'name' => 'TNS Denmark',
					'fields' => [
						's' 	=> '',
						'cp' => '',
						'url' => ''
					],
					'scriptName' => 'spring',
					TnsTracking::PLUGIN_OPTION_ACTIVE_STRING => '',
					'trackingUrl' => 'tns-gallup.dk',
					'trackingFunction' => 'push'
			],
			'tns-no' => [
					'name' => 'TNS Norway',
					'fields' => [
						's' => '',
						'cp' => '',
						'url' => ''
					],
					'scriptName' => 'unispring',
					TnsTracking::PLUGIN_OPTION_ACTIVE_STRING => '',
					'trackingUrl' => 'mmk.tns-cs.net',
					'trackingFunction' => 'c'
			]
	];

	public function __construct(){
		$this->jsFolder = plugin_dir_url(__FILE__).'js/';
		$this->activeTracking = $this->getTnsOption(TnsTracking::PLUGIN_OPTION_ACTIVE_STRING);
	}

	public function initHooks(){
		add_action('admin_menu', function() {
			// Add a new submenu item under Settings:
			add_options_page('TNS settings', 'TNS settings', 'manage_options', 'tns_settings', array($this, 'loadAdminPage'));
		});
		if($this->activeTracking == 'tns-no'){
			// load the unispring script first
			add_action('wp_enqueue_scripts', function(){
				wp_enqueue_script( $this->activeTracking.TnsTracking::PLUGIN_OPTION_DIVIDER.'js' , $this->jsFolder.$this->trackingOptions[$this->activeTracking]['scriptName'].'.js', array(), '', 'all', true);
			});
			add_action('wp_footer', array($this, 'implementTrackingScript'),1200);
		}
		if($this->activeTracking == 'tns-dk'){

			add_action('wp_head', function(){
				echo '<script type="text/javascript"> (function() {
				var scr = document.createElement(\'script\');
				scr.type = \'text/javascript\'; scr.async = true;
				scr.src = \''.$this->jsFolder.$this->trackingOptions[$this->activeTracking]['scriptName'].'.js\';
				var s = document.getElementsByTagName(\'script\')[0];
				s.parentNode.insertBefore(scr, s);
				})();
			</script>';
			});
			add_action('wp_footer', array($this, 'implementTrackingScript'),1200);
		}
	}

	public function fetchTrackingOptions(){
		foreach ($this->trackingOptions as $trackingOption => $trackingOptionValue){
			$this->activeTracking = $this->getTnsOption(TnsTracking::PLUGIN_OPTION_ACTIVE_STRING);
			foreach($trackingOptionValue['fields'] as $field => $fieldValue){
				$trackingOptionValue['fields'][$field] = $this->getTnsOption($trackingOption . TnsTracking::PLUGIN_OPTION_DIVIDER .$field);
			}
			$this->trackingOptions[$trackingOption]['fields'] = $trackingOptionValue['fields'];
		
			$this->trackingOptions[$trackingOption][TnsTracking::PLUGIN_OPTION_ACTIVE_STRING] = ($this->activeTracking === $trackingOption)? true : false;

		}
	}

	public static function getTnsOption($option, $defaultValue = NULL) {
		$configValue = get_option(TnsTracking::PLUGIN_OPTION_NAMESPACE . TnsTracking::PLUGIN_OPTION_DIVIDER . $option, NULL );
		return (empty($configValue)) ? $defaultValue : $configValue;
	}

	public static function setTnsOption($option, $value) {
		return update_option($option, $value);
	}

	public function updateOptionsIfChanged(){
		foreach ($_POST as $key => $value) {
			if($key != 'submit'){
				TnsTracking::setTnsOption($key,$value);
			}
		}
	}

	public function loadAdminPage(){
		$this->updateOptionsIfChanged();
		$optionFields = '';
		$this->fetchTrackingOptions();

		foreach ($this->trackingOptions as $trackingOption => $trackingOptionValue) {
			$isChecked = ($this->activeTracking == $trackingOption)?'checked':'';
			$optionFields .= '<tr><th colspan="2">'.$trackingOptionValue['name'].'<input type="radio" name="'.TnsTracking::PLUGIN_OPTION_NAMESPACE . TnsTracking::PLUGIN_OPTION_DIVIDER . TnsTracking::PLUGIN_OPTION_ACTIVE_STRING .'" value="' . $trackingOption . '" '.$isChecked.'/></th></tr>';
			foreach($trackingOptionValue['fields'] as $field => $fieldValue){
				$fieldKey = TnsTracking::PLUGIN_OPTION_NAMESPACE . TnsTracking::PLUGIN_OPTION_DIVIDER . $trackingOption . TnsTracking::PLUGIN_OPTION_DIVIDER . $field;
				$optionFields .= '
				<tr>
					<th scope="row"><label for="'. $fieldKey .'">'.$field.'</label></th>
					<td><input name="'.$fieldKey.'" type="text" id="'.$fieldKey.'" value="'.$fieldValue.'" class="regular-text"></td>
				</tr>';
			}
		}

		echo '
		<div class="wrap">
			<form method="POST">
				<table class="form-table">
					<tbody>'. $optionFields .'</tbody>
				</table>
				<input type="submit" name="submit" id="submit" class="button button-primary" value="'.__('Gem ændringer').'">
			</form>
		</div>';

	}

	public function implementTrackingScript(){
		$this->fetchTrackingOptions();
		if($this->activeTracking && !is_admin()){

			$javascriptOutput = '<script type="text/javascript">';

			if($this->activeTracking == 'tns-dk'){
				$javascriptOutput .=
				'var '.$this->trackingOptions[$this->activeTracking]['scriptName'].'q = 
					'.$this->trackingOptions[$this->activeTracking]['scriptName'].'q || [];
					'.$this->trackingOptions[$this->activeTracking]['scriptName'].'q.'.$this->trackingOptions[$this->activeTracking]['trackingFunction'].'({
						"s":\''.$this->trackingOptions[$this->activeTracking]['fields']['s'].'\',
						"cp":\''.$this->trackingOptions[$this->activeTracking]['fields']['cp'].'\',
						"url": window.location.toString()
					});';
			}

			if ($this->activeTracking == 'tns-no'){
				$javascriptOutput .=
				'var sp_e0 = {
				 "s":\''.$this->trackingOptions[$this->activeTracking]['fields']['s'].'\',
				 "cp":\''.$this->trackingOptions[$this->activeTracking]['fields']['cp'].'\',
				 "url": window.location.toString()
				}
				'.$this->trackingOptions[$this->activeTracking]['scriptName'].'.'.$this->trackingOptions[$this->activeTracking]['trackingFunction'].'(sp_e0);';
			}


			$javascriptOutput .= '</script><noscript><img src="http://'.$this->trackingOptions[$this->activeTracking]['fields']['s'].'.'.$this->trackingOptions[$this->activeTracking]['trackingUrl'].'/j0=,,,;+,cp='.$this->trackingOptions[$this->activeTracking]['fields']['cp'].'+url='.$this->trackingOptions[$this->activeTracking]['fields']['url'].';;;" alt="tns-tracking"></noscript>';

			echo $javascriptOutput;
		}
	}
}

$tns = new TnsTracking();
$tns->initHooks();
?>