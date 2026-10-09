/**
 * [jobpress] query attributes: which jobs a listing shows, in what order, how
 * many, and the "View all jobs" link to the Jobs Page.
 */
const { test, expect, uid, expectNoPhpErrors } = require( '../fixtures' );

test.describe( '[jobpress] query attributes', () => {
	let token, engineering, design, remote, contract, jobs;

	// Titles of the jobs a listing shows, in order.
	const listedTitles = ( page, scope = '.jp-listing' ) =>
		page
			.locator( `${ scope } .jp-listing__job-title` )
			.evaluateAll( ( els, t ) => els.map( ( el ) => el.textContent.trim() ).filter( ( title ) => title.endsWith( t ) ), token );

	const visit = async ( page, jobPress, shortcode ) => {
		const listingPage = await jobPress.createPage( `Query ${ uid() }`, shortcode );
		await page.goto( listingPage.link );
		await expectNoPhpErrors( page );
		return listingPage;
	};

	test.beforeEach( async ( { jobPress } ) => {
		token = uid();
		engineering = await jobPress.createTerm( 'jobpress_category', `Engineering ${ token }` );
		design = await jobPress.createTerm( 'jobpress_category', `Design ${ token }` );
		remote = await jobPress.createTerm( 'jobpress_type', `Remote ${ token }` );
		contract = await jobPress.createTerm( 'jobpress_type', `Contract ${ token }` );

		// Created in this order, so newest first is D, C, B, A.
		jobs = {
			a: await jobPress.createJob( { title: `Alpha ${ token }`, categories: [ engineering.id ], types: [ remote.id ] } ),
			b: await jobPress.createJob( { title: `Bravo ${ token }`, categories: [ engineering.id ], types: [ contract.id ] } ),
			c: await jobPress.createJob( { title: `Charlie ${ token }`, categories: [ design.id ], types: [ remote.id ] } ),
			d: await jobPress.createJob( { title: `Delta ${ token }` } ),
		};
		await jobPress.updateSettings( 'shortcode', { jobpress_design_type: '1' } );
	} );

	const t = ( name ) => `${ name } ${ token }`;

	test( 'filters by category', async ( { page, jobPress } ) => {
		await visit( page, jobPress, `[jobpress category="${ engineering.slug }"]` );
		expect( await listedTitles( page ) ).toEqual( [ t( 'Bravo' ), t( 'Alpha' ) ] );
	} );

	test( 'matches any of several categories', async ( { page, jobPress } ) => {
		await visit( page, jobPress, `[jobpress category="${ engineering.slug }, ${ design.slug }"]` );
		expect( await listedTitles( page ) ).toEqual( [ t( 'Charlie' ), t( 'Bravo' ), t( 'Alpha' ) ] );
	} );

	test( 'filters by type', async ( { page, jobPress } ) => {
		await visit( page, jobPress, `[jobpress type="${ remote.slug }"]` );
		expect( await listedTitles( page ) ).toEqual( [ t( 'Charlie' ), t( 'Alpha' ) ] );
	} );

	test( 'requires both category and type when both are set', async ( { page, jobPress } ) => {
		await visit( page, jobPress, `[jobpress category="${ engineering.slug }" type="${ remote.slug }"]` );
		expect( await listedTitles( page ) ).toEqual( [ t( 'Alpha' ) ] );
	} );

	test( 'includes and excludes specific jobs', async ( { page, jobPress } ) => {
		await visit( page, jobPress, `[jobpress include="${ jobs.a.id },${ jobs.c.id },${ jobs.d.id }" exclude="${ jobs.d.id }"]` );
		expect( await listedTitles( page ) ).toEqual( [ t( 'Charlie' ), t( 'Alpha' ) ] );
	} );

	test( 'orders by title', async ( { page, jobPress } ) => {
		await visit( page, jobPress, `[jobpress include="${ Object.values( jobs ).map( ( job ) => job.id ).join( ',' ) }" orderby="title" order="ASC"]` );
		expect( await listedTitles( page ) ).toEqual( [ t( 'Alpha' ), t( 'Bravo' ), t( 'Charlie' ), t( 'Delta' ) ] );
	} );

	test( 'falls back to newest first for invalid ordering', async ( { page, jobPress } ) => {
		await visit( page, jobPress, `[jobpress category="${ engineering.slug }" orderby="nonsense" order="sideways"]` );
		expect( await listedTitles( page ) ).toEqual( [ t( 'Bravo' ), t( 'Alpha' ) ] );
	} );

	test( 'limits the number of jobs and counts all matching jobs', async ( { page, jobPress } ) => {
		await visit( page, jobPress, `[jobpress category="${ engineering.slug },${ design.slug }" per_page="2"]` );
		expect( await listedTitles( page ) ).toEqual( [ t( 'Charlie' ), t( 'Bravo' ) ] );
		// The open positions count is the filtered total, not the number shown.
		await expect( page.locator( '.jp-listing__count' ) ).toHaveText( /\b0?3 open positions/ );
	} );

	test( 'limits grouped designs per category group', async ( { page, jobPress } ) => {
		await visit( page, jobPress, `[jobpress design="2" category="${ engineering.slug },${ design.slug }" per_page="1"]` );

		const groups = page.locator( '.jp-listing__group' );
		await expect( groups ).toHaveCount( 2 );
		await expect( groups.filter( { hasText: engineering.name } ).locator( '.jp-listing__card' ) ).toHaveCount( 1 );
		await expect( groups.filter( { hasText: design.name } ).locator( '.jp-listing__card' ) ).toHaveCount( 1 );
		// Only the listed categories get a group; jobs without one aren't listed.
		await expect( page.getByRole( 'heading', { name: 'Other openings' } ) ).toHaveCount( 0 );
	} );

	test( 'shows every job by default', async ( { page, jobPress } ) => {
		await visit( page, jobPress, '[jobpress]' );
		expect( ( await listedTitles( page ) ).sort() ).toEqual( [ t( 'Alpha' ), t( 'Bravo' ), t( 'Charlie' ), t( 'Delta' ) ] );
		await expect( page.locator( '.jp-listing__view-all' ) ).toHaveCount( 0 );
	} );

	test( 'links to the Jobs Page with a "View all jobs" button', async ( { page, jobPress } ) => {
		const jobsPageUrl = await jobPress.getJobsPageUrl();
		await visit( page, jobPress, '[jobpress per_page="1" show_view_all="yes" view_all_text="See every opening"]' );

		const viewAll = page.locator( '.jp-listing .jp-listing__view-all' );
		await expect( viewAll ).toHaveText( 'See every opening' );
		await expect( viewAll ).toHaveAttribute( 'href', jobsPageUrl );
	} );

	test( 'keeps a single category filter in the "View all jobs" link', async ( { page, jobPress } ) => {
		await visit( page, jobPress, `[jobpress category="${ engineering.slug }" show_view_all="yes"]` );

		await page.locator( '.jp-listing__view-all' ).click();
		await expect( page.locator( '.jobpress-job-lists' ) ).toContainText( t( 'Alpha' ) );
		await expect( page.locator( '.jobpress-job-lists' ) ).not.toContainText( t( 'Charlie' ) );
	} );
} );
