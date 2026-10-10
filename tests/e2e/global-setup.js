/**
 * Logs in once as the admin user and stores the auth cookies + REST nonce
 * so every test starts authenticated.
 */
const { request } = require( '@playwright/test' );
const { RequestUtils } = require( '@wordpress/e2e-test-utils-playwright' );

module.exports = async function globalSetup( config ) {
	const { storageState, baseURL } = config.projects[ 0 ].use;
	const requestContext = await request.newContext( { baseURL } );
	const requestUtils = new RequestUtils( requestContext, {
		storageStatePath: storageState,
	} );

	await requestUtils.setupRest();

	// E2E_THEME=<slug> runs the suite against a specific theme, e.g. a classic
	// theme (twentytwentyone) or a block theme (twentytwentyfive). The theme must
	// already be installed.
	if ( process.env.E2E_THEME ) {
		await requestUtils.activateTheme( process.env.E2E_THEME );
	}

	await requestContext.dispose();
};
