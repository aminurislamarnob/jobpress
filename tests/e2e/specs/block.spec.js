/**
 * The JobPress Jobs block. Pages are created with block markup over REST and
 * asserted on the frontend; one test drives the editor itself. Needs
 * WordPress 6.3+, where the block is registered.
 */
const { test, expect, uid, expectNoPhpErrors } = require( '../fixtures' );

// The listing markup, with the per-instance IDs normalized.
const listingHtml = ( locator ) =>
	locator.evaluate( ( el ) => el.outerHTML.replace( /jp-listing-\d+/g, 'jp-listing-N' ) );

test.describe( 'JobPress Jobs block', () => {
	let token, job;

	test.beforeAll( async ( { requestUtils } ) => {
		const registered = await requestUtils
			.rest( { path: '/wp/v2/block-types/jobpress/jobs' } )
			.then( () => true, () => false );
		test.skip( ! registered, 'The block is not registered (WordPress before 6.3)' );
	} );

	test.beforeEach( async ( { jobPress } ) => {
		token = uid();
		job = await jobPress.createJob( { title: `Block Job ${ token }` } );
	} );

	test( 'renders like the shortcode when nothing is set', async ( { page, jobPress } ) => {
		const blockPage = await jobPress.createBlockPage( `Block page ${ token }` );
		const shortcodePage = await jobPress.createPage( `Shortcode page ${ token }`, '[jobpress]' );

		await page.goto( shortcodePage.link );
		const expected = await listingHtml( page.locator( '.jp-listing' ) );
		await page.goto( blockPage.link );
		const listing = page.locator( '.wp-block-jobpress-jobs > .jp-listing' );
		expect( await listingHtml( listing ) ).toBe( expected );
		await expect( listing.locator( `a[href="${ job.link }"]` ).first() ).toBeVisible();
		await expectNoPhpErrors( page );
	} );

	test( 'renders the selected design', async ( { page, jobPress } ) => {
		await jobPress.updateSettings( 'shortcode', { jobpress_design_type: '1' } );
		const blockPage = await jobPress.createBlockPage( `Block page ${ token }`, [ { design: '5' } ] );
		await page.goto( blockPage.link );

		const listing = page.locator( '.jp-listing' );
		await expect( listing ).toHaveClass( /jp-design-v5/ );
		// Styled by the v5 stylesheet: the grid.
		await expect( listing.locator( '.jobpress-job-grids .jp-row' ) ).toHaveCSS( 'display', 'grid' );
		await expectNoPhpErrors( page );
	} );

	test( 'inherits the Listing Defaults it does not override', async ( { page, jobPress } ) => {
		await jobPress.updateSettings( 'shortcode', {
			jobpress_design_type: '1',
			jobpress_listing_title: `Global title ${ token }`,
			jobpress_listing_show_subtitle: 'no',
			jobpress_listing_show_positions: 'no',
			jobpress_listing_button_text: `Global button ${ token }`,
		} );
		const blockPage = await jobPress.createBlockPage( `Block page ${ token }`, [
			{},
			{ title: `Own title ${ token }`, show_subtitle: 'yes', show_positions: 'yes', button_text: '' },
		] );
		await page.goto( blockPage.link );

		const [ first, second ] = [ page.locator( '.jp-listing' ).nth( 0 ), page.locator( '.jp-listing' ).nth( 1 ) ];
		await expect( first.locator( '.jp-listing__title' ) ).toHaveText( `Global title ${ token }` );
		await expect( first.locator( '.jp-listing__subtitle' ) ).toHaveCount( 0 );
		await expect( first.locator( '.jp-listing__count' ) ).toHaveCount( 0 );
		await expect( first.locator( '.jp-listing__button' ).first() ).toHaveText( `Global button ${ token }` );

		// A block's own setting wins; an empty one inherits the global value.
		await expect( second.locator( '.jp-listing__title' ) ).toHaveText( `Own title ${ token }` );
		await expect( second.locator( '.jp-listing__subtitle' ) ).toBeVisible();
		await expect( second.locator( '.jp-listing__count' ) ).toBeVisible();
		await expect( second.locator( '.jp-listing__button' ).first() ).toHaveText( `Global button ${ token }` );
		await expectNoPhpErrors( page );
	} );

	test( 'renders like the shortcode with every content setting', async ( { page, jobPress } ) => {
		const attributes = {
			design: '3',
			show_title: 'yes',
			title: `Ops roles "${ token }" <b>`,
			show_subtitle: 'no',
			show_positions: 'no',
			show_type: 'yes',
			show_vacancy: 'no',
			show_deadline: 'yes',
			show_experience: 'no',
			button_text: 'Open role',
			show_search: 'yes',
			show_view_all: 'yes',
			view_all_text: 'All ops jobs',
		};
		const blockPage = await jobPress.createBlockPage( `Block page ${ token }`, [ attributes ] );
		const shortcodePage = await jobPress.createPage(
			`Shortcode page ${ token }`,
			`[jobpress design="3" show_title="yes" title='Ops roles "${ token }" <b>' show_subtitle="no" show_positions="no" ` +
				'show_type="yes" show_vacancy="no" show_deadline="yes" show_experience="no" button_text="Open role" ' +
				'show_search="yes" show_view_all="yes" view_all_text="All ops jobs"]'
		);

		const jobsPageUrl = await jobPress.getJobsPageUrl();

		await page.goto( shortcodePage.link );
		const expected = await listingHtml( page.locator( '.jp-listing' ) );
		await page.goto( blockPage.link );
		const listing = page.locator( '.jp-listing' );
		expect( await listingHtml( listing ) ).toBe( expected );

		// And the settings took effect.
		await expect( listing ).toHaveClass( /jp-design-v3/ );
		await expect( listing.locator( '.jp-listing__title' ) ).toHaveText( `Ops roles "${ token }" <b>` );
		await expect( listing.locator( '.jp-listing__button' ).first() ).toHaveText( 'Open role' );
		await expect( listing.locator( '.jp-listing__search form' ) ).toBeVisible();
		await expect( listing.locator( '.jp-listing__view-all' ) ).toHaveText( 'All ops jobs' );
		await expect( listing.locator( '.jp-listing__view-all' ) ).toHaveAttribute( 'href', jobsPageUrl );
		await expectNoPhpErrors( page );
	} );

	test( 'lists the jobs its query settings select', async ( { page, jobPress } ) => {
		const category = await jobPress.createTerm( 'jobpress_category', `Ops ${ token }` );
		const type = await jobPress.createTerm( 'jobpress_type', `Hybrid ${ token }` );
		const otherType = await jobPress.createTerm( 'jobpress_type', `Onsite ${ token }` );
		const create = ( title, types = [ type.id ] ) =>
			jobPress.createJob( { title: `${ title } ${ token }`, categories: [ category.id ], types } );
		const lead = await create( 'Ops Lead' );
		const analyst = await create( 'Ops Analyst' );
		const intern = await create( 'Ops Intern' );
		await create( 'Ops Onsite', [ otherType.id ] );
		const zeta = await create( 'Ops Zeta' );

		const blockPage = await jobPress.createBlockPage( `Block page ${ token }`, [
			// In the category and type, without one job, by title: the first two.
			{ design: '1', category: category.slug, type: type.slug, exclude: String( intern.id ), orderby: 'title', order: 'ASC', per_page: '2' },
			// Only the chosen jobs, newest first.
			{ design: '1', include: `${ lead.id },${ zeta.id }` },
		] );
		await page.goto( blockPage.link );

		const titles = ( n ) => page.locator( '.jp-listing' ).nth( n ).locator( '.jp-listing__job-title' );
		await expect( titles( 0 ) ).toHaveText( [ analyst.title.rendered, lead.title.rendered ] );
		await expect( titles( 1 ) ).toHaveText( [ zeta.title.rendered, lead.title.rendered ] );
		await expectNoPhpErrors( page );
	} );

	test( 'picks query terms and jobs by name in the editor', async ( { admin, editor, page, jobPress } ) => {
		const category = await jobPress.createTerm( 'jobpress_category', `Picked ${ token }` );
		const picked = await jobPress.createJob( { title: `Picked Job ${ token }`, categories: [ category.id ] } );
		await jobPress.createJob( { title: `Other Job ${ token }` } );

		await admin.createNewPost( { postType: 'page', title: `Block editor ${ token }` } );
		await editor.insertBlock( { name: 'jobpress/jobs', attributes: { design: '1' } } );
		await editor.openDocumentSettingsSidebar();
		const sidebar = page.getByRole( 'region', { name: 'Editor settings' } );
		await sidebar.getByRole( 'button', { name: 'Query' } ).click();

		await sidebar.getByLabel( 'Categories' ).fill( `Picked ${ token }` );
		await page.getByRole( 'option', { name: `Picked ${ token }` } ).click();
		await sidebar.getByLabel( 'Order by' ).selectOption( 'title' );

		const blockAttributes = () =>
			page.evaluate( () => window.wp.data.select( 'core/block-editor' ).getSelectedBlock().attributes );
		await expect.poll( blockAttributes ).toMatchObject( { category: category.slug, orderby: 'title' } );

		const preview = editor.canvas.locator( '.wp-block-jobpress-jobs .jp-listing' );
		await expect( preview.locator( '.jp-listing__job-title' ) ).toHaveText( [ picked.title.rendered ] );

		await sidebar.getByLabel( 'Leave out these jobs' ).fill( `Picked Job ${ token }` );
		await page.getByRole( 'option', { name: `Picked Job ${ token } (#${ picked.id })` } ).click();
		await expect.poll( blockAttributes ).toMatchObject( { exclude: String( picked.id ) } );
		await expect( preview.locator( '.jp-listing__job-title' ) ).toHaveCount( 0 );
	} );

	test( 'loads the stylesheets of the designs on the page in the head', async ( { page, jobPress } ) => {
		await jobPress.updateSettings( 'shortcode', { jobpress_design_type: '1' } );
		// A v5 block, and a v2 block nested in a group and columns.
		const blockPage = await jobPress.createPage(
			`Block page ${ token }`,
			'<!-- wp:jobpress/jobs {"design":"5"} /-->\n\n' +
				'<!-- wp:group --><div class="wp-block-group"><!-- wp:columns --><div class="wp-block-columns">' +
				'<!-- wp:column --><div class="wp-block-column"><!-- wp:jobpress/jobs {"design":"2"} /--></div><!-- /wp:column -->' +
				'</div><!-- /wp:columns --></div><!-- /wp:group -->'
		);
		await page.goto( blockPage.link );

		const designStylesheets = await page
			.locator( 'link[id^="jobpress-design-v"]' )
			.evaluateAll( ( links ) => links.map( ( link ) => [ link.id, link.parentElement.tagName ] ) );
		expect( designStylesheets.sort() ).toEqual( [
			[ 'jobpress-design-v2-css', 'HEAD' ],
			[ 'jobpress-design-v5-css', 'HEAD' ],
		] );
		await expect( page.locator( 'head link#jobpress-common-css' ) ).toHaveCount( 1 );

		// Each listing is styled by its own design.
		const [ grid, grouped ] = [ page.locator( '.jp-design-v5' ), page.locator( '.jp-design-v2' ) ];
		await expect( grid.locator( '.jobpress-job-grids .jp-row' ) ).toHaveCSS( 'display', 'grid' );
		await expect( grouped.locator( '.jp-listing__job-title' ).first() ).toBeVisible();
		await expect( grouped.locator( '.jobpress-job-grids' ) ).toHaveCount( 0 );
		await expectNoPhpErrors( page );
	} );

	test( 'shows the settings each design uses, with the global values they inherit', async ( { admin, editor, page, jobPress } ) => {
		await jobPress.updateSettings( 'shortcode', {
			jobpress_design_type: '1',
			jobpress_listing_show_title: 'yes',
			jobpress_listing_show_location: 'no',
			jobpress_listing_title: `Global title ${ token }`,
		} );
		await admin.createNewPost( { postType: 'page', title: `Block editor ${ token }` } );
		await editor.insertBlock( { name: 'jobpress/jobs' } );
		await editor.openDocumentSettingsSidebar();
		const sidebar = page.getByRole( 'region', { name: 'Editor settings' } );

		await expect( sidebar.getByLabel( 'Design' ) ).toContainText( 'Default (Design V1: list)' );

		await sidebar.getByRole( 'button', { name: 'Header' } ).click();
		await expect( sidebar.getByLabel( 'Title', { exact: true } ) ).toContainText( 'Default (Show)' );
		await expect( sidebar.getByLabel( 'Title text', { exact: true } ) ).toHaveAttribute( 'placeholder', `Global title ${ token }` );
		await sidebar.getByLabel( 'Title', { exact: true } ).selectOption( 'no' );
		await expect( sidebar.getByLabel( 'Title text', { exact: true } ) ).toHaveCount( 0 );

		// Card fields: v1 shows the location (hidden globally), v3 the vacancies instead.
		await sidebar.getByRole( 'button', { name: 'Job Card' } ).click();
		await expect( sidebar.getByLabel( 'Location' ) ).toContainText( 'Default (Hide)' );
		await expect( sidebar.getByLabel( 'Vacancies' ) ).toHaveCount( 0 );
		await sidebar.getByLabel( 'Design' ).selectOption( '3' );
		await expect( sidebar.getByLabel( 'Location' ) ).toHaveCount( 0 );
		await expect( sidebar.getByLabel( 'Vacancies' ) ).toBeVisible();
		await expect( sidebar.getByLabel( 'Open positions count' ) ).toBeVisible();
		await sidebar.getByLabel( 'Design' ).selectOption( '2' );
		await expect( sidebar.getByLabel( 'Open positions count' ) ).toHaveCount( 0 );

		// The preview follows the settings.
		await sidebar.getByLabel( 'Vacancies' ).waitFor( { state: 'detached' } );
		const preview = editor.canvas.locator( '.wp-block-jobpress-jobs .jp-listing' );
		await expect( preview ).toHaveClass( /jp-design-v2/ );
		await expect( preview.locator( '.jp-listing__title' ) ).toHaveCount( 0 );
	} );

	test( 'can be added and configured in the editor', async ( { admin, editor, page, jobPress } ) => {
		await jobPress.updateSettings( 'shortcode', { jobpress_design_type: '1' } );
		await admin.createNewPost( { postType: 'page', title: `Block editor ${ token }` } );
		await editor.insertBlock( { name: 'jobpress/jobs' } );

		// The server-rendered preview, in the global design.
		const preview = editor.canvas.locator( '.wp-block-jobpress-jobs .jp-listing' );
		await expect( preview ).toHaveClass( /jp-design-v1/ );
		await expect( preview.getByText( `Block Job ${ token }` ) ).toBeVisible();

		// Clicking a job link in the preview selects the block rather than leaving the editor.
		const editorUrl = page.url();
		const box = await preview.locator( `a[href="${ job.link }"]` ).first().boundingBox();
		await page.mouse.click( box.x + box.width / 2, box.y + box.height / 2 );
		expect( page.url() ).toBe( editorUrl );

		await editor.openDocumentSettingsSidebar();
		await page.getByRole( 'region', { name: 'Editor settings' } ).getByLabel( 'Design' ).selectOption( '5' );
		await expect( preview ).toHaveClass( /jp-design-v5/ );

		const postId = await editor.publishPost();
		jobPress.deleteAfterTest( postId );

		const saved = await page.evaluate( () => window.wp.data.select( 'core/editor' ).getEditedPostContent() );
		expect( saved ).toContain( '<!-- wp:jobpress/jobs {"design":"5"} /-->' );

		await page.goto( `/?page_id=${ postId }` );
		await expect( page.locator( '.wp-block-jobpress-jobs > .jp-listing' ) ).toHaveClass( /jp-design-v5/ );
		await expectNoPhpErrors( page );
	} );
} );
