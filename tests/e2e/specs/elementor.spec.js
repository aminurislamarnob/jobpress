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

	test( 'keeps the positions count hidden in widgets saved before 2.3.0', async ( { page, jobPress } ) => {
		// The old show_positions switcher saved '' when switched off.
		const widgetPage = await jobPress.createElementorPage( `Widget page ${ token }`, [
			{ show_positions: '' },
		] );
		await page.goto( widgetPage.link );

		await expect( page.locator( '.jp-listing' ) ).toHaveCount( 1 );
		await expect( page.getByText( /open positions/ ) ).toHaveCount( 0 );
	} );

	// The listing markup, with the per-instance IDs normalized.
	const listingHtml = ( locator ) =>
		locator.evaluate( ( el ) => el.outerHTML.replace( /jp-listing-\d+/g, 'jp-listing-N' ) );

	test( 'renders like the shortcode when nothing is set', async ( { page, jobPress } ) => {
		const widgetPage = await jobPress.createElementorPage( `Widget page ${ token }`, [ {} ] );
		const shortcodePage = await jobPress.createPage( `Shortcode page ${ token }`, '[jobpress]' );

		await page.goto( shortcodePage.link );
		const expected = await listingHtml( page.locator( '.jp-listing' ) );
		await page.goto( widgetPage.link );
		expect( await listingHtml( page.locator( '.jp-listing' ) ) ).toBe( expected );
	} );

	test( 'renders like the shortcode with every content and query setting', async ( { page, jobPress } ) => {
		const category = await jobPress.createTerm( 'jobpress_category', `Ops ${ token }` );
		const type = await jobPress.createTerm( 'jobpress_type', `Hybrid ${ token }` );
		const first = await jobPress.createJob( { title: `Ops Lead ${ token }`, categories: [ category.id ], types: [ type.id ] } );
		const second = await jobPress.createJob( { title: `Ops Analyst ${ token }`, categories: [ category.id ], types: [ type.id ] } );
		const third = await jobPress.createJob( { title: `Ops Intern ${ token }`, categories: [ category.id ], types: [ type.id ] } );

		const settings = {
			design: '3',
			show_title: 'yes',
			title: `Ops roles ${ token }`,
			show_subtitle: 'no',
			show_count: 'no',
			show_type: 'yes',
			show_vacancy: 'no',
			show_deadline: 'yes',
			show_experience: 'no',
			button_text: 'Open role',
			show_search: 'yes',
			show_view_all: 'yes',
			view_all_text: 'All ops jobs',
			per_page: 2,
			category: [ category.slug ],
			type: [ type.slug ],
			exclude: [ String( third.id ) ],
			orderby: 'title',
			order: 'ASC',
		};
		const widgetPage = await jobPress.createElementorPage( `Widget page ${ token }`, [ settings ] );
		const shortcodePage = await jobPress.createPage(
			`Shortcode page ${ token }`,
			`[jobpress design="3" show_title="yes" title="Ops roles ${ token }" show_subtitle="no" show_positions="no" ` +
				'show_type="yes" show_vacancy="no" show_deadline="yes" show_experience="no" button_text="Open role" ' +
				`show_search="yes" show_view_all="yes" view_all_text="All ops jobs" per_page="2" category="${ category.slug }" ` +
				`type="${ type.slug }" exclude="${ third.id }" orderby="title" order="ASC"]`
		);

		await page.goto( shortcodePage.link );
		const expected = await listingHtml( page.locator( '.jp-listing' ) );
		await page.goto( widgetPage.link );
		const widget = page.locator( '.jp-listing' );
		expect( await listingHtml( widget ) ).toBe( expected );

		// And the settings took effect.
		await expect( widget ).toHaveClass( /jp-design-v3/ );
		await expect( page.locator( 'head link#jobpress-design-v3-css' ) ).toHaveCount( 1 );
		await expect( widget.locator( '.jp-listing__title' ) ).toHaveText( `Ops roles ${ token }` );
		await expect( widget.locator( '.jp-listing__job-title' ) ).toHaveText( [ second.title.rendered, first.title.rendered ] );
		await expect( widget.locator( '.jp-listing__button' ).first() ).toHaveText( 'Open role' );
		await expect( widget.locator( '.jp-listing__view-all' ) ).toHaveText( 'All ops jobs' );
		await expectNoPhpErrors( page );
	} );
} );
