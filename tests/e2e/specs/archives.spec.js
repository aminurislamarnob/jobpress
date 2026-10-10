/**
 * Job category and job type archives (/jobpress_category/{slug}, /jobpress_type/{slug}).
 */
const { test, expect, uid, expectNoPhpErrors } = require( '../fixtures' );

test.describe( 'Taxonomy archives', () => {
	let category, type, categorizedJob, typedJob, otherJob;

	test.beforeEach( async ( { jobPress } ) => {
		const token = uid();
		category = await jobPress.createTerm( 'jobpress_category', `Marketing ${ token }` );
		type = await jobPress.createTerm( 'jobpress_type', `Contract ${ token }` );
		categorizedJob = await jobPress.createJob( {
			title: `Content Writer ${ token }`,
			categories: [ category.id ],
		} );
		typedJob = await jobPress.createJob( {
			title: `Contract Tester ${ token }`,
			types: [ type.id ],
		} );
		otherJob = await jobPress.createJob( { title: `Unrelated Role ${ token }` } );
	} );

	test( 'category archive lists only jobs in that category', async ( { page } ) => {
		await page.goto( category.link );

		await expect( page.getByRole( 'heading', { level: 1, name: category.name } ) ).toBeVisible();
		await expect( page.getByRole( 'link', { name: categorizedJob.title.rendered } ) ).toBeVisible();
		await expect( page.getByRole( 'link', { name: otherJob.title.rendered } ) ).toHaveCount( 0 );
		await expect( page.locator( '.jp-container' ) ).toHaveCount( 1 );
		await expectNoPhpErrors( page );
	} );

	test( 'category archive preselects its category in the filter', async ( { page } ) => {
		await page.goto( category.link );

		await expect( page.locator( '#jobpress-category' ) ).toHaveValue( category.slug );
		await expect( page.locator( '#jobpress-type' ) ).toHaveValue( '' );
	} );

	test( 'category archive paginates by the jobs per page setting', async ( { page, jobPress } ) => {
		// One job per page is below the Reading setting, which used to make /page/2/ 404.
		await jobPress.updateSettings( 'general', { jobpress_jobs_per_page: '1' } );
		const secondJob = await jobPress.createJob( {
			title: `Second Writer ${ uid() }`,
			categories: [ category.id ],
		} );

		await page.goto( category.link );
		await expect( page.getByRole( 'link', { name: secondJob.title.rendered } ) ).toBeVisible();

		await page.locator( '.jobpress-pagination a.page-numbers', { hasText: /^2$/ } ).click();
		await expect( page.getByRole( 'link', { name: categorizedJob.title.rendered } ) ).toBeVisible();
		await expect( page.getByRole( 'link', { name: secondJob.title.rendered } ) ).toHaveCount( 0 );
		await expectNoPhpErrors( page );
	} );

	test( 'type archive lists only jobs of that type', async ( { page } ) => {
		await page.goto( type.link );

		await expect( page.getByRole( 'heading', { level: 1, name: type.name } ) ).toBeVisible();
		await expect( page.getByRole( 'link', { name: typedJob.title.rendered } ) ).toBeVisible();
		await expect( page.getByRole( 'link', { name: otherJob.title.rendered } ) ).toHaveCount( 0 );
		await expectNoPhpErrors( page );
	} );
} );
