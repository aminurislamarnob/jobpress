/**
 * Playwright config for JobPress end-to-end tests.
 *
 * Runs against any WordPress site with JobPress active. Configure it with
 * tests/e2e/.env (see .env.example) or environment variables:
 *   WP_BASE_URL, WP_USERNAME, WP_PASSWORD
 * Without them it targets wp-env's default (http://localhost:8889, admin/password).
 */
const path = require( 'path' );
const { defineConfig, devices } = require( '@playwright/test' );

// Must run before @wordpress/e2e-test-utils-playwright is loaded, since it reads
// WP_BASE_URL / WP_USERNAME / WP_PASSWORD at require time.
try {
	process.loadEnvFile( path.join( __dirname, '.env' ) );
} catch ( error ) {
	// No .env file: fall back to the environment / wp-env defaults.
}

const artifactsPath = path.join( __dirname, 'artifacts' );
process.env.WP_BASE_URL ??= 'http://localhost:8889';
process.env.STORAGE_STATE_PATH ??= path.join(
	artifactsPath,
	'storage-states/admin.json'
);

module.exports = defineConfig( {
	testDir: './specs',
	outputDir: path.join( artifactsPath, 'test-results' ),
	globalSetup: require.resolve( './global-setup.js' ),
	// Tests change shared site options (design, colors, jobs per page), so run serially.
	fullyParallel: false,
	workers: 1,
	forbidOnly: !! process.env.CI,
	retries: process.env.CI ? 1 : 0,
	timeout: 60_000,
	expect: { timeout: 10_000 },
	reporter: [
		[ process.env.CI ? 'github' : 'list' ],
		[
			'html',
			{ outputFolder: path.join( artifactsPath, 'report' ), open: 'never' },
		],
	],
	use: {
		baseURL: process.env.WP_BASE_URL,
		storageState: process.env.STORAGE_STATE_PATH,
		trace: 'retain-on-failure',
		screenshot: 'only-on-failure',
	},
	projects: [
		{
			name: 'chromium',
			use: { ...devices[ 'Desktop Chrome' ] },
		},
	],
} );
