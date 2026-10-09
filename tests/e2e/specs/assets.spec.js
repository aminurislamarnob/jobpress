/**
 * Public stylesheets only load where JobPress output is shown, and the
 * appearance colors are applied (and sanitized) as CSS variables.
 */
const { test, expect, uid } = require( '../fixtures' );

const designStylesheet = ( page ) => page.locator( 'link#jobpress-css-css' );
const commonStylesheet = ( page ) => page.locator( 'link#jobpress-common-css' );
const brandColorVariable = ( page ) =>
	page.evaluate( () =>
		getComputedStyle( document.documentElement ).getPropertyValue( '--jp-brand-color' ).trim()
	);

test.describe( 'Public assets', () => {
	test( 'are not loaded on unrelated pages', async ( { page, jobPress } ) => {
		const plainPage = await jobPress.createPage( `Plain page ${ uid() }`, '<p>Nothing to see.</p>' );
		await page.goto( plainPage.link );

		await expect( designStylesheet( page ) ).toHaveCount( 0 );
		await expect( commonStylesheet( page ) ).toHaveCount( 0 );
	} );

	test( 'load the archive stylesheet on the Jobs Page', async ( { page, jobPress } ) => {
		await page.goto( await jobPress.getJobsPageUrl() );

		await expect( designStylesheet( page ) ).toHaveAttribute( 'href', /jobpress-style-v3\.css/ );
		await expect( commonStylesheet( page ) ).toHaveCount( 1 );
	} );

	test( 'load on a single job page', async ( { page, jobPress } ) => {
		const job = await jobPress.createJob( { title: `Styled Job ${ uid() }` } );
		await page.goto( job.link );

		await expect( designStylesheet( page ) ).toHaveCount( 1 );
		await expect( commonStylesheet( page ) ).toHaveCount( 1 );
	} );
} );

test.describe( 'Appearance colors', () => {
	test( 'apply a valid brand color', async ( { page, jobPress } ) => {
		await jobPress.updateSettings( 'appearance', { jobpress_brand_color: '#123456' } );
		await page.goto( await jobPress.getJobsPageUrl() );

		expect( await brandColorVariable( page ) ).toBe( '#123456' );
	} );

	test( 'reject a value that is not a hex color', async ( { page, jobPress } ) => {
		await jobPress.updateSettings( 'appearance', {
			jobpress_brand_color: 'red;}body{display:none',
		} );

		// The invalid value is discarded; the field shows the default color again.
		expect( await jobPress.getSetting( 'appearance', 'jobpress_brand_color' ) ).not.toContain( 'display' );

		await page.goto( await jobPress.getJobsPageUrl() );
		// Falls back to the default brand color and the injected rule is not applied.
		expect( await brandColorVariable( page ) ).toBe( '#0086fe' );
		await expect( page.locator( 'body' ) ).toBeVisible();
	} );
} );
