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

			await expectNoPhpErrors( page );
		} );
	}

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
