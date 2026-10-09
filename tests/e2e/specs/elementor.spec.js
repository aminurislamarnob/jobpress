/**
 * The JobPress Elementor widget, rendered on the frontend from saved Elementor
 * data (the editor UI itself is not driven). Needs Elementor to be active.
 */
const { test, expect, uid, expectNoPhpErrors } = require( '../fixtures' );

test.describe( 'Elementor widget', () => {
	let token, job;

	test.beforeAll( async ( { requestUtils } ) => {
		const plugins = await requestUtils.rest( { path: '/wp/v2/plugins', params: { search: 'elementor' } } );
		test.skip(
			! plugins.some( ( plugin ) => plugin.plugin.startsWith( 'elementor/' ) && plugin.status === 'active' ),
			'Elementor is not active'
		);
	} );

	test.beforeEach( async ( { jobPress } ) => {
		token = uid();
		job = await jobPress.createJob( { title: `Widget Job ${ token }` } );
	} );

	test( 'renders the title, subtitle and positions count', async ( { page, jobPress } ) => {
		const widgetPage = await jobPress.createElementorPage( `Widget page ${ token }`, [
			{ title: `Widget title ${ token }`, subtitle: `Widget subtitle ${ token }`, show_positions: 'yes' },
		] );
		await page.goto( widgetPage.link );

		const widget = page.locator( `.elementor-element-${ widgetPage.widgetIds[ 0 ] }` );
		await expect( widget.getByRole( 'heading', { name: `Widget title ${ token }` } ) ).toBeVisible();
		await expect( widget.getByText( `Widget subtitle ${ token }` ) ).toBeVisible();
		await expect( widget.getByText( /open positions/ ) ).toBeVisible();
		await expect( widget.locator( `a[href="${ job.link }"]` ).first() ).toBeVisible();

		// Elementor's generated stylesheet for this page is loaded, so style controls can apply.
		await expect( page.locator( `link[href*="post-${ widgetPage.id }.css"]` ) ).toHaveCount( 1 );
		await expectNoPhpErrors( page );
	} );

	test( 'can hide the positions count', async ( { page, jobPress } ) => {
		const widgetPage = await jobPress.createElementorPage( `Widget page ${ token }`, [
			{ show_positions: '' },
		] );
		await page.goto( widgetPage.link );

		await expect( page.locator( '.jp-listing' ) ).toHaveCount( 1 );
		await expect( page.getByText( /open positions/ ) ).toHaveCount( 0 );
	} );
} );
