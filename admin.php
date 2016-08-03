<?php
class TnsAdmin {

	plugin_option_namespace = 'wp-tns';

	public function _construct(){
		add_action('admin_menu', array($this, 'addTnsAdminMenu'));
	}

	public function addTnsAdminMenu(){
		add_options_page('TNS settings', 'TNS settings', 'manage_options', 'tns_settings', array($this, 'tnsAdminPage'));
	}

	public static function getTnsOption($option, $defaultValue = NULL) {
    	$configValue = get_option($this::plugin_option_namespace . $option, NULL );
    	return (empty($configValue)) ? $defaultValue : $configValue;
	}

	public static function setTnsOption($option, $value) {
	    return update_option($this->plugin_option_namespace . $option, $value);
	}

	public function updateOptionsIfChanged(){
		foreach ($_POST as $key => $value) {
			if($key != 'submit'){
				TnsAdmin::setTnsOption($key,$value);
			}
		}
	}

	function tnsAdminPage(){

		$options = array(
			's' => array(
				'name' => 'S',
				'value' => TnsAdmin::getTnsOption('s', false),
			),
			'cp' => array(
				'name' => 'CP',
				'value' => TnsAdmin::getTnsOption('cp', false)
			)
		);

		$optionFields = '';

		foreach ($options as $option) {
			$optionFields .= <<<HTML
			<tr>
				<th scope="row"><label for="tns-$option['name']">$option['name']</label></th>
				<td><input name="tns-$option['name']" type="text" id="tns-$option['name']" value="$option['value']" class="regular-text"></td>
			</tr>
HTML;
		}

		$this->updateOptionsIfChanged();

		echo <<<HTML
		<table>
			<tbody>
				$optionFields
			</tbody>
		</table>
HTML;
	}
}

new TnsAdmin();
?>