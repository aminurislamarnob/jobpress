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
