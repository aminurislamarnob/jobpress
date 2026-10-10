/**
 * The JobPress settings screens (General, Appearance, Shortcodes) on small screens.
 */
const { test, expect } = require( '../fixtures' );

test.describe( 'Settings screens', () => {
	for ( const screen of [ 'general', 'appearance', 'shortcode' ] ) {
		test( `${ screen } fits a phone screen`, async ( { page, jobPress } ) => {
			await page.setViewportSize( { width: 375, height: 800 } );
			await jobPress.visitSettings( screen );

			const layout = await page.evaluate( () => ( {
				pageWidth: document.documentElement.scrollWidth,
				viewportWidth: document.documentElement.clientWidth,
				contentWidth: document.querySelector( '.jobpress-right-content' ).getBoundingClientRect().width,
			} ) );
			expect( layout.pageWidth ).toBeLessThanOrEqual( layout.viewportWidth );
			// The navigation sits above the content instead of squeezing it.
			expect( layout.contentWidth ).toBeGreaterThan( 300 );
		} );
	}
} );
