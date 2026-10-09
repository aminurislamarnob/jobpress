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

			// Only this design's stylesheet loads, in the head.
			await expect( page.locator( `head link#jobpress-design-v${ design }-css` ) ).toHaveAttribute(
				'href',
				new RegExp( `jobpress-style-v${ design }\\.css` )
			);
			await expect( page.locator( 'link[id^="jobpress-design-v"]' ) ).toHaveCount( 1 );
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

	test( 'uses the design attribute over the global design', async ( { page, jobPress } ) => {
		await jobPress.updateSettings( 'shortcode', { jobpress_design_type: '1' } );
		const designPage = await jobPress.createPage( `Careers grid ${ token }`, '[jobpress design="5"]' );
		await page.goto( designPage.link );

		await expect( page.locator( '.jp-listing.jp-design-v5' ) ).toHaveCount( 1 );
		await expect( page.locator( 'head link#jobpress-design-v5-css' ) ).toHaveCount( 1 );
		// The global design's stylesheet isn't needed on this page.
		await expect( page.locator( 'link#jobpress-design-v1-css' ) ).toHaveCount( 0 );
		await expect( page.locator( '.jp-listing__jobs .jp-row' ) ).toHaveCSS( 'display', 'grid' );
	} );

	test( 'falls back to the global design for an invalid design attribute', async ( { page, jobPress } ) => {
		await jobPress.updateSettings( 'shortcode', { jobpress_design_type: '3' } );
		const designPage = await jobPress.createPage( `Careers invalid ${ token }`, '[jobpress design="9"]' );
		await page.goto( designPage.link );

		await expect( page.locator( '.jp-listing.jp-design-v3' ) ).toHaveCount( 1 );
	} );

	test( 'renders different designs side by side on one page', async ( { page, jobPress } ) => {
		await jobPress.updateSettings( 'shortcode', { jobpress_design_type: '1' } );
		const mixedPage = await jobPress.createPage(
			`Careers mixed ${ token }`,
			'[jobpress design="1" title="List"][jobpress design="3" title="Cards"][jobpress design="5" title="Grid"]'
		);
		await page.goto( mixedPage.link );

		for ( const design of [ 1, 3, 5 ] ) {
			await expect( page.locator( `head link#jobpress-design-v${ design }-css` ) ).toHaveCount( 1 );
		}

		// Each listing keeps its own design's look: v1 is one bordered box with
		// divided rows, v3 separate shadowed cards, v5 a grid.
		const v1Card = page.locator( '.jp-design-v1 .jp-listing__card' ).first();
		const v3Card = page.locator( '.jp-design-v3 .jp-listing__card' ).first();
		await expect( page.locator( '.jp-design-v1 .jp-listing__jobs' ) ).toHaveCSS( 'border-top-width', '1px' );
		await expect( v1Card ).toHaveCSS( 'box-shadow', 'none' );
		await expect( v1Card ).toHaveCSS( 'margin-bottom', '0px' );
		await expect( page.locator( '.jp-design-v3 .jp-listing__jobs' ) ).toHaveCSS( 'border-top-width', '0px' );
		await expect( v3Card ).not.toHaveCSS( 'box-shadow', 'none' );
		await expect( v3Card ).toHaveCSS( 'margin-bottom', '20px' );
		await expect( page.locator( '.jp-design-v5 .jp-listing__jobs .jp-row' ) ).toHaveCSS( 'display', 'grid' );
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
