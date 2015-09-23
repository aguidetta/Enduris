<?php
/*
Plugin Name: TablePress Extension: DataTables FixedHeader
Plugin URI: http://tablepress.org/extensions/datatables-fixedheader/
Description: Custom Extension for TablePress to add the DataTables FixedHeader functionality
Version: 1.2
Author: Tobias Bäthge
Author URI: http://tobias.baethge.com/
*/

/*
 * Shortcode:
 * [table id=123 datatables_fixedheader=top datatables_fixedheader_offsettop=60 /]
 */

/*
 * Register necessary Plugin Filters.
 */
add_filter( 'tablepress_shortcode_table_default_shortcode_atts', 'tablepress_add_shortcode_parameters_fixedheader' );
add_filter( 'tablepress_table_js_options', 'tablepress_add_fixedheader_js_options', 10, 3 );
add_filter( 'tablepress_datatables_command', 'tablepress_add_fixedheader_js_command', 10, 5 );

/**
 * Add "datatables_fixedheader" as a valid parameter to the [table /] Shortcode.
 *
 * @since 1.0
 *
 * @param array $default_atts Default attributes for the TablePress [table /] Shortcode.
 * @return array Extended attributes for the Shortcode.
 */
function tablepress_add_shortcode_parameters_fixedheader( $default_atts ) {
	$default_atts['datatables_fixedheader'] = '';
	$default_atts['datatables_fixedheader_offsettop'] = '';
	return $default_atts;
}

/**
 * Pass "datatables_fixedheader" from Shortcode parameters to JavaScript arguments.
 *
 * @since 1.0
 *
 * @param array  $js_options    Current JS options.
 * @param string $table_id      Table ID.
 * @param array $render_options Render Options.
 * @return array Modified JS options.
 */
function tablepress_add_fixedheader_js_options( $js_options, $table_id, $render_options ) {
	$js_options['datatables_fixedheader'] = $render_options['datatables_fixedheader'];
	$js_options['datatables_fixedheader_offsettop'] = $render_options['datatables_fixedheader_offsettop'];

	// Register the JS.
	if ( '' !== $js_options['datatables_fixedheader'] ) {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		$js_fixedheader_url = plugins_url( "fixedheader{$suffix}.js", __FILE__ );
		wp_enqueue_script( 'tablepress-fixedheader', $js_fixedheader_url, array( 'tablepress-datatables' ), '2.1.3-dev', true );

		// Horizontal Scrolling must be turned off for FixedHeader.
		$js_options['datatables_scrollx'] = false;
	}

	return $js_options;
}

/**
 * Evaluate "datatables_fixedheader" parameter and add corresponding JavaScript code, if needed.
 *
 * @since 1.0
 *
 * @param string $command    DataTables command.
 * @param string $html_id    HTML ID of the table.
 * @param array  $parameters DataTables parameters.
 * @param string $table_id   Table ID.
 * @param array  $js_options DataTables JS options.
 * @return string Modified DataTables command.
 */
function tablepress_add_fixedheader_js_command( $command, $html_id, $parameters, $table_id, $js_options ) {
	if ( empty( $js_options['datatables_fixedheader'] ) ) {
		return $command;
	}

	// Default values (no need to add a header to the parameter list, if the default value is set in the Shortcode).
	$default_headers = array(
		'top' => true,
		'bottom' => false,
		'left' => false,
		'right' => false,
	);
	$headers = array(
		'top' => false,
		'bottom' => false,
		'left' => false,
		'right' => false,
	);

	// Loop trough all headers that are set in the fixedheader parameter.
	$fixedheaders = explode( ',', $js_options['datatables_fixedheader'] );
	foreach ( $fixedheaders as $header ) {
		if ( isset( $headers[ $header ] ) ) {
			$headers[ $header ] = true;
		}
	}

	// Build parameter string.
	foreach ( $headers as $header => $header_used ) {
		if ( $header_used === $default_headers[ $header ] ) {
			unset( $headers[ $header ] );
		} else {
			$headers[ $header ] = '"' . $header . '": ' . ( $header_used ? 'true' : 'false' ) ;
		}
	}
	$parameter = implode( ', ', $headers );

	if ( ! empty( $js_options['datatables_fixedheader_offsettop'] ) ) {
		if ( '' !== $parameter ) {
			$parameter .= ', ';
		}
		$parameter .= '"offsetTop": ' . absint( $js_options['datatables_fixedheader_offsettop'] );
	}

	if ( '' !== $parameter ) {
		$parameter = ', {' . $parameter . '}';
	}

	$name = str_replace( '-', '_', "DT-{$html_id}" );
	$command = "var {$name} = {$command}\nnew $.fn.dataTable.FixedHeader( {$name}{$parameter} );";
	return $command;
}
