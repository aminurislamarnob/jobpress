/** @jsxRuntime classic */
/** @jsx createElement */
// The classic JSX runtime keeps the block working before WordPress 6.6, which
// added the react-jsx-runtime script the automatic runtime needs.
import { createElement } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { FormTokenField, PanelBody, SelectControl, TextControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { decodeEntities } from '@wordpress/html-entities';

/**
 * Split a comma-separated attribute into a list.
 *
 * @param {string} value
 * @return {string[]}
 */
const parseList = ( value ) =>
	String( value || '' )
		.split( ',' )
		.map( ( item ) => item.trim() )
		.filter( Boolean );

/**
 * A token field over a comma-separated attribute: tokens show labels, the
 * attribute keeps the values (term slugs or job IDs).
 *
 * @param {Object}   props
 * @param {string}   props.label    Field label.
 * @param {string}   props.help     Help text.
 * @param {string}   props.value    Comma-separated values.
 * @param {Object[]} props.options  Choices: { value, label }.
 * @param {Function} props.onChange Receives the new comma-separated value.
 */
function ListTokenField( { label, help, value, options, onChange } ) {
	const toLabel = ( item ) => options.find( ( option ) => option.value === item )?.label ?? item;
	const toValue = ( token ) =>
		options.find( ( option ) => option.label === token || option.value === token )?.value;

	return (
		<div className="jobpress-block-token-field">
			<FormTokenField
				__nextHasNoMarginBottom
				__next40pxDefaultSize
				__experimentalExpandOnFocus
				label={ label }
				value={ parseList( value ).map( toLabel ) }
				suggestions={ options.map( ( option ) => option.label ) }
				__experimentalValidateInput={ ( token ) => undefined !== toValue( token ) }
				onChange={ ( tokens ) =>
					onChange(
						tokens
							.map( ( token ) => toValue( typeof token === 'string' ? token : token.value ) )
							.filter( Boolean )
							.join( ',' )
					)
				}
			/>
			<p className="jobpress-block-token-field__help">{ help }</p>
		</div>
	);
}

/**
 * Inspector panel: which jobs the block lists.
 *
 * @param {Object}   props
 * @param {Object}   props.attributes    Block attributes.
 * @param {Function} props.setAttributes Block attribute setter.
 */
export default function QueryPanel( { attributes, setAttributes } ) {
	const { categories, types, jobs } = useSelect( ( select ) => {
		const { getEntityRecords } = select( coreStore );
		const termQuery = { per_page: -1, hide_empty: false, _fields: 'id,name,slug' };
		return {
			categories: getEntityRecords( 'taxonomy', 'jobpress_category', termQuery ) ?? [],
			types: getEntityRecords( 'taxonomy', 'jobpress_type', termQuery ) ?? [],
			jobs:
				getEntityRecords( 'postType', 'jobpress', {
					per_page: 100,
					status: 'publish',
					orderby: 'title',
					order: 'asc',
					_fields: 'id,title',
				} ) ?? [],
		};
	}, [] );

	const termOptions = ( terms ) => terms.map( ( term ) => ( { value: term.slug, label: term.name } ) );
	const jobOptions = jobs.map( ( job ) => ( {
		value: String( job.id ),
		/* translators: 1: job title, 2: job ID */
		label: sprintf( __( '%1$s (#%2$d)', 'jobpress' ), decodeEntities( job.title.rendered || '' ), job.id ),
	} ) );

	const orderby = attributes.orderby || 'date';

	return (
		<PanelBody className="jobpress-block-panel" title={ __( 'Query', 'jobpress' ) } initialOpen={ false }>
			<TextControl
				__nextHasNoMarginBottom
				__next40pxDefaultSize
				type="number"
				min={ 1 }
				label={ __( 'Number of jobs', 'jobpress' ) }
				help={ __( 'Leave empty to show all jobs. Grouped designs show this many per category.', 'jobpress' ) }
				value={ attributes.per_page }
				onChange={ ( value ) => setAttributes( { per_page: value && Number( value ) > 0 ? String( parseInt( value, 10 ) ) : '' } ) }
			/>
			<ListTokenField
				label={ __( 'Categories', 'jobpress' ) }
				help={ __( 'Show jobs in any of these categories. Leave empty for all.', 'jobpress' ) }
				value={ attributes.category }
				options={ termOptions( categories ) }
				onChange={ ( category ) => setAttributes( { category } ) }
			/>
			<ListTokenField
				label={ __( 'Job types', 'jobpress' ) }
				help={ __( 'Show jobs of any of these types. Leave empty for all.', 'jobpress' ) }
				value={ attributes.type }
				options={ termOptions( types ) }
				onChange={ ( type ) => setAttributes( { type } ) }
			/>
			<ListTokenField
				label={ __( 'Only these jobs', 'jobpress' ) }
				help={ __( 'Leave empty to show every matching job.', 'jobpress' ) }
				value={ attributes.include }
				options={ jobOptions }
				onChange={ ( include ) => setAttributes( { include } ) }
			/>
			<ListTokenField
				label={ __( 'Leave out these jobs', 'jobpress' ) }
				help={ __( 'Jobs to hide from this list.', 'jobpress' ) }
				value={ attributes.exclude }
				options={ jobOptions }
				onChange={ ( exclude ) => setAttributes( { exclude } ) }
			/>
			<SelectControl
				__nextHasNoMarginBottom
				__next40pxDefaultSize
				label={ __( 'Order by', 'jobpress' ) }
				value={ orderby }
				options={ [
					{ value: 'date', label: __( 'Date', 'jobpress' ) },
					{ value: 'title', label: __( 'Title', 'jobpress' ) },
					{ value: 'menu_order', label: __( 'Menu order', 'jobpress' ) },
					{ value: 'rand', label: __( 'Random', 'jobpress' ) },
				] }
				// Date is the shortcode's default, so it is stored empty.
				onChange={ ( value ) => setAttributes( { orderby: 'date' === value ? '' : value } ) }
			/>
			{ 'rand' !== orderby && (
				<SelectControl
					__nextHasNoMarginBottom
					__next40pxDefaultSize
					label={ __( 'Order', 'jobpress' ) }
					value={ ( attributes.order || 'DESC' ).toUpperCase() }
					options={ [
						{ value: 'DESC', label: __( 'Descending', 'jobpress' ) },
						{ value: 'ASC', label: __( 'Ascending', 'jobpress' ) },
					] }
					onChange={ ( value ) => setAttributes( { order: 'DESC' === value ? '' : value } ) }
				/>
			) }
		</PanelBody>
	);
}
