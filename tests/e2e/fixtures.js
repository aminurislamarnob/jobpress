/**
 * Shared Playwright fixtures for JobPress.
 *
 * Extends the WordPress e2e fixtures (admin, editor, page, requestUtils) with a
 * `jobPress` helper that creates test data over REST, changes plugin settings
 * through the real settings screens, and undoes everything after each test, so
 * the suite can run against a site that already has real content.
 */
const { test: base, expect } = require( '@wordpress/e2e-test-utils-playwright' );

const SETTINGS_PAGES = {
	general: 'jobpress_settings',
	appearance: 'jobpress_appearance_settings',
	shortcode: 'jobpress_shortcode',
};

/**
 * A short random token, used to keep test data unique per run. Letters only:
 * WordPress rewrites patterns like "4x2433" to "4×2433" in rendered titles,
 * which made the title text differ from what the tests look for.
 */
const uid = () =>
	Array.from( { length: 6 }, () => String.fromCharCode( 97 + Math.floor( Math.random() * 26 ) ) ).join( '' );

/**
 * Markup of a JobPress Jobs block, serialized the way the block editor does.
 *
 * @param {Object} attributes Block attributes.
 * @return {string}
 */
const jobsBlock = ( attributes = {} ) => {
	const json = JSON.stringify( attributes )
		.replace( /--/g, '\\u002d\\u002d' )
		.replace( /</g, '\\u003c' )
		.replace( />/g, '\\u003e' )
		.replace( /&/g, '\\u0026' )
		.replace( /\\"/g, '\\u0022' );
	return Object.keys( attributes ).length
		? `<!-- wp:jobpress/jobs ${ json } /-->`
		: '<!-- wp:jobpress/jobs /-->';
};

class JobPressUtils {
	constructor( { page, requestUtils } ) {
		this.page = page;
		this.requestUtils = requestUtils;
		this.cleanups = [];
	}

	/**
	 * Create a published job over REST.
	 *
	 * @param {Object}   job
	 * @param {string}   job.title
	 * @param {string}   [job.content]
	 * @param {number[]} [job.categories] jobpress_category term IDs.
	 * @param {number[]} [job.types]      jobpress_type term IDs.
	 * @return {Promise<Object>} REST job object (has id, link, title).
	 */
	async createJob( { title, content = 'E2E job description.', categories = [], types = [] } ) {
		const job = await this.requestUtils.rest( {
			method: 'POST',
			path: '/wp/v2/jobpress',
			data: {
				title,
				content,
				status: 'publish',
				jobpress_category: categories,
				jobpress_type: types,
			},
		} );
		this.cleanups.push( () =>
			this.requestUtils.rest( {
				method: 'DELETE',
				path: `/wp/v2/jobpress/${ job.id }`,
				params: { force: true },
			} )
		);
		return job;
	}

	/**
	 * Create a job category or type term over REST.
	 *
	 * @param {'jobpress_category'|'jobpress_type'} taxonomy
	 * @param {string}                               name
	 * @return {Promise<Object>} REST term object (has id, slug, link).
	 */
	async createTerm( taxonomy, name ) {
		const term = await this.requestUtils.rest( {
			method: 'POST',
			path: `/wp/v2/${ taxonomy }`,
			data: { name },
		} );
		this.cleanups.push( () =>
			this.requestUtils.rest( {
				method: 'DELETE',
				path: `/wp/v2/${ taxonomy }/${ term.id }`,
				params: { force: true },
			} )
		);
		return term;
	}

	/**
	 * Create a published page over REST.
	 *
	 * @param {string} title
	 * @param {string} content
	 * @return {Promise<Object>} REST page object.
	 */
	async createPage( title, content ) {
		const page = await this.requestUtils.createPage( {
			title,
			content,
			status: 'publish',
		} );
		this.cleanups.push( () =>
			this.requestUtils.rest( {
				method: 'DELETE',
				path: `/wp/v2/pages/${ page.id }`,
				params: { force: true },
			} )
		);
		return page;
	}

	/**
	 * Create a published Elementor page holding JobPress widgets, over REST.
	 *
	 * Each widget is placed in its own container. Elementor builds the page's CSS
	 * file (including widget style controls) the first time the page is viewed.
	 *
	 * @param {string}   title
	 * @param {Object[]} widgets           Settings of each JobPress widget on the page.
	 * @param {Object}   containerSettings Settings of each container, e.g. { flex_direction: 'row' }.
	 * @return {Promise<Object>} REST page object.
	 */
	async createElementorPage( title, widgets = [ {} ], containerSettings = {} ) {
		// Elementor element IDs are 7-character hex strings.
		const elementId = () => Math.random().toString( 16 ).slice( 2, 9 ).padEnd( 7, '0' );
		const data = widgets.map( ( settings ) => ( {
			id: elementId(),
			elType: 'container',
			settings: containerSettings,
			elements: [
				{
					id: elementId(),
					elType: 'widget',
					widgetType: 'jobpress_jobs',
					settings,
					elements: [],
				},
			],
			isInner: false,
		} ) );

		const page = await this.requestUtils.rest( {
			method: 'POST',
			path: '/wp/v2/pages',
			data: {
				title,
				status: 'publish',
				meta: {
					_elementor_edit_mode: 'builder',
					_elementor_template_type: 'wp-page',
					_elementor_data: JSON.stringify( data ),
				},
			},
		} );
		page.widgetIds = data.map( ( container ) => container.elements[ 0 ].id );
		this.cleanups.push( () =>
			this.requestUtils.rest( {
				method: 'DELETE',
				path: `/wp/v2/pages/${ page.id }`,
				params: { force: true },
			} )
		);
		return page;
	}

	/**
	 * Create a published page holding JobPress Jobs blocks, over REST.
	 *
	 * @param {string}   title
	 * @param {Object[]} blocks Attributes of each block on the page.
	 * @return {Promise<Object>} REST page object.
	 */
	async createBlockPage( title, blocks = [ {} ] ) {
		return this.createPage( title, blocks.map( jobsBlock ).join( '\n\n' ) );
	}

	/**
	 * Delete a post (or page) after the test, e.g. one created in the editor.
	 *
	 * @param {number} id
	 * @param {string} [restBase] REST base of its post type.
	 */
	deleteAfterTest( id, restBase = 'pages' ) {
		this.cleanups.push( () =>
			this.requestUtils.rest( {
				method: 'DELETE',
				path: `/wp/v2/${ restBase }/${ id }`,
				params: { force: true },
			} )
		);
	}

	/**
	 * Save JobPress settings through the plugin's settings screen, restoring the
	 * previous values after the test.
	 *
	 * @param {'general'|'appearance'|'shortcode'} screen
	 * @param {Object<string,string>}              values Option name => value ('yes'/'no' for checkboxes).
	 */
	async updateSettings( screen, values ) {
		const previous = await this.saveSettings( screen, values );
		this.cleanups.push( () => this.saveSettings( screen, previous ) );
	}

	/**
	 * Fill and submit a settings screen.
	 *
	 * @return {Promise<Object<string,string>>} The values before saving.
	 */
	async saveSettings( screen, values ) {
		await this.visitSettings( screen );
		const previous = {};

		for ( const [ name, value ] of Object.entries( values ) ) {
			const field = this.page.locator( `[name="${ name }"]` );
			// Color fields are hidden behind the wpColorPicker UI, so set them directly.
			// Checkboxes take 'yes' (checked) or 'no' (unchecked).
			previous[ name ] = await field.evaluate( ( el, newValue ) => {
				if ( el.type === 'checkbox' ) {
					const wasChecked = el.checked;
					el.checked = newValue === 'yes';
					return wasChecked ? 'yes' : 'no';
				}
				const old = el.value;
				el.value = newValue;
				return old;
			}, value );
		}

		await this.page.locator( 'form[action="options.php"] [type="submit"]' ).click();
		await expect( this.page.locator( '#setting-error-settings_updated' ) ).toBeVisible();
		return previous;
	}

	async visitSettings( screen ) {
		await this.page.goto(
			`/wp-admin/edit.php?post_type=jobpress&page=${ SETTINGS_PAGES[ screen ] }`
		);
	}

	/** Read a field value from a settings screen. */
	async getSetting( screen, name ) {
		await this.visitSettings( screen );
		return this.page.locator( `[name="${ name }"]` ).inputValue();
	}

	/** URL of the Jobs Page configured in General Settings. */
	async getJobsPageUrl() {
		const pageId = await this.getSetting( 'general', 'jobpress_jobs_page_id' );
		expect( Number( pageId ), 'A Jobs Page must be configured' ).toBeGreaterThan( 0 );
		const jobsPage = await this.requestUtils.rest( { path: `/wp/v2/pages/${ pageId }` } );
		return jobsPage.link;
	}

	async cleanup() {
		// Undo in reverse order, e.g. delete jobs before their terms.
		for ( const undo of this.cleanups.reverse() ) {
			await undo();
		}
		this.cleanups = [];
	}
}

const test = base.extend( {
	jobPress: async ( { page, requestUtils }, use ) => {
		const jobPress = new JobPressUtils( { page, requestUtils } );
		await use( jobPress );
		await jobPress.cleanup();
	},
} );

/**
 * Assert the page rendered without PHP errors or notices leaking into the HTML.
 *
 * @param {import('@playwright/test').Page} page
 */
async function expectNoPhpErrors( page ) {
	const html = await page.content();
	expect( html ).not.toMatch( /<b>(Fatal error|Warning|Notice|Deprecated)<\/b>:/ );
}

module.exports = { test, expect, uid, expectNoPhpErrors, jobsBlock };
