/**
 * Hardening: job details people with lower roles can edit, and plugin files
 * opened directly.
 */
const { test, expect, uid, expectNoPhpErrors } = require( '../fixtures' );

const GOOGLE_MAP = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1!2d90.4!3d23.8';

test.describe( 'Security', () => {
	test( 'job pages only show Google Maps embeds', async ( { page, jobPress } ) => {
		const job = await jobPress.createJob( { title: `Map Job ${ uid() }` } );
		const map = page.locator( '.jp-job-location-googlemap iframe' );

		await jobPress.setJobDetails( job, {
			google_map_iframe: `<iframe src="${ GOOGLE_MAP }" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>`,
		} );
		await page.goto( job.link );
		await expect( map ).toHaveAttribute( 'src', GOOGLE_MAP );
		// The iframe is rebuilt, so only its URL is kept from what was entered.
		await expect( map ).not.toHaveAttribute( 'style', /./ );
		await expectNoPhpErrors( page );

		// A full-page iframe of another site, e.g. a fake login page.
		await jobPress.setJobDetails( job, {
			google_map_iframe:
				'<iframe src="https://evil.example/login" style="position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:99999"></iframe>',
		} );
		await page.goto( job.link );
		await expect( page.locator( '.jp-single-wrapper' ) ).toBeVisible();
		await expect( page.locator( '.jp-single-wrapper iframe' ) ).toHaveCount( 0 );
		await expect( page.locator( '.jp-job-location-googlemap' ) ).toHaveCount( 0 );
	} );

	test( 'plugin files print nothing when opened directly', async ( { page, jobPress } ) => {
		const listingPage = await jobPress.createPage( `Direct ${ uid() }`, '[jobpress]' );
		await page.goto( listingPage.link );
		const stylesheet = await page.locator( 'link#jobpress-common-css' ).getAttribute( 'href' );
		const pluginUrl = stylesheet.slice( 0, stylesheet.indexOf( 'assets/' ) );

		for ( const file of [
			'templates/single-jobpress.php',
			'templates/archive-jobpress.php',
			'templates/task-pages/settings.php',
			'templates/task-pages/appearance-settings.php',
			'templates/task-pages/shortcodes-settings.php',
			'templates/task-pages/nav.php',
		] ) {
			const response = await page.request.get( pluginUrl + file );
			// Without the ABSPATH check, PHP errors would show the server path.
			expect( await response.text(), file ).toBe( '' );
		}
	} );
} );
