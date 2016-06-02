<?php
	namespace OddSiteTransfer\OddCore\Admin\MetaData;
	
	use \OddSiteTransfer\OddCore\Admin\MetaData\MetaField;
	
	// \OddSiteTransfer\OddCore\Admin\MetaData\SelectMetaField
	class SelectMetaField extends MetaField {
		
		protected $_options = array();
		
		function __construct() {
			//echo("\OddCore\Admin\MetaData\SelectMetaField::__construct<br />");
			
			parent::__construct();
		}
		
		public function add_option($name, $value) {
			
			$this->_options[$value] = $name;
			
			return $this;
		}
		
		public function output($post) {
			//echo("\OddCore\Admin\MetaData\SelectMetaField::output<br />");
			
			$selected_value = $this->get_value($post);
			
			?>
				<select name="<?php echo($this->get_field_name()); ?>">
					<?php
						foreach($this->_options as $value => $name) {
							?>
								<option value="<?php echo($value); ?>" <?php if($selected_value === $value) {echo('selected');} ?>><?php echo($name); ?></option>
							<?php
						}
					?>
				</select>
			<?
		}
		
		public static function test_import() {
			echo("Imported \OddCore\Admin\MetaData\SelectMetaField<br />");
		}
	}
?>