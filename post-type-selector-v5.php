<?php

if( ! class_exists('acf_field_post_type_selector') ) :

class acf_field_post_type_selector extends acf_field {

    const SELECTOR_TYPE_SELECT = 0;
    const SELECTOR_TYPE_RADIO = 1;
    const SELECTOR_TYPE_CHECKBOXES = 2;

    /*
    *  __construct
    *
    *  This function will setup the field type data
    *
    *  @type    function
    *  @date    5/03/2014
    *  @since   5.0.0
    *
    *  @param   n/a
    *  @return  n/a
    */

    function __construct()
    {

        /*
        *  name (string) Single word, no spaces. Underscores allowed
        */

        $this->name = 'post_type_selector';


        /*
        *  label (string) Multiple words, can include spaces, visible when selecting a field type
        */

        $this->label = __('Post Type Selector', 'acf-post_type_selector');


        /*
        *  category (string) basic | content | choice | relational | jquery | layout | CUSTOM GROUP NAME
        */

        $this->category = 'relational';


        /*
        *  defaults (array) Array of default settings which are merged into the field object. These are used later in settings
        */

        $this->defaults = array(
            'post_type'   => array(),
            'select_type' => 'Checkboxes',
        );


        /*
        *  l10n (array) Array of strings that are used in JavaScript. This allows JS strings to be translated in PHP and loaded via:
        *  var message = acf._e('FIELD_NAME', 'error');
        */

        $this->l10n = array(
            'error' => __('Error! Please enter a higher value', 'acf-post_type_selector'),
        );


        // do not delete!
        parent::__construct();

        // settings
        $this->settings = array(
            'version' => '1.0.0'
        );

    }


    /*
    *  render_field_settings()
    *
    *  Create extra settings for your field. These are visible when editing a field
    *
    *  @type    action
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   $field (array) the $field being edited
    *  @return  n/a
    */

    function render_field_settings( $field ) {

        // defaults?
        $field = array_merge($this->defaults, $field);

        /*
        *  acf_render_field_setting
        *
        *  This function will create a setting for your field. Simply pass the $field parameter and an array of field settings.
        *  The array of settings does not require a `value` or `prefix`; These settings are found from the $field array.
        *
        *  More than one setting can be added by copy/paste the above code.
        *  Please note that you must also have a matching $defaults value for the field name (font_size)
        */

        // default_value
        acf_render_field_setting( $field, array(
            'label'         => __('Filter by Post Type','acf-post_type_selector'),
            'instructions'  => '',
            'type'          => 'select',
            'name'          => 'post_type',
            'choices'       => acf_get_pretty_post_types(),
            'multiple'      => 1,
            'ui'            => 1,
            'allow_null'    => 1,
            'placeholder'   => __('All post types','acf-post_type_selector'),
        ));

        // default_value
        acf_render_field_setting( $field, array(
            'label'         => __('Selector Type','acf-post_type_selector'),
            'instructions'  => __('How would you like to select the post type?','acf-post_type_selector'),
            'type'          => 'select',
            'name'          => 'select_type',
            'layout'        => 'horizontal',
            'choices'       => array(
                acf_field_post_type_selector::SELECTOR_TYPE_SELECT => __( 'Select' ),
                acf_field_post_type_selector::SELECTOR_TYPE_RADIO => __( 'Radio' ),
                acf_field_post_type_selector::SELECTOR_TYPE_CHECKBOXES => __( 'Checkboxes' ),
            )
        ));

    }
    
    /*
    *  acf_force_type_array
    *
    *  This function will force a variable to become an array
    *
    *  @type	function
    *  @date	4/02/2014
    *  @since	5.0.0
    *
    *  @param	$var (mixed)
    *  @return	(array)
    */
    function acf_force_type_array( $var ) {

        // is array?
        if ( is_array($var) ) {
        	return $var;
        }

        // bail early if empty
        if( empty($var) && !is_numeric($var) ) {
        	return array();
        }

        // string 
        if( is_string($var) ) {
        	return explode(',', $var);
        }

        // place in array
        return array( $var );
    }

    /*
    *  render_field()
    *
    *  Create the HTML interface for your field
    *
    *  @param   $field (array) the $field being rendered
    *
    *  @type    action
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   $field (array) the $field being edited
    *  @return  n/a
    */

    function render_field( $field ) {

        // defaults?
        $field = array_merge( $this->defaults, $field );

        $post_types = get_post_types( array(
            'public' => true,
        ), 'objects' );
        $post_types_filter = !empty($field['post_type']) ? $this->acf_force_type_array( $field['post_type'] ) : acf_get_post_types();

        // filter post types
        $post_types = array_filter($post_types, function($post_type) use ($post_types_filter) {
            return in_array($post_type->name, $post_types_filter);
        });

        // create Field HTML
        $checked = array();

        switch ( $field[ 'select_type' ] ) {

            case acf_field_post_type_selector::SELECTOR_TYPE_SELECT:

                echo '<select id="' . $field[ 'name' ] . '" class="' . $field[ 'class' ] . '" name="' . $field[ 'name' ] . '">';

                $checked[ $field[ 'value' ] ] = 'selected="selected"';

                foreach( $post_types as $post_type ) {
                    $is_option_checked = isset($checked[ $post_type->name ]) ? $checked[ $post_type->name ] : '';

                    echo '<option ' . $is_option_checked . ' value="' . $post_type->name . '">' . $post_type->labels->name . '</option>';

                }

                echo '</select>';

            break;

            case acf_field_post_type_selector::SELECTOR_TYPE_RADIO:

                echo '<ul class="radio_list radio horizontal">';

                $checked[ $field[ 'value' ] ] = 'checked="checked"';

                foreach( $post_types as $post_type ) {

                ?>

                    <li><input type="radio" <?php echo ( isset( $checked[ $post_type->name ] ) ) ? $checked[ $post_type->name] : null; ?> class="<?php echo $field[ 'class' ]; ?>" name="<?php echo $field[ 'name' ]; ?>" value="<?php echo $post_type->name; ?>"><label><?php echo $post_type->labels->name; ?></label></li>

                <?php

                }

                echo '</ul>';


            break;

            case acf_field_post_type_selector::SELECTOR_TYPE_CHECKBOXES:

                echo '<ul class="checkbox_list checkbox">';

                if ( ! empty( $field[ 'value'] ) ) {

                    foreach(  $field[ 'value' ] as $val ) {

                        $checked[ $val ] = 'checked="checked"';

                    }

                }

                foreach( $post_types as $post_type ) {

                ?>

                    <li><input type="checkbox" <?php echo ( isset( $checked[ $post_type->name ] ) ) ? $checked[ $post_type->name] : null; ?> class="<?php echo $field[ 'class' ]; ?>" name="<?php echo $field[ 'name' ]; ?>[]" value="<?php echo $post_type->name; ?>"><label><?php echo $post_type->labels->name; ?></label></li>
                <?php

                }

                echo '</ul>';

            break;

        }
    }


    /*
    *  input_admin_enqueue_scripts()
    *
    *  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
    *  Use this action to add CSS + JavaScript to assist your render_field() action.
    *
    *  @type    action (admin_enqueue_scripts)
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   n/a
    *  @return  n/a
    */



    function input_admin_enqueue_scripts() {

        $dir = plugin_dir_url( __FILE__ );


        // register & include JS
        wp_register_script( 'acf-input-post_type_selector', "{$dir}js/input.js", array('acf-input'), $this->settings['version'] );
        wp_enqueue_script('acf-input-post_type_selector');


        // register & include CSS
        wp_register_style( 'acf-input-post_type_selector', "{$dir}css/input.css", array('acf-input'), $this->settings['version'] );
        wp_enqueue_style('acf-input-post_type_selector');


    }




    /*
    *  input_admin_head()
    *
    *  This action is called in the admin_head action on the edit screen where your field is created.
    *  Use this action to add CSS and JavaScript to assist your render_field() action.
    *
    *  @type    action (admin_head)
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   n/a
    *  @return  n/a
    */

    /*

    function input_admin_head() {



    }

    */


    /*
    *  input_form_data()
    *
    *  This function is called once on the 'input' page between the head and footer
    *  There are 2 situations where ACF did not load during the 'acf/input_admin_enqueue_scripts' and
    *  'acf/input_admin_head' actions because ACF did not know it was going to be used. These situations are
    *  seen on comments / user edit forms on the front end. This function will always be called, and includes
    *  $args that related to the current screen such as $args['post_id']
    *
    *  @type    function
    *  @date    6/03/2014
    *  @since   5.0.0
    *
    *  @param   $args (array)
    *  @return  n/a
    */

    /*

    function input_form_data( $args ) {



    }

    */


    /*
    *  input_admin_footer()
    *
    *  This action is called in the admin_footer action on the edit screen where your field is created.
    *  Use this action to add CSS and JavaScript to assist your render_field() action.
    *
    *  @type    action (admin_footer)
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   n/a
    *  @return  n/a
    */

    /*

    function input_admin_footer() {



    }

    */


    /*
    *  field_group_admin_enqueue_scripts()
    *
    *  This action is called in the admin_enqueue_scripts action on the edit screen where your field is edited.
    *  Use this action to add CSS + JavaScript to assist your render_field_options() action.
    *
    *  @type    action (admin_enqueue_scripts)
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   n/a
    *  @return  n/a
    */

    /*

    function field_group_admin_enqueue_scripts() {

    }

    */


    /*
    *  field_group_admin_head()
    *
    *  This action is called in the admin_head action on the edit screen where your field is edited.
    *  Use this action to add CSS and JavaScript to assist your render_field_options() action.
    *
    *  @type    action (admin_head)
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   n/a
    *  @return  n/a
    */

    /*

    function field_group_admin_head() {

    }

    */


    /*
    *  load_value()
    *
    *  This filter is applied to the $value after it is loaded from the db
    *
    *  @type    filter
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   $value (mixed) the value found in the database
    *  @param   $post_id (mixed) the $post_id from which the value was loaded
    *  @param   $field (array) the field array holding all the field options
    *  @return  $value
    */

    /*

    function load_value( $value, $post_id, $field ) {

        return $value;

    }

    */


    /*
    *  update_value()
    *
    *  This filter is applied to the $value before it is saved in the db
    *
    *  @type    filter
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   $value (mixed) the value found in the database
    *  @param   $post_id (mixed) the $post_id from which the value was loaded
    *  @param   $field (array) the field array holding all the field options
    *  @return  $value
    */

    /*

    function update_value( $value, $post_id, $field ) {

        return $value;

    }

    */


    /*
    *  format_value()
    *
    *  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
    *
    *  @type    filter
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   $value (mixed) the value which was loaded from the database
    *  @param   $post_id (mixed) the $post_id from which the value was loaded
    *  @param   $field (array) the field array holding all the field options
    *
    *  @return  $value (mixed) the modified value
    */

    /*

    function format_value( $value, $post_id, $field ) {

        // bail early if no value
        if( empty($value) ) {

            return $value;

        }


        // apply setting
        if( $field['font_size'] > 12 ) {

            // format the value
            // $value = 'something';

        }


        // return
        return $value;
    }

    */


    /*
    *  validate_value()
    *
    *  This filter is used to perform validation on the value prior to saving.
    *  All values are validated regardless of the field's required setting. This allows you to validate and return
    *  messages to the user if the value is not correct
    *
    *  @type    filter
    *  @date    11/02/2014
    *  @since   5.0.0
    *
    *  @param   $valid (boolean) validation status based on the value and the field's required setting
    *  @param   $value (mixed) the $_POST value
    *  @param   $field (array) the field array holding all the field options
    *  @param   $input (string) the corresponding input name for $_POST value
    *  @return  $valid
    */

    /*

    function validate_value( $valid, $value, $field, $input ){

        // Basic usage
        if( $value < $field['custom_minimum_setting'] )
        {
            $valid = false;
        }


        // Advanced usage
        if( $value < $field['custom_minimum_setting'] )
        {
            $valid = __('The value is too little!','acf-FIELD_NAME'),
        }


        // return
        return $valid;

    }

    */


    /*
    *  delete_value()
    *
    *  This action is fired after a value has been deleted from the db.
    *  Please note that saving a blank value is treated as an update, not a delete
    *
    *  @type    action
    *  @date    6/03/2014
    *  @since   5.0.0
    *
    *  @param   $post_id (mixed) the $post_id from which the value was deleted
    *  @param   $key (string) the $meta_key which the value was deleted
    *  @return  n/a
    */

    /*

    function delete_value( $post_id, $key ) {



    }

    */


    /*
    *  load_field()
    *
    *  This filter is applied to the $field after it is loaded from the database
    *
    *  @type    filter
    *  @date    23/01/2013
    *  @since   3.6.0
    *
    *  @param   $field (array) the field array holding all the field options
    *  @return  $field
    */

    /*

    function load_field( $field ) {

        return $field;

    }

    */


    /*
    *  update_field()
    *
    *  This filter is applied to the $field before it is saved to the database
    *
    *  @type    filter
    *  @date    23/01/2013
    *  @since   3.6.0
    *
    *  @param   $field (array) the field array holding all the field options
    *  @return  $field
    */

    /*

    function update_field( $field ) {

        return $field;

    }

    */


    /*
    *  delete_field()
    *
    *  This action is fired after a field is deleted from the database
    *
    *  @type    action
    *  @date    11/02/2014
    *  @since   5.0.0
    *
    *  @param   $field (array) the field array holding all the field options
    *  @return  n/a
    */

    /*

    function delete_field( $field ) {



    }

    */


}


// create field
new acf_field_post_type_selector();

endif;

?>