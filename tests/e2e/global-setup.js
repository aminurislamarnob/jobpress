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
	await requestContext.dispose();
};
