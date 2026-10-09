/** @jsxRuntime classic */
/** @jsx createElement */
// The classic JSX runtime keeps the block working before WordPress 6.6, which
// added the react-jsx-runtime script the automatic runtime needs.
import { createElement } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { Disabled, ExternalLink, PanelBody, SelectControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

import QueryPanel from './query';
import { data, hasDesign, isShown, InheritedTextControl, VisibilityControl } from './controls';

export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps();
	const props = { attributes, setAttributes };

	const designOptions = [
		{
			/* translators: %s: name of the design selected in the JobPress settings */
			label: sprintf( __( 'Default (%s)', 'jobpress' ), data.designs[ data.globalDesign ] ),
			value: '',
		},
		...Object.entries( data.designs ).map( ( [ value, label ] ) => ( { value, label } ) ),
	];

	const cardFields = {
		category: __( 'Category', 'jobpress' ),
		type: __( 'Job type', 'jobpress' ),
		location: __( 'Location', 'jobpress' ),
		experience: __( 'Experience', 'jobpress' ),
		vacancy: __( 'Vacancies', 'jobpress' ),
		deadline: __( 'Deadline', 'jobpress' ),
	};

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody className="jobpress-block-panel" title={ __( 'Layout', 'jobpress' ) }>
					<p>
						{ __( 'Settings left on Default use the global Listing Defaults.', 'jobpress' ) }{ ' ' }
						<ExternalLink href={ data.settingsUrl }>{ __( 'Listing Defaults', 'jobpress' ) }</ExternalLink>
					</p>
					<SelectControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( 'Design', 'jobpress' ) }
						value={ attributes.design }
						options={ designOptions }
						onChange={ ( design ) => setAttributes( { design } ) }
					/>
				</PanelBody>

				<PanelBody className="jobpress-block-panel" title={ __( 'Header', 'jobpress' ) } initialOpen={ false }>
					<VisibilityControl { ...props } attribute="show_title" label={ __( 'Title', 'jobpress' ) } />
					{ isShown( attributes, 'show_title' ) && (
						<InheritedTextControl { ...props } attribute="title" label={ __( 'Title text', 'jobpress' ) } />
					) }
					<VisibilityControl { ...props } attribute="show_subtitle" label={ __( 'Subtitle', 'jobpress' ) } />
					{ isShown( attributes, 'show_subtitle' ) && (
						<InheritedTextControl { ...props } attribute="subtitle" label={ __( 'Subtitle text', 'jobpress' ) } />
					) }
					{ hasDesign( attributes, data.designFields.positions ) && (
						<VisibilityControl { ...props } attribute="show_positions" label={ __( 'Open positions count', 'jobpress' ) } />
					) }
				</PanelBody>

				<PanelBody className="jobpress-block-panel" title={ __( 'Job Card', 'jobpress' ) } initialOpen={ false }>
					{ Object.entries( cardFields ).map(
						( [ field, label ] ) =>
							hasDesign( attributes, data.designFields[ field ] ) && (
								<VisibilityControl key={ field } { ...props } attribute={ `show_${ field }` } label={ label } />
							)
					) }
					{ hasDesign( attributes, data.designFields.button ) && (
						<InheritedTextControl { ...props } attribute="button_text" label={ __( 'Button text', 'jobpress' ) } />
					) }
				</PanelBody>

				<PanelBody className="jobpress-block-panel" title={ __( 'Search & Links', 'jobpress' ) } initialOpen={ false }>
					<VisibilityControl
						{ ...props }
						attribute="show_search"
						label={ __( 'Search bar', 'jobpress' ) }
						help={ __( 'Searches open the Jobs Page results.', 'jobpress' ) }
					/>
					<VisibilityControl { ...props } attribute="show_view_all" label={ __( '"View all jobs" link', 'jobpress' ) } />
					{ isShown( attributes, 'show_view_all' ) && (
						<InheritedTextControl { ...props } attribute="view_all_text" label={ __( 'Link text', 'jobpress' ) } />
					) }
				</PanelBody>

				<QueryPanel { ...props } />
			</InspectorControls>
			<Disabled>
				<ServerSideRender block="jobpress/jobs" attributes={ attributes } />
			</Disabled>
		</div>
	);
}
