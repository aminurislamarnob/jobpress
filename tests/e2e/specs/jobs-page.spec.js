/**
 * The Jobs Page: listing, keyword search, category/type filters, empty state, pagination.
 */
const { test, expect, uid, expectNoPhpErrors } = require( '../fixtures' );

test.describe( 'Jobs page', () => {
	let token, jobsPageUrl, engineering, fullTime, backendJob, designJob;

	test.beforeEach( async ( { jobPress } ) => {
		token = uid();
		engineering = await jobPress.createTerm( 'jobpress_category', `Engineering ${ token }` );
		fullTime = await jobPress.createTerm( 'jobpress_type', `Full Time ${ token }` );
		backendJob = await jobPress.createJob( {
			title: `Backend Developer ${ token }`,
			categories: [ engineering.id ],
			types: [ fullTime.id ],
		} );
		designJob = await jobPress.createJob( { title: `Product Designer ${ token }` } );
		jobsPageUrl = await jobPress.getJobsPageUrl();
	} );

	test( 'lists published jobs with links to the job page', async ( { page } ) => {
		await page.goto( jobsPageUrl );

		const jobLink = page.getByRole( 'link', { name: backendJob.title.rendered } );
		await expect( jobLink ).toBeVisible();
		await expect( page.getByRole( 'link', { name: designJob.title.rendered } ) ).toBeVisible();
		await expect( page.locator( '#jobpress-search-form' ) ).toBeVisible();
		await expectNoPhpErrors( page );

		await jobLink.click();
		await expect( page ).toHaveURL( backendJob.link );
	} );

	test( 'searches jobs by keyword', async ( { page } ) => {
		await page.goto( jobsPageUrl );

		await page.locator( '#jobpress-keyword' ).fill( `Designer ${ token }` );
		await page.getByRole( 'button', { name: 'Find Jobs' } ).click();

		await expect( page ).toHaveURL( /job_search=/ );
		await expect( page.getByRole( 'link', { name: designJob.title.rendered } ) ).toBeVisible();
		await expect( page.getByRole( 'link', { name: backendJob.title.rendered } ) ).toHaveCount( 0 );
		await expect( page.locator( '#jobpress-keyword' ) ).toHaveValue( `Designer ${ token }` );
	} );

	test( 'filters jobs by category', async ( { page } ) => {
		await page.goto( jobsPageUrl );

		await page.locator( '#jobpress-category' ).selectOption( engineering.slug );
		await page.getByRole( 'button', { name: 'Find Jobs' } ).click();

		await expect( page ).toHaveURL( new RegExp( `jobcategory=${ engineering.slug }` ) );
		await expect( page.getByRole( 'link', { name: backendJob.title.rendered } ) ).toBeVisible();
		await expect( page.getByRole( 'link', { name: designJob.title.rendered } ) ).toHaveCount( 0 );
	} );

	test( 'filters jobs by type', async ( { page } ) => {
		await page.goto( jobsPageUrl );

		await page.locator( '#jobpress-type' ).selectOption( fullTime.slug );
		await page.getByRole( 'button', { name: 'Find Jobs' } ).click();

		await expect( page ).toHaveURL( new RegExp( `jobtype=${ fullTime.slug }` ) );
		await expect( page.getByRole( 'link', { name: backendJob.title.rendered } ) ).toBeVisible();
		await expect( page.getByRole( 'link', { name: designJob.title.rendered } ) ).toHaveCount( 0 );
	} );

	test( 'shows an empty state when nothing matches', async ( { page } ) => {
		await page.goto( jobsPageUrl );

		await page.locator( '#jobpress-keyword' ).fill( `no-such-job-${ token }` );
		await page.getByRole( 'button', { name: 'Find Jobs' } ).click();

		await expect( page.getByRole( 'heading', { name: 'No jobs found' } ) ).toBeVisible();
		await expectNoPhpErrors( page );
	} );

	test( 'paginates using the Jobs Per Page setting', async ( { page, jobPress } ) => {
		await jobPress.updateSettings( 'general', { jobpress_jobs_per_page: '1' } );
		await page.goto( jobsPageUrl );

		const pagination = page.getByRole( 'navigation', { name: 'Jobs Pagination' } );
		await expect( pagination ).toBeVisible();
		await expect( page.locator( '.jp-single-job-list' ) ).toHaveCount( 1 );
		const firstPageJob = await page.locator( '.jp-single-job-list h4' ).textContent();

		await pagination.getByRole( 'link', { name: '2', exact: true } ).click();

		await expect( page.locator( '.jp-single-job-list' ) ).toHaveCount( 1 );
		await expect( page.locator( '.jp-single-job-list h4' ) ).not.toHaveText( firstPageJob );
		await expectNoPhpErrors( page );
	} );
} );
