/**
 * JobPress Jobs block: an editor front end for the [jobpress] listing engine.
 * The block is dynamic; PHP renders it (see inc/Base/Blocks.php).
 */
import { registerBlockType } from '@wordpress/blocks';

import metadata from './block.json';
import Edit from './edit';
import transforms from './transforms';
import './editor.css';

registerBlockType( metadata.name, {
	edit: Edit,
	transforms,
	save: () => null,
} );
