/**
 * Creating a job in the block editor with the "Job Others Informations" meta box,
 * then checking the single job page.
 */
const { test, expect, uid, expectNoPhpErrors } = require( '../fixtures' );

test.describe( 'Job editor', () => {
	test.afterEach( async ( { requestUtils } ) => {
		// Jobs created through the editor aren't tracked by the jobPress fixture.
		const jobs = await requestUtils.rest( {
			path: '/wp/v2/jobpress',
			params: { search: 'E2E Editor Job', status: 'any', per_page: 100 },
		} );
		for ( const job of jobs ) {
			await requestUtils.rest( {
				method: 'DELETE',
				path: `/wp/v2/jobpress/${ job.id }`,
				params: { force: true },
			} );
		}
	} );

	test( 'saves job details and shows them on the job page', async ( { admin, editor, page } ) => {
		const title = `E2E Editor Job ${ uid() }`;

		await admin.createNewPost( { postType: 'jobpress', title } );

		// Recent WordPress versions collapse classic meta boxes into a "Meta Boxes" panel.
		const metaBox = page.locator( '#jobpress_meta_box' );
		const metaBoxesToggle = page.getByRole( 'button', { name: 'Meta Boxes' } );
		await expect( metaBoxesToggle.or( metaBox ).first() ).toBeVisible();
		if ( ( await metaBoxesToggle.count() ) && ( await metaBoxesToggle.getAttribute( 'aria-expanded' ) ) === 'false' ) {
			// The panel's resize handle overlaps the toggle, so use the keyboard.
			await metaBoxesToggle.focus();
			await page.keyboard.press( 'Enter' );
			await expect( metaBoxesToggle ).toHaveAttribute( 'aria-expanded', 'true' );
		}

		await metaBox.scrollIntoViewIfNeeded();
		await metaBox.locator( '#jobpress_vacancy' ).fill( '3' );
		await metaBox.locator( '#jobpress_experience' ).fill( '2+ years' );
		await metaBox.locator( '#jobpress_working_hour' ).fill( '9am - 5pm' );
		await metaBox.locator( '#jobpress_working_days' ).fill( 'Mon - Fri' );
		await metaBox.locator( '#jobpress_salary' ).fill( 'Negotiable' );
		await metaBox.locator( '#jobpress_apply_deadline' ).fill( '2030-12-31' );
		await metaBox.locator( '#jobpress_location' ).fill( 'Dhaka, Bangladesh' );
		await metaBox.locator( '#application_by_email' ).check();
		await metaBox.locator( '#jobpress_email' ).fill( 'Send your CV to jobs@example.test' );

		await editor.publishPost();

		const job = ( await page.evaluate( () =>
			window.wp.data.select( 'core/editor' ).getCurrentPost()
		) );
		await page.goto( job.link );

		await expect( page.getByRole( 'heading', { name: 'Job Summary' } ) ).toBeVisible();
		const summary = page.locator( '.jp-summary' );
		for ( const value of [ '2+ years', '9am - 5pm', 'Mon - Fri', 'Negotiable', 'Dhaka, Bangladesh' ] ) {
			await expect( summary ).toContainText( value );
		}

		// The deadline uses the site's date format instead of the raw Y-m-d value.
		await expect( summary ).toContainText( '2030' );
		await expect( summary ).not.toContainText( '2030-12-31' );

		const applySection = page.locator( '#job-apply' );
		await expect( applySection.getByRole( 'heading' ) ).toContainText( title );
		await expect( applySection ).toContainText( 'Send your CV to jobs@example.test' );

		await expect( page.locator( '.jp-container' ) ).toHaveCount( 1 );
		await expectNoPhpErrors( page );
	} );
} );
