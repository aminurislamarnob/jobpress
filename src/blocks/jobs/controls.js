/** @jsxRuntime classic */
/** @jsx createElement */
// The classic JSX runtime keeps the block working before WordPress 6.6, which
// added the react-jsx-runtime script the automatic runtime needs.
import { createElement } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { SelectControl, TextControl } from '@wordpress/components';

/** Data passed from PHP: designs, global settings and per-design elements. */
export const data = window.jobpressBlock;

/**
 * The design a block renders: its own, or else the global design.
 *
 * @param {Object} attributes Block attributes.
 * @return {number} Design number.
 */
export const getDesign = ( attributes ) => Number( attributes.design || data.globalDesign );

/**
 * Whether the block renders one of the given designs.
 *
 * @param {Object}   attributes Block attributes.
 * @param {number[]} designs    Design numbers.
 * @return {boolean}
 */
export const hasDesign = ( attributes, designs ) => designs.includes( getDesign( attributes ) );

/**
 * Whether a Default/Show/Hide setting shows its element: "Show", or "Default"
 * when the global setting shows it.
 *
 * @param {Object} attributes Block attributes.
 * @param {string} attribute  Attribute name.
 * @return {boolean}
 */
export const isShown = ( attributes, attribute ) =>
	'yes' === attributes[ attribute ] ||
	( '' === attributes[ attribute ] && 'yes' === data.settings[ attribute ].value );

/**
 * A Default/Show/Hide select; "Default" follows the global setting, and says what it is.
 *
 * @param {Object}   props
 * @param {string}   props.attribute     Attribute name.
 * @param {string}   props.label         Control label.
 * @param {string}   [props.help]        Help text.
 * @param {Object}   props.attributes    Block attributes.
 * @param {Function} props.setAttributes Block attribute setter.
 */
export function VisibilityControl( { attribute, label, help, attributes, setAttributes } ) {
	const inherited = 'yes' === data.settings[ attribute ].value ? __( 'Show', 'jobpress' ) : __( 'Hide', 'jobpress' );

	return (
		<SelectControl
			__nextHasNoMarginBottom
			__next40pxDefaultSize
			label={ label }
			help={ help }
			value={ attributes[ attribute ] }
			options={ [
				/* translators: %s: Show or Hide, the global setting */
				{ value: '', label: sprintf( __( 'Default (%s)', 'jobpress' ), inherited ) },
				{ value: 'yes', label: __( 'Show', 'jobpress' ) },
				{ value: 'no', label: __( 'Hide', 'jobpress' ) },
			] }
			onChange={ ( value ) => setAttributes( { [ attribute ]: value } ) }
		/>
	);
}

/**
 * A text field whose empty value inherits the global setting, shown as placeholder.
 *
 * @param {Object}   props
 * @param {string}   props.attribute     Attribute name.
 * @param {string}   props.label         Control label.
 * @param {Object}   props.attributes    Block attributes.
 * @param {Function} props.setAttributes Block attribute setter.
 */
export function InheritedTextControl( { attribute, label, attributes, setAttributes } ) {
	return (
		<TextControl
			__nextHasNoMarginBottom
			__next40pxDefaultSize
			label={ label }
			help={ __( 'Leave empty to use the JobPress settings.', 'jobpress' ) }
			placeholder={ data.settings[ attribute ].value }
			value={ attributes[ attribute ] }
			onChange={ ( value ) => setAttributes( { [ attribute ]: value } ) }
		/>
	);
}
