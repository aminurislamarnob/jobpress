/**
 * Global listing settings (Settings > Shortcodes > Listing Defaults) apply to
 * every listing, and each listing can override them with attributes.
 */
const { test, expect, uid, expectNoPhpErrors } = require( '../fixtures' );

test.describe( 'Listing defaults', () => {
	let token;

	const visit = async ( page, jobPress, shortcode ) => {
		const listingPage = await jobPress.createPage( `Defaults ${ uid() }`, shortcode );
		await page.goto( listingPage.link );
		await expectNoPhpErrors( page );
	};

	test.beforeEach( async ( { jobPress } ) => {
		token = uid();
		await jobPress.createJob( { title: `Defaults Job ${ token }` } );
		await jobPress.updateSettings( 'shortcode', { jobpress_design_type: '1' } );
	} );

	test( 'use the built-in header by default', async ( { page, jobPress } ) => {
		await visit( page, jobPress, '[jobpress]' );

		await expect( page.locator( '.jp-listing__title' ) ).toHaveText( 'Job openings' );
		await expect( page.locator( '.jp-listing__subtitle' ) ).toHaveText(
			'Find the right job for you no matter what it is that you do.'
		);
		await expect( page.locator( '.jp-listing__count' ) ).toBeVisible();
	} );

	test( 'set the title and subtitle of every listing', async ( { page, jobPress } ) => {
		await jobPress.updateSettings( 'shortcode', {
			jobpress_listing_title: `Global title ${ token }`,
			jobpress_listing_subtitle: `Global subtitle ${ token }`,
		} );

		await visit( page, jobPress, '[jobpress][jobpress title="Own title" subtitle=""]' );

		const [ first, second ] = [ page.locator( '.jp-listing' ).nth( 0 ), page.locator( '.jp-listing' ).nth( 1 ) ];
		await expect( first.locator( '.jp-listing__title' ) ).toHaveText( `Global title ${ token }` );
		await expect( first.locator( '.jp-listing__subtitle' ) ).toHaveText( `Global subtitle ${ token }` );
		// A listing's own attribute wins; an empty one inherits the global value.
		await expect( second.locator( '.jp-listing__title' ) ).toHaveText( 'Own title' );
		await expect( second.locator( '.jp-listing__subtitle' ) ).toHaveText( `Global subtitle ${ token }` );
	} );

	test( 'can hide the header parts of every listing', async ( { page, jobPress } ) => {
		await jobPress.updateSettings( 'shortcode', {
			jobpress_listing_show_title: 'no',
			jobpress_listing_show_subtitle: 'no',
			jobpress_listing_show_positions: 'no',
		} );

		await visit( page, jobPress, '[jobpress title="Hidden anyway"][jobpress show_title="yes" show_positions="yes"]' );

		const [ first, second ] = [ page.locator( '.jp-listing' ).nth( 0 ), page.locator( '.jp-listing' ).nth( 1 ) ];
		await expect( first.locator( '.jp-listing__title' ) ).toHaveCount( 0 );
		await expect( first.locator( '.jp-listing__subtitle' ) ).toHaveCount( 0 );
		await expect( first.locator( '.jp-listing__count' ) ).toHaveCount( 0 );
		// A listing can show them again.
		await expect( second.locator( '.jp-listing__title' ) ).toHaveText( 'Job openings' );
		await expect( second.locator( '.jp-listing__subtitle' ) ).toHaveCount( 0 );
		await expect( second.locator( '.jp-listing__count' ) ).toBeVisible();
	} );

	test( 'can hide the title, subtitle and count per listing', async ( { page, jobPress } ) => {
		await visit( page, jobPress, '[jobpress show_title="no" show_positions="no"]' );

		await expect( page.locator( '.jp-listing__title' ) ).toHaveCount( 0 );
		await expect( page.locator( '.jp-listing__subtitle' ) ).toBeVisible();
		await expect( page.locator( '.jp-listing__count' ) ).toHaveCount( 0 );
	} );

	test( 'keep the settings screen values after saving', async ( { page, jobPress } ) => {
		await jobPress.updateSettings( 'shortcode', {
			jobpress_listing_title: `Saved ${ token }`,
			jobpress_listing_show_subtitle: 'no',
		} );
		await jobPress.visitSettings( 'shortcode' );

		await expect( page.locator( '[name="jobpress_listing_title"]' ) ).toHaveValue( `Saved ${ token }` );
		await expect( page.locator( '[name="jobpress_listing_show_subtitle"]' ) ).not.toBeChecked();
		await expect( page.locator( '[name="jobpress_listing_show_title"]' ) ).toBeChecked();
	} );
} );
