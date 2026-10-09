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

		// Computed styles of the listing's elements: unset style controls add no CSS.
		// (The wrapper itself and inherited text alignment are left out: themes lay out
		// post content differently from Elementor content.)
		const listingStyles = () =>
			page.locator( '.jp-listing' ).evaluate( ( listing ) =>
				[ ...listing.querySelectorAll( '*' ) ].map( ( el ) => {
					const style = getComputedStyle( el );
					return [ 'color', 'background-color', 'font-size', 'padding', 'margin', 'border', 'border-radius', 'gap', 'display' ]
						.map( ( prop ) => style.getPropertyValue( prop ) )
						.join( '|' );
				} )
			);

		await page.goto( shortcodePage.link );
		const expected = await listingHtml( page.locator( '.jp-listing' ) );
		const expectedStyles = await listingStyles();
		await page.goto( widgetPage.link );
		expect( await listingHtml( page.locator( '.jp-listing' ) ) ).toBe( expected );
		expect( await listingStyles() ).toEqual( expectedStyles );
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

	test.describe( 'style controls', () => {
		const px = ( size ) => ( { unit: 'px', size, sizes: [] } );
		const box = ( value ) => ( { unit: 'px', top: String( value ), right: String( value ), bottom: String( value ), left: String( value ), isLinked: true } );

		test( 'override the colors of one widget', async ( { page, jobPress } ) => {
			const widgetPage = await jobPress.createElementorPage( `Widget colors ${ token }`, [
				{ design: '1', brand_color: '#ff0000', heading_color: '#00aa00' },
				{ design: '1' },
			] );
			await page.goto( widgetPage.link );

			const [ styled, plain ] = [ page.locator( '.jp-listing' ).nth( 0 ), page.locator( '.jp-listing' ).nth( 1 ) ];
			await expect( styled.locator( '.jp-listing__button' ).first() ).toHaveCSS( 'background-color', 'rgb(255, 0, 0)' );
			await expect( styled.locator( '.jp-listing__title' ) ).toHaveCSS( 'color', 'rgb(0, 170, 0)' );
			await expect( plain.locator( '.jp-listing__button' ).first() ).not.toHaveCSS( 'background-color', 'rgb(255, 0, 0)' );
		} );

		for ( const design of [ 1, 2, 3, 4, 5 ] ) {
			test( `style the header, cards, job titles and details in design v${ design }`, async ( { page, jobPress } ) => {
				const widgetPage = await jobPress.createElementorPage( `Widget style ${ token }`, [
					{
						design: String( design ),
						header_align: 'left',
						header_spacing: px( 12 ),
						title_color: '#112233',
						title_typography_typography: 'custom',
						title_typography_font_size: px( 41 ),
						subtitle_color: '#223344',
						card_padding: box( 7 ),
						card_gap: px( 13 ),
						card_background: '#fafaf0',
						card_border_border: 'solid',
						card_border_width: box( 3 ),
						card_border_color: '#00ff00',
						card_radius: box( 9 ),
						job_title_color: '#334455',
						job_title_typography_typography: 'custom',
						job_title_typography_font_size: px( 23 ),
						meta_color: '#445566',
					},
				] );
				await page.goto( widgetPage.link );

				const listing = page.locator( '.jp-listing' );
				const header = listing.locator( '.jp-listing__header' );
				await expect( header ).toHaveCSS( 'text-align', 'left' );
				await expect( header ).toHaveCSS( 'margin-bottom', '12px' );
				await expect( listing.locator( '.jp-listing__title' ) ).toHaveCSS( 'color', 'rgb(17, 34, 51)' );
				await expect( listing.locator( '.jp-listing__title' ) ).toHaveCSS( 'font-size', '41px' );
				await expect( listing.locator( '.jp-listing__subtitle' ) ).toHaveCSS( 'color', 'rgb(34, 51, 68)' );

				const cards = listing.locator( '.jp-listing__card' );
				const card = cards.first();
				await expect( card ).toHaveCSS( 'padding-top', '7px' );
				await expect( card ).toHaveCSS( 'background-color', 'rgb(250, 250, 240)' );
				await expect( card ).toHaveCSS( 'border-bottom-width', '3px' );
				await expect( card ).toHaveCSS( 'border-bottom-color', 'rgb(0, 255, 0)' );
				await expect( card ).toHaveCSS( 'border-top-left-radius', '9px' );
				await expect( card ).toHaveCSS( 'margin-bottom', '0px' );

				await expect( listing.locator( '.jp-listing__job-title' ).first() ).toHaveCSS( 'font-size', '23px' );
				const titleLink = listing.locator( '.jp-listing__job-title a' ).first();
				await expect( ( await titleLink.count() ) ? titleLink : listing.locator( '.jp-listing__job-title' ).first() ).toHaveCSS( 'color', 'rgb(51, 68, 85)' );
				await expect( listing.locator( '.jp-listing__meta' ).first() ).toHaveCSS( 'color', 'rgb(68, 85, 102)' );

				// Space between the first two cards in one list (the grid uses a gap in both directions).
				const container = design === 5 ? listing.locator( '.jp-row' ) : listing.locator( '.jp-listing__jobs' ).first();
				await expect( container ).toHaveCSS( design === 5 ? 'column-gap' : 'row-gap', '13px' );
			} );
		}

		const buttonStyle = ( prefix ) => ( {
			[ `${ prefix }_typography_typography` ]: 'custom',
			[ `${ prefix }_typography_font_size` ]: px( 17 ),
			[ `${ prefix }_padding` ]: box( 6 ),
			[ `${ prefix }_radius` ]: box( 11 ),
			[ `${ prefix }_color` ]: '#010203',
			[ `${ prefix }_background` ]: '#ffee00',
		} );
		const expectButtonStyle = async ( button ) => {
			await expect( button ).toHaveCSS( 'font-size', '17px' );
			await expect( button ).toHaveCSS( 'padding-left', '6px' );
			await expect( button ).toHaveCSS( 'border-top-left-radius', '11px' );
			await expect( button ).toHaveCSS( 'color', 'rgb(1, 2, 3)' );
			await expect( button ).toHaveCSS( 'background-color', 'rgb(255, 238, 0)' );
		};

		for ( const design of [ 1, 3, 4 ] ) {
			test( `style the apply button in design v${ design }`, async ( { page, jobPress } ) => {
				const widgetPage = await jobPress.createElementorPage( `Widget button ${ token }`, [
					{ design: String( design ), ...buttonStyle( 'button' ) },
				] );
				await page.goto( widgetPage.link );
				await expectButtonStyle( page.locator( '.jp-listing .jp-listing__button' ).first() );
			} );
		}

		test( 'color the arrow link in design v2', async ( { page, jobPress } ) => {
			const widgetPage = await jobPress.createElementorPage( `Widget arrow ${ token }`, [
				{ design: '2', button_color: '#010203' },
			] );
			await page.goto( widgetPage.link );
			await expect( page.locator( '.jp-listing .jp-listing__button svg' ).first() ).toHaveCSS( 'fill', 'rgb(1, 2, 3)' );
		} );

		for ( const design of [ 2, 4 ] ) {
			test( `style the category group headers in design v${ design }`, async ( { page, jobPress } ) => {
				const category = await jobPress.createTerm( 'jobpress_category', `Group ${ token }` );
				await jobPress.createJob( { title: `Grouped ${ token }`, categories: [ category.id ] } );
				const widgetPage = await jobPress.createElementorPage( `Widget groups ${ token }`, [
					{
						design: String( design ),
						category: [ category.slug ],
						group_background: '#f0f0ff',
						group_padding: box( 8 ),
						group_title_color: '#123123',
						group_title_typography_typography: 'custom',
						group_title_typography_font_size: px( 27 ),
						group_count_color: '#321321',
						group_count_background: '#eeffee',
					},
				] );
				await page.goto( widgetPage.link );

				const header = page.locator( '.jp-listing .jp-listing__group-header' );
				await expect( header ).toHaveCSS( 'background-color', 'rgb(240, 240, 255)' );
				await expect( header ).toHaveCSS( 'padding-top', '8px' );
				await expect( header.locator( '.jp-listing__group-title' ) ).toHaveCSS( 'color', 'rgb(18, 49, 35)' );
				await expect( header.locator( '.jp-listing__group-title' ) ).toHaveCSS( 'font-size', '27px' );
				await expect( header.locator( '.jp-listing__group-count' ) ).toHaveCSS( 'color', 'rgb(50, 19, 33)' );
				await expect( header.locator( '.jp-listing__group-count' ) ).toHaveCSS( 'background-color', 'rgb(238, 255, 238)' );
			} );
		}

		test( 'style the search bar and the "View all jobs" link', async ( { page, jobPress } ) => {
			const widgetPage = await jobPress.createElementorPage( `Widget search ${ token }`, [
				{
					show_search: 'yes',
					show_view_all: 'yes',
					search_background: '#fdf6e3',
					search_radius: box( 2 ),
					search_text_color: '#0a0b0c',
					...buttonStyle( 'search_button' ),
					...buttonStyle( 'view_all' ),
					view_all_align: 'right',
				},
			] );
			await page.goto( widgetPage.link );

			const bar = page.locator( '.jp-listing .jobpress-search-form-wrapper' );
			await expect( bar ).toHaveCSS( 'background-color', 'rgb(253, 246, 227)' );
			await expect( bar ).toHaveCSS( 'border-top-left-radius', '2px' );
			await expect( bar.locator( 'input[name="job_search"]' ) ).toHaveCSS( 'color', 'rgb(10, 11, 12)' );
			await expectButtonStyle( bar.locator( '.search-submit' ) );

			await expectButtonStyle( page.locator( '.jp-listing .jp-listing__view-all' ) );
			await expect( page.locator( '.jp-listing .jp-listing__footer' ) ).toHaveCSS( 'text-align', 'right' );
		} );

		test( 'set the grid columns per device', async ( { page, jobPress } ) => {
			const widgetPage = await jobPress.createElementorPage( `Widget grid ${ token }`, [
				{ design: '5', columns: '2', columns_mobile: '1' },
			] );
			await page.goto( widgetPage.link );

			const columnCount = () =>
				page.locator( '.jp-listing .jp-row' ).evaluate( ( el ) => getComputedStyle( el ).gridTemplateColumns.split( ' ' ).length );
			expect( await columnCount() ).toBe( 2 );
			await page.setViewportSize( { width: 390, height: 800 } );
			await expect.poll( columnCount ).toBe( 1 );
		} );
	} );
} );
