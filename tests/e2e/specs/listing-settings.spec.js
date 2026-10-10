/**
 * Global listing settings (Settings > Shortcodes > Listing Defaults) apply to
 * every listing, and each listing can override them with attributes.
 */
const { test, expect, uid, expectNoPhpErrors } = require( '../fixtures' );

test.describe( 'Listing defaults', () => {
	let token;

	const visit = async ( page, jobPress, shortcode ) => {
		const listingPage = await jobPress.createPage( `Defaults ${ uid() }`, shortcode );
		await page.goto( listingPage.link );
		await expectNoPhpErrors( page );
	};

	test.beforeEach( async ( { jobPress } ) => {
		token = uid();
		await jobPress.createJob( { title: `Defaults Job ${ token }` } );
		await jobPress.updateSettings( 'shortcode', { jobpress_design_type: '1' } );
	} );

	test( 'use the built-in header by default', async ( { page, jobPress } ) => {
		await visit( page, jobPress, '[jobpress]' );

		await expect( page.locator( '.jp-listing__title' ) ).toHaveText( 'Job openings' );
		await expect( page.locator( '.jp-listing__subtitle' ) ).toHaveText(
			'Find the right job for you no matter what it is that you do.'
		);
		await expect( page.locator( '.jp-listing__count' ) ).toBeVisible();
	} );

	test( 'set the title and subtitle of every listing', async ( { page, jobPress } ) => {
		await jobPress.updateSettings( 'shortcode', {
			jobpress_listing_title: `Global title ${ token }`,
			jobpress_listing_subtitle: `Global subtitle ${ token }`,
		} );

		await visit( page, jobPress, '[jobpress][jobpress title="Own title" subtitle=""]' );

		const [ first, second ] = [ page.locator( '.jp-listing' ).nth( 0 ), page.locator( '.jp-listing' ).nth( 1 ) ];
		await expect( first.locator( '.jp-listing__title' ) ).toHaveText( `Global title ${ token }` );
		await expect( first.locator( '.jp-listing__subtitle' ) ).toHaveText( `Global subtitle ${ token }` );
		// A listing's own attribute wins; an empty one inherits the global value.
		await expect( second.locator( '.jp-listing__title' ) ).toHaveText( 'Own title' );
		await expect( second.locator( '.jp-listing__subtitle' ) ).toHaveText( `Global subtitle ${ token }` );
	} );

	test( 'can hide the header parts of every listing', async ( { page, jobPress } ) => {
		await jobPress.updateSettings( 'shortcode', {
			jobpress_listing_show_title: 'no',
			jobpress_listing_show_subtitle: 'no',
			jobpress_listing_show_positions: 'no',
		} );

		await visit( page, jobPress, '[jobpress title="Hidden anyway"][jobpress show_title="yes" show_positions="yes"]' );

		const [ first, second ] = [ page.locator( '.jp-listing' ).nth( 0 ), page.locator( '.jp-listing' ).nth( 1 ) ];
		await expect( first.locator( '.jp-listing__title' ) ).toHaveCount( 0 );
		await expect( first.locator( '.jp-listing__subtitle' ) ).toHaveCount( 0 );
		await expect( first.locator( '.jp-listing__count' ) ).toHaveCount( 0 );
		// A listing can show them again.
		await expect( second.locator( '.jp-listing__title' ) ).toHaveText( 'Job openings' );
		await expect( second.locator( '.jp-listing__subtitle' ) ).toHaveCount( 0 );
		await expect( second.locator( '.jp-listing__count' ) ).toBeVisible();
	} );

	test( 'can hide the title, subtitle and count per listing', async ( { page, jobPress } ) => {
		await visit( page, jobPress, '[jobpress show_title="no" show_positions="no"]' );

		await expect( page.locator( '.jp-listing__title' ) ).toHaveCount( 0 );
		await expect( page.locator( '.jp-listing__subtitle' ) ).toBeVisible();
		await expect( page.locator( '.jp-listing__count' ) ).toHaveCount( 0 );
	} );

	test( 'keep the settings screen values after saving', async ( { page, jobPress } ) => {
		await jobPress.updateSettings( 'shortcode', {
			jobpress_listing_title: `Saved ${ token }`,
			jobpress_listing_show_subtitle: 'no',
		} );
		await jobPress.visitSettings( 'shortcode' );

		await expect( page.locator( '[name="jobpress_listing_title"]' ) ).toHaveValue( `Saved ${ token }` );
		await expect( page.locator( '[name="jobpress_listing_show_subtitle"]' ) ).not.toBeChecked();
		await expect( page.locator( '[name="jobpress_listing_show_title"]' ) ).toBeChecked();
	} );

	test.describe( 'card fields', () => {
		let category, type, job;

		test.beforeEach( async ( { jobPress } ) => {
			category = await jobPress.createTerm( 'jobpress_category', `Fields ${ token }` );
			type = await jobPress.createTerm( 'jobpress_type', `Shift ${ token }` );
			job = await jobPress.createJob( { title: `Fields Job ${ token }`, categories: [ category.id ], types: [ type.id ] } );
		} );

		// The card of the test job in a listing.
		const card = ( page, listing = page.locator( '.jp-listing' ).first() ) =>
			listing.locator( '.jp-listing__card' ).filter( { hasText: `Fields Job ${ token }` } );

		test( 'show the category and type by default and can hide them per listing', async ( { page, jobPress } ) => {
			const include = `include="${ job.id }"`;
			await visit(
				page,
				jobPress,
				`[jobpress design="1" ${ include }][jobpress design="1" ${ include } show_category="no"][jobpress design="5" ${ include } show_type="no"]`
			);
			const listings = page.locator( '.jp-listing' );

			await expect( card( page, listings.nth( 0 ) ) ).toContainText( category.name );
			await expect( card( page, listings.nth( 0 ) ) ).toContainText( type.name );
			await expect( card( page, listings.nth( 1 ) ) ).not.toContainText( category.name );
			// No leading separator when the category is hidden.
			await expect( card( page, listings.nth( 1 ) ).locator( '.jp-listing__meta' ) ).toHaveText( type.name );
			await expect( card( page, listings.nth( 2 ) ) ).toContainText( category.name );
			await expect( card( page, listings.nth( 2 ) ) ).not.toContainText( type.name );
		} );

		test( 'can hide the type, vacancies and deadline in the card designs', async ( { page, jobPress } ) => {
			await jobPress.setJobDetails( job, { jobpress_vacancy: '2', jobpress_apply_deadline: '2030-12-31' } );
			await visit(
				page,
				jobPress,
				`[jobpress design="3" include="${ job.id }"][jobpress design="3" include="${ job.id }" show_vacancy="no" show_deadline="no"]`
			);
			const listings = page.locator( '.jp-listing' );

			await expect( card( page, listings.nth( 0 ) ).locator( '.jp-listing__vacancy' ) ).toHaveText( 'Vacancies: 2' );
			await expect( card( page, listings.nth( 0 ) ).locator( '.jp-listing__deadline' ) ).toContainText( '2030' );
			await expect( card( page, listings.nth( 1 ) ).locator( '.jp-listing__type' ) ).toContainText( type.name );
			await expect( card( page, listings.nth( 1 ) ).locator( '.jp-listing__vacancy' ) ).toHaveCount( 0 );
			await expect( card( page, listings.nth( 1 ) ).locator( '.jp-listing__deadline' ) ).toHaveCount( 0 );
		} );

		test( 'leave out the details a job has no value for', async ( { page, jobPress } ) => {
			const bare = await jobPress.createJob( { title: `Fields Bare ${ token }` } );
			await jobPress.setJobDetails( job, { jobpress_vacancy: '2-3' } );
			const include = `include="${ job.id },${ bare.id }"`;
			await visit( page, jobPress, `[jobpress design="3" ${ include }][jobpress design="4" ${ include }]` );

			for ( const listing of [ page.locator( '.jp-design-v3' ), page.locator( '.jp-design-v4' ) ] ) {
				const bareCard = listing.locator( '.jp-listing__card' ).filter( { hasText: `Fields Bare ${ token }` } );
				await expect( bareCard ).toBeVisible();
				// No type, vacancies or deadline: no empty labels, and no empty meta line.
				await expect( bareCard.locator( '.jp-listing__meta' ) ).toHaveCount( 0 );
				await expect( card( page, listing ).locator( '.jp-listing__deadline' ) ).toHaveCount( 0 );
				// Vacancies are text, e.g. a range.
				await expect( card( page, listing ).locator( '.jp-listing__vacancy' ) ).toHaveText( 'Vacancies: 2-3' );
			}

			await page.goto( await jobPress.getJobsPageUrl() );
			const archiveCard = page.locator( '.jp-single-job-list' ).filter( { hasText: `Fields Bare ${ token }` } );
			await expect( archiveCard ).toBeVisible();
			await expect( archiveCard ).not.toContainText( 'Vacancies:' );
			await expect( archiveCard ).not.toContainText( 'Deadline:' );
			await expect( archiveCard ).not.toContainText( 'Job Type:' );
		} );

		test( 'change the button text per listing and globally', async ( { page, jobPress } ) => {
			await jobPress.updateSettings( 'shortcode', { jobpress_listing_button_text: 'Apply now' } );
			await visit( page, jobPress, `[jobpress design="1" include="${ job.id }"][jobpress design="3" include="${ job.id }" button_text="Details"]` );

			const listings = page.locator( '.jp-listing' );
			await expect( card( page, listings.nth( 0 ) ).locator( '.jp-listing__button' ) ).toHaveText( 'Apply now' );
			await expect( card( page, listings.nth( 1 ) ).locator( '.jp-listing__button' ) ).toHaveText( 'Details' );
		} );

		test( 'hidden globally, are hidden in listings and on the jobs archive', async ( { page, jobPress } ) => {
			await jobPress.setJobDetails( job, { jobpress_vacancy: '2', jobpress_apply_deadline: '2030-12-31' } );
			await jobPress.updateSettings( 'shortcode', {
				jobpress_listing_show_type: 'no',
				jobpress_listing_show_deadline: 'no',
				jobpress_listing_button_text: 'See job',
			} );

			await visit( page, jobPress, `[jobpress design="3" include="${ job.id }"][jobpress design="3" include="${ job.id }" show_type="yes"]` );
			const listings = page.locator( '.jp-listing' );
			await expect( card( page, listings.nth( 0 ) ).locator( '.jp-listing__type' ) ).toHaveCount( 0 );
			await expect( card( page, listings.nth( 0 ) ).locator( '.jp-listing__deadline' ) ).toHaveCount( 0 );
			await expect( card( page, listings.nth( 0 ) ).locator( '.jp-listing__vacancy' ) ).toBeVisible();
			// A listing can show a field again.
			await expect( card( page, listings.nth( 1 ) ).locator( '.jp-listing__type' ) ).toBeVisible();

			await page.goto( category.link );
			const archiveCard = page.locator( '.jp-single-job-list' ).filter( { hasText: `Fields Job ${ token }` } );
			await expect( archiveCard ).toBeVisible();
			await expect( archiveCard.locator( '.jp-listing__type' ) ).toHaveCount( 0 );
			await expect( archiveCard.locator( '.jp-listing__deadline' ) ).toHaveCount( 0 );
			await expect( archiveCard.locator( '.jp-listing__vacancy' ) ).toBeVisible();
			await expect( archiveCard.getByRole( 'link', { name: 'See job' } ) ).toBeVisible();
		} );
	} );
} );
