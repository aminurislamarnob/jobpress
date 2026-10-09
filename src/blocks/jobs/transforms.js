/**
 * Conversions between the block and the [jobpress] shortcode. The block's
 * attributes are the shortcode's, so both directions copy them.
 */
import { createBlock, getBlockType } from '@wordpress/blocks';
import { next } from '@wordpress/shortcode';

import metadata from './block.json';
import { data } from './controls';

/**
 * Attributes the block shares with the shortcode.
 *
 * @return {string[]}
 */
const getSharedAttributes = () => {
	const blockAttributes = getBlockType( metadata.name )?.attributes ?? {};
	return data.shortcodeAttributes.filter( ( name ) => name in blockAttributes );
};

/**
 * Block attributes from shortcode attributes.
 *
 * @param {Object} named Named shortcode attributes.
 * @return {Object}
 */
const fromShortcodeAttributes = ( named = {} ) =>
	Object.fromEntries(
		getSharedAttributes()
			.filter( ( name ) => undefined !== named[ name ] )
			.map( ( name ) => [ name, String( named[ name ] ) ] )
	);

/**
 * Quote a shortcode attribute value. Brackets would end the shortcode, so they
 * become entities, as do double quotes when the value has both kinds of quotes.
 *
 * @param {string} value
 * @return {string}
 */
const quote = ( value ) => {
	value = value.replace( /\[/g, '&#91;' ).replace( /\]/g, '&#93;' );
	if ( ! value.includes( '"' ) ) {
		return `"${ value }"`;
	}
	return value.includes( "'" ) ? `"${ value.replace( /"/g, '&quot;' ) }"` : `'${ value }'`;
};

/**
 * The shortcode for block attributes: only the attributes that are set.
 *
 * @param {Object} attributes Block attributes.
 * @return {string}
 */
const toShortcode = ( attributes ) => {
	const atts = getSharedAttributes()
		.filter( ( name ) => 'string' === typeof attributes[ name ] && '' !== attributes[ name ].trim() )
		.map( ( name ) => ` ${ name }=${ quote( attributes[ name ] ) }` );
	return `[jobpress${ atts.join( '' ) }]`;
};

const isJobPressShortcode = ( text = '' ) => /^\s*\[jobpress(?:\s[^\]]*)?\]\s*$/.test( text );

export default {
	from: [
		{
			// A Shortcode block holding only [jobpress …].
			type: 'block',
			blocks: [ 'core/shortcode' ],
			isMatch: ( { text } ) => isJobPressShortcode( text ),
			transform: ( { text } ) =>
				createBlock( metadata.name, fromShortcodeAttributes( next( 'jobpress', text )?.shortcode.attrs.named ) ),
		},
		{
			// [jobpress …] pasted into the editor.
			type: 'shortcode',
			tag: 'jobpress',
			transform: ( attributes, { shortcode } ) =>
				createBlock( metadata.name, fromShortcodeAttributes( shortcode.attrs.named ) ),
		},
	],
	to: [
		{
			type: 'block',
			blocks: [ 'core/shortcode' ],
			transform: ( attributes ) => createBlock( 'core/shortcode', { text: toShortcode( attributes ) } ),
		},
	],
};
