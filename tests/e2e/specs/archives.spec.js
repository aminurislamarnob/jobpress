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
		await expect( page.locator( '#primary' ) ).toHaveCount( 1 );
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
