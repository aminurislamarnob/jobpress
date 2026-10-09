/** @jsxRuntime classic */
/** @jsx createElement */
// The classic JSX runtime keeps the block working before WordPress 6.6, which
// added the react-jsx-runtime script the automatic runtime needs.
import { createElement } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { Disabled, PanelBody, SelectControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

const data = window.jobpressBlock;

export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps();

	const designOptions = [
		{
			/* translators: %s: name of the design selected in the JobPress settings */
			label: sprintf( __( 'Default (%s)', 'jobpress' ), data.designs[ data.globalDesign ] ),
			value: '',
		},
		...Object.entries( data.designs ).map( ( [ value, label ] ) => ( { value, label } ) ),
	];

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Layout', 'jobpress' ) }>
					<SelectControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Design', 'jobpress' ) }
						value={ attributes.design }
						options={ designOptions }
						onChange={ ( design ) => setAttributes( { design } ) }
					/>
				</PanelBody>
			</InspectorControls>
			<Disabled>
				<ServerSideRender block="jobpress/jobs" attributes={ attributes } />
			</Disabled>
		</div>
	);
}
