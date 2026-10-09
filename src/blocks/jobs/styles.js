/** @jsxRuntime classic */
/** @jsx createElement */
// The classic JSX runtime keeps the block working before WordPress 6.6, which
// added the react-jsx-runtime script the automatic runtime needs.
import { createElement } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { InspectorControls, PanelColorSettings } from '@wordpress/block-editor';
import { PanelBody, RangeControl } from '@wordpress/components';

import { data, hasDesign } from './controls';

/**
 * Styles tab of the block: listing colors, cards and the apply button. The
 * listing colors are [jobpress] attributes; the rest is CSS the block renders.
 *
 * @param {Object}   props
 * @param {Object}   props.attributes    Block attributes.
 * @param {Function} props.setAttributes Block attribute setter.
 */
export default function StylesPanels( { attributes, setAttributes } ) {
	// Colors are stored as '' when cleared, so the global setting applies again.
	const color = ( attribute, label ) => ( {
		label,
		value: attributes[ attribute ] || undefined,
		onChange: ( value ) => setAttributes( { [ attribute ]: value || '' } ),
	} );

	const range = ( attribute, label, max ) => (
		<RangeControl
			__nextHasNoMarginBottom
			__next40pxDefaultSize
			label={ label }
			value={ attributes[ attribute ] }
			min={ 0 }
			max={ max }
			allowReset
			onChange={ ( value ) => setAttributes( { [ attribute ]: value } ) }
		/>
	);

	return (
		<InspectorControls group="styles">
			<PanelColorSettings
				__experimentalIsRenderedInSidebar
				title={ __( 'Listing colors', 'jobpress' ) }
				enableAlpha={ false }
				colorSettings={ [
					color( 'brand_color', __( 'Brand', 'jobpress' ) ),
					color( 'hover_color', __( 'Hover', 'jobpress' ) ),
					color( 'heading_color', __( 'Headings', 'jobpress' ) ),
					color( 'secondary_color', __( 'Secondary text', 'jobpress' ) ),
					color( 'content_color', __( 'Content text', 'jobpress' ) ),
					color( 'border_color', __( 'Borders', 'jobpress' ) ),
				] }
			>
				<p className="components-base-control__help">
					{ __( 'Leave a color unset to use the JobPress appearance settings.', 'jobpress' ) }
				</p>
			</PanelColorSettings>

			<PanelBody className="jobpress-block-panel" title={ __( 'Job cards', 'jobpress' ) } initialOpen={ false }>
				{ range( 'cardGap', __( 'Space between cards (px)', 'jobpress' ), 100 ) }
				{ range( 'cardPadding', __( 'Padding (px)', 'jobpress' ), 100 ) }
				{ range( 'cardRadius', __( 'Border radius (px)', 'jobpress' ), 50 ) }
			</PanelBody>

			<PanelColorSettings
				__experimentalIsRenderedInSidebar
				title={ __( 'Card colors', 'jobpress' ) }
				initialOpen={ false }
				enableAlpha
				colorSettings={ [
					color( 'cardBackground', __( 'Card background', 'jobpress' ) ),
					...( hasDesign( attributes, data.designFields.applyButton )
						? [
								color( 'buttonColor', __( 'Button text', 'jobpress' ) ),
								color( 'buttonBackground', __( 'Button background', 'jobpress' ) ),
								color( 'buttonHoverColor', __( 'Button text on hover', 'jobpress' ) ),
								color( 'buttonHoverBackground', __( 'Button background on hover', 'jobpress' ) ),
						  ]
						: [] ),
				] }
			/>
		</InspectorControls>
	);
}
