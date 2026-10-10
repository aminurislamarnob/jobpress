/**
 * Page layout on the active theme (classic or block): JobPress pages use the
 * theme's header and footer, sit inside a padded container, and the single job
 * page shows the job title.
 *
 * Run it against both kinds of theme with E2E_THEME, e.g.
 *   E2E_THEME=twentytwentyone npm run test:e2e -- specs/layout.spec.js
 *   E2E_THEME=twentytwentyfive npm run test:e2e -- specs/layout.spec.js
 */
const { test, expect, uid, expectNoPhpErrors } = require( '../fixtures' );

test.describe( 'Page layout', () => {
	let siteTitle, job, category, jobsPageUrl;

	test.beforeEach( async ( { jobPress, requestUtils } ) => {
		siteTitle = ( await requestUtils.rest( { path: '/wp/v2/settings' } ) ).title;
		category = await jobPress.createTerm( 'jobpress_category', `Layout ${ uid() }` );
		job = await jobPress.createJob( {
			title: `Layout Test Job ${ uid() }`,
			categories: [ category.id ],
		} );
		jobsPageUrl = await jobPress.getJobsPageUrl();
	} );

	// Escape the title for use in a case-insensitive regular expression.
	const titleMatcher = () => new RegExp( siteTitle.replace( /[.*+?^${}()|[\]\\]/g, '\\$&' ), 'i' );

	for ( const [ name, getUrl ] of [
		[ 'jobs page', () => jobsPageUrl ],
		[ 'category archive', () => category.link ],
		[ 'single job page', () => job.link ],
	] ) {
		test( `${ name } has the theme header, footer and a padded container`, async ( { page } ) => {
			await page.goto( getUrl() );

			// The first <header>/<footer> in the page is the theme's own.
			await expect( page.locator( 'header' ).first() ).toContainText( titleMatcher() );
			await expect( page.locator( 'footer' ).first() ).toBeVisible();

			// Some classic themes (e.g. Twenty Twenty-One) open their own #primary in
			// header.php, so find our wrapper by class rather than by ID.
			const container = page.locator( '.jp-container' );
			await expect( container ).toHaveCount( 1 );
			// Measure <main>, which sits inside the container's side padding.
			const box = await container.locator( 'main' ).boundingBox();
			const viewport = page.viewportSize();
			expect( box.x, 'content must not touch the left edge' ).toBeGreaterThanOrEqual( 15 );
			expect( box.x + box.width, 'content must not touch the right edge' ).toBeLessThanOrEqual(
				viewport.width - 15
			);

			await expectNoPhpErrors( page );
		} );
	}

	test( 'block theme header and footer are laid out like on a normal page', async ( { page, jobPress } ) => {
		const plainPage = await jobPress.createPage( `Layout reference ${ uid() }`, '<p>Reference</p>' );

		// Position of the last link in the header/footer, e.g. a right-aligned menu. Block
		// layout styles are only printed when the blocks are rendered before wp_head().
		const lastLinkX = ( container ) =>
			page
				.locator( container )
				.first()
				.locator( 'a' )
				.last()
				.evaluate( ( el ) => Math.round( el.getBoundingClientRect().x ) );

		await page.goto( plainPage.link );
		// Block themes only: classic themes print their header and footer from PHP, so
		// there are no block layout styles to go missing.
		test.skip( ( await page.locator( '.wp-site-blocks' ).count() ) === 0, 'Block themes only' );

		const expected = { header: await lastLinkX( 'header' ), footer: await lastLinkX( 'footer' ) };

		for ( const url of [ jobsPageUrl, job.link ] ) {
			await page.goto( url );
			expect( await lastLinkX( 'header' ) ).toBe( expected.header );
			expect( await lastLinkX( 'footer' ) ).toBe( expected.footer );
		}
	} );

	test( 'single job page shows the job title as the main heading', async ( { page } ) => {
		await page.goto( job.link );

		await expect( page.getByRole( 'heading', { level: 1 } ) ).toHaveCount( 1 );
		await expect( page.getByRole( 'heading', { level: 1 } ) ).toHaveText( job.title.rendered );
	} );
} );
