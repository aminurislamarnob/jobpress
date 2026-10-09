/**
 * Theme copies of JobPress templates: outdated copies are reported to admins,
 * and outdated listing templates (which run their own query) still honour the
 * listing's query attributes.
 *
 * Uses the test child theme in tests/e2e/themes/jobpress-e2e-outdated (installed
 * by wp-env; link it into wp-content/themes to run this against another site).
 */
const { test, expect, uid } = require( '../fixtures' );

const THEME = 'jobpress-e2e-outdated';

test.describe( 'Theme template overrides', () => {
	let previousTheme;

	test.beforeEach( async ( { requestUtils } ) => {
		const themes = await requestUtils.rest( { path: '/wp/v2/themes' } );
		test.skip( ! themes.some( ( theme ) => theme.stylesheet === THEME ), `The ${ THEME } test theme is not installed` );
		previousTheme = themes.find( ( theme ) => theme.status === 'active' ).stylesheet;
		await requestUtils.activateTheme( THEME );
	} );

	test.afterEach( async ( { requestUtils } ) => {
		if ( previousTheme ) {
			await requestUtils.activateTheme( previousTheme );
		}
		// Show the notice again next time.
		await requestUtils.rest( {
			method: 'POST',
			path: '/wp/v2/users/me',
			data: { meta: { jobpress_dismissed_template_notice: '' } },
		} );
	} );

	test( 'admins are told about outdated copies until they dismiss the notice', async ( { page } ) => {
		await page.goto( '/wp-admin/index.php' );

		const notice = page.locator( '.jobpress-template-notice' );
		await expect( notice ).toBeVisible();
		await expect( notice ).toContainText( `${ THEME }/jobpress/listing/jobpress-listing-v1.php` );
		await expect( notice ).toContainText( 'version unknown' );
		// The up-to-date copy isn't listed.
		await expect( notice ).not.toContainText( 'jobpress-listing-v3.php' );

		await notice.getByRole( 'link', { name: 'Dismiss this notice' } ).click();
		await expect( page ).toHaveURL( /index\.php$/ );
		await expect( page.locator( '.jobpress-template-notice' ) ).toHaveCount( 0 );
		await page.reload();
		await expect( page.locator( '.jobpress-template-notice' ) ).toHaveCount( 0 );
	} );

	test( 'an outdated listing template shows the listing\'s jobs', async ( { page, jobPress } ) => {
		const token = uid();
		const sales = await jobPress.createTerm( 'jobpress_category', `Sales ${ token }` );
		await jobPress.createJob( { title: `Sales Lead ${ token }`, categories: [ sales.id ] } );
		await jobPress.createJob( { title: `Sales Rep ${ token }`, categories: [ sales.id ] } );
		await jobPress.createJob( { title: `Cook ${ token }` } );
		const listingPage = await jobPress.createPage(
			`Legacy ${ token }`,
			`[jobpress design="1" category="${ sales.slug }" orderby="title" order="ASC" per_page="1"]`
		);
		await page.goto( listingPage.link );

		// The theme copy (no jp-listing__ hook classes) renders inside the listing wrapper.
		await expect( page.locator( '.jp-listing .jp-single-job-list' ) ).toHaveCount( 1 );
		await expect( page.locator( '.jp-listing__card' ) ).toHaveCount( 0 );
		await expect( page.locator( '.jp-listing' ) ).toContainText( `Sales Lead ${ token }` );
	} );
} );
