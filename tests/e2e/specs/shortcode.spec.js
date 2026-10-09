/**
 * The [jobpress] shortcode with each listing design (Settings > Shortcode > Select Design).
 */
const { test, expect, uid, expectNoPhpErrors } = require( '../fixtures' );

// Designs that group jobs under category headings.
const GROUPED_DESIGNS = [ 2, 4 ];

test.describe( '[jobpress] shortcode', () => {
	let token, shortcodePage, categorizedJob, uncategorizedJob, category;

	test.beforeEach( async ( { jobPress } ) => {
		token = uid();
		category = await jobPress.createTerm( 'jobpress_category', `Support ${ token }` );
		categorizedJob = await jobPress.createJob( {
			title: `Support Agent ${ token }`,
			categories: [ category.id ],
		} );
		uncategorizedJob = await jobPress.createJob( { title: `Office Manager ${ token }` } );
		shortcodePage = await jobPress.createPage(
			`Careers ${ token }`,
			`[jobpress title="Join us ${ token }" subtitle="We are hiring"]`
		);
	} );

	for ( const design of [ 1, 2, 3, 4, 5 ] ) {
		test( `renders design v${ design }`, async ( { page, jobPress } ) => {
			await jobPress.updateSettings( 'shortcode', { jobpress_design_type: String( design ) } );
			await page.goto( shortcodePage.link );

			await expect( page.locator( 'link#jobpress-css-css' ) ).toHaveAttribute(
				'href',
				new RegExp( `jobpress-style-v${ design }\\.css` )
			);
			await expect( page.getByRole( 'heading', { name: `Join us ${ token }` } ) ).toBeVisible();

			// Every design links each job to its page, including jobs without a category.
			for ( const job of [ categorizedJob, uncategorizedJob ] ) {
				const jobLink = page.locator( `a[href="${ job.link }"]` ).first();
				await expect( jobLink ).toBeVisible();
				await expect( page.getByText( job.title.rendered ).first() ).toBeVisible();
			}

			if ( GROUPED_DESIGNS.includes( design ) ) {
				await expect( page.getByRole( 'heading', { name: category.name } ) ).toBeVisible();
				await expect( page.getByRole( 'heading', { name: 'Other openings' } ) ).toBeVisible();
			}

			// The listing is wrapped in an element scoped to its design, and exposes
			// the jp-listing__* hook classes that styling (e.g. Elementor) targets.
			const listing = page.locator( `.jp-listing.jp-design-v${ design }` );
			await expect( listing ).toHaveCount( 1 );
			await expect( listing ).toHaveAttribute( 'id', /^jp-listing-\d+$/ );
			await expect( listing.locator( '.jp-listing__title' ) ).toHaveText( `Join us ${ token }` );
			await expect( listing.locator( '.jp-listing__subtitle' ) ).toHaveText( 'We are hiring' );
			for ( const job of [ categorizedJob, uncategorizedJob ] ) {
				await expect(
					listing.locator( '.jp-listing__card' ).filter( { hasText: job.title.rendered } )
				).toHaveCount( 1 );
			}
			await expect( listing.locator( '.jp-listing__job-title' ).first() ).toBeVisible();
			if ( design !== 5 ) {
				await expect( listing.locator( '.jp-listing__button' ).first() ).toBeVisible();
			}
			if ( GROUPED_DESIGNS.includes( design ) ) {
				await expect( listing.locator( '.jp-listing__group-title', { hasText: category.name } ) ).toBeVisible();
			}

			await expectNoPhpErrors( page );
		} );
	}

	test( 'gives each listing on a page its own ID', async ( { page, jobPress } ) => {
		const twoListings = await jobPress.createPage(
			`Two listings ${ token }`,
			'[jobpress title="First"][jobpress title="Second"]'
		);
		await page.goto( twoListings.link );

		const ids = await page.locator( '.jp-listing' ).evaluateAll( ( els ) => els.map( ( el ) => el.id ) );
		expect( ids ).toHaveLength( 2 );
		expect( new Set( ids ).size ).toBe( 2 );
	} );

	test( 'links job titles in design v1', async ( { page, jobPress } ) => {
		await jobPress.updateSettings( 'shortcode', { jobpress_design_type: '1' } );
		await page.goto( shortcodePage.link );

		await page.getByRole( 'link', { name: categorizedJob.title.rendered } ).click();
		await expect( page ).toHaveURL( categorizedJob.link );
	} );

	test( 'can hide the open positions count', async ( { page, jobPress } ) => {
		await jobPress.updateSettings( 'shortcode', { jobpress_design_type: '1' } );
		const hiddenCountPage = await jobPress.createPage(
			`Careers no count ${ token }`,
			'[jobpress show_positions="no"]'
		);

		await page.goto( shortcodePage.link );
		await expect( page.getByText( /open positions/ ) ).toBeVisible();

		await page.goto( hiddenCountPage.link );
		await expect( page.getByText( /open positions/ ) ).toHaveCount( 0 );
	} );
} );
