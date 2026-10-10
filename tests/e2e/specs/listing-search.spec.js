/**
 * The listing search bar launches a Jobs Page search, and global defaults for
 * the search bar and the "View all jobs" link.
 */
const { test, expect, uid, expectNoPhpErrors } = require( '../fixtures' );

test.describe( 'Listing search bar', () => {
	let token, category, job;

	const visit = async ( page, jobPress, shortcode ) => {
		const listingPage = await jobPress.createPage( `Search ${ uid() }`, shortcode );
		await page.goto( listingPage.link );
		await expectNoPhpErrors( page );
	};

	test.beforeEach( async ( { jobPress } ) => {
		token = uid();
		category = await jobPress.createTerm( 'jobpress_category', `Finance ${ token }` );
		job = await jobPress.createJob( { title: `Accountant ${ token }`, categories: [ category.id ] } );
		await jobPress.createJob( { title: `Gardener ${ token }` } );
	} );

	test( 'is off by default', async ( { page, jobPress } ) => {
		await visit( page, jobPress, '[jobpress]' );
		await expect( page.locator( '.jp-listing .jobpress-search-form-wrapper' ) ).toHaveCount( 0 );
	} );

	test( 'opens the Jobs Page results', async ( { page, jobPress } ) => {
		const jobsPageUrl = await jobPress.getJobsPageUrl();
		await visit( page, jobPress, '[jobpress show_search="yes"]' );

		const form = page.locator( '.jp-listing .jp-listing__search form' );
		await form.locator( 'input[name="job_search"]' ).fill( `Accountant ${ token }` );
		await form.getByRole( 'button', { name: 'Find Jobs' } ).click();

		await expect( page ).toHaveURL( new RegExp( `^${ jobsPageUrl.replace( /[.*+?^${}()|[\]\\]/g, '\\$&' ) }` ) );
		await expect( page ).toHaveURL( /job_search=/ );
		await expect( page.locator( '.jobpress-job-lists' ) ).toContainText( `Accountant ${ token }` );
		await expect( page.locator( '.jobpress-job-lists' ) ).not.toContainText( `Gardener ${ token }` );
	} );

	test( 'preselects a listing\'s single category', async ( { page, jobPress } ) => {
		await visit( page, jobPress, `[jobpress show_search="yes" category="${ category.slug }"]` );
		await expect( page.locator( '.jp-listing select[name="jobcategory"]' ) ).toHaveValue( category.slug );
	} );

	test( 'gives each form on a page its own IDs and labels', async ( { page, jobPress } ) => {
		await visit( page, jobPress, '[jobpress show_search="yes"][jobpress show_search="yes" design="3"]' );

		const ids = await page.locator( '[id]' ).evaluateAll( ( els ) => els.map( ( el ) => el.id ) );
		const duplicates = ids.filter( ( id, index ) => ids.indexOf( id ) !== index );
		expect( duplicates ).toEqual( [] );

		const forms = page.locator( '.jp-listing__search form' );
		await expect( forms ).toHaveCount( 2 );
		for ( const index of [ 0, 1 ] ) {
			const form = forms.nth( index );
			const keyword = form.locator( 'input[name="job_search"]' );
			await form.locator( `label[for="${ await keyword.getAttribute( 'id' ) }"]` ).click();
			await expect( keyword ).toBeFocused();
		}
	} );

	test( 'and the "View all jobs" link can be turned on for every listing', async ( { page, jobPress } ) => {
		await jobPress.updateSettings( 'shortcode', {
			jobpress_listing_show_search: 'yes',
			jobpress_listing_show_view_all: 'yes',
			jobpress_listing_view_all_text: `Every job ${ token }`,
		} );
		await visit( page, jobPress, '[jobpress][jobpress show_search="no" show_view_all="no"]' );

		const [ first, second ] = [ page.locator( '.jp-listing' ).nth( 0 ), page.locator( '.jp-listing' ).nth( 1 ) ];
		await expect( first.locator( '.jobpress-search-form-wrapper' ) ).toHaveCount( 1 );
		await expect( first.locator( '.jp-listing__view-all' ) ).toHaveText( `Every job ${ token }` );
		// A listing can turn them off again.
		await expect( second.locator( '.jobpress-search-form-wrapper' ) ).toHaveCount( 0 );
		await expect( second.locator( '.jp-listing__view-all' ) ).toHaveCount( 0 );
	} );

	test( 'still works on the jobs archive', async ( { page, jobPress } ) => {
		await page.goto( await jobPress.getJobsPageUrl() );

		const form = page.locator( '#jobpress-search-form' );
		await form.locator( '#jobpress-keyword' ).fill( `Accountant ${ token }` );
		await form.getByRole( 'button', { name: 'Find Jobs' } ).click();
		await expect( page ).toHaveURL( /job_search=/ );
		await expect( page.locator( '.jobpress-job-lists' ) ).toContainText( job.title.rendered );
	} );
} );
