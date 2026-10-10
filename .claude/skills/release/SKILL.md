---
name: release
description: Release a new JobPress version to WordPress.org, or push a readme/banner-only update without a release. Use when asked to release, ship or tag a version, bump the version, or update the WordPress.org readme or assets.
---

# Release JobPress

JobPress reaches WordPress.org through two GitHub workflows, one per branch of this skill:

- **Release**: pushing a tag `vX.Y.Z` runs "Deploy to WordPress.org" (`10up/action-wordpress-plugin-deploy`). It commits the repo, minus `.distignore`, to SVN `trunk` and `tags/X.Y.Z`.
- **Readme/assets only**: pushing to the `trunk` branch runs "Plugin asset/readme update" (`10up/action-wordpress-plugin-asset-update`). It updates only `readme.txt` and `.wordpress-org/` (banners, icons, screenshots), with no new version.

Both authenticate with the `SVN_USERNAME` / `SVN_PASSWORD` repo secrets.

`develop` is the default branch and is protected: a PR needs a code owner's approval. The user may say to merge as admin (`gh pr merge <n> --admin --merge`). Do that only when they say so for this PR. `--match-head-commit` needs the full SHA (`git rev-parse <short>`).

## Release X.Y.Z

1. **Prepare on a branch off `develop`.**
   - Bump the version in all three places: the `Version:` header and `JOBPRESS_VERSION` in `jobpress.php`, and `Stable tag:` in `readme.txt`.
   - Add a `readme.txt` changelog entry headed `= vX.Y.Z (Mon D, YYYY)  =`, with `**feat:**` / `**fix:**` bullets written for site owners.
   - Raise `Tested up to:` if a newer WordPress was tested.
   - Regenerate the POT with `wp i18n make-pot . languages/jobpress.pot`.
   - Rebuild and commit `assets/blocks/` (`npm run build:blocks`) if `src/blocks/` changed since the last tag.

   This step is done when `git diff v<previous>..HEAD` shows every user-facing change in the changelog, and all three version strings match.
2. **PR to `develop`.** CI runs the "Block build is up to date" check and Playwright on twentytwentyone and twentytwentyfive, about 7 minutes. Merge when it's green and approved, or as admin if the user says so.
3. **Tag.** Confirm with the user first: this publishes the release.
   - Run `git checkout develop && git pull --ff-only`, then `git tag vX.Y.Z` and `git push origin vX.Y.Z`. Tags are lightweight, like the existing ones.
   - Only users with write/admin permission may push tags. The "Restrict Tag Creation to Owners" workflow checks this.
4. **Verify the deploy.** Run `gh run watch` on the "Deploy to WordPress.org" run, then confirm both:
   - `curl -s https://api.wordpress.org/plugins/info/1.0/jobpress.json` reports `"version":"X.Y.Z"`.
   - `https://downloads.wordpress.org/plugin/jobpress.X.Y.Z.zip` returns 200.

   If the run fails with `svn: E215004: Authentication failed`, the SVN password has changed. Ask the user to update the secret with `! gh secret set SVN_PASSWORD -R aminurislamarnob/jobpress`, then run `gh run rerun <id> --failed`. The tag stays as it is.
5. **Publish the docs.** With the tagged commit checked out, run the `publish-docs` skill. It updates https://pluginizelab.com/docs/jobpress to vX.Y.Z.

## Readme/assets-only update

1. Change only `readme.txt` and/or `.wordpress-org/`, PR to `develop`, and merge.
2. Run `git diff --stat v<latest tag> origin/develop`. It must list only those paths. The asset workflow deploys nothing when other files changed, and a code change belongs in a release.
3. Run `git push origin origin/develop:refs/heads/trunk`. Then watch the "Plugin asset/readme update" run, which ends with `Committed revision N`. Check https://plugins.svn.wordpress.org/jobpress/tags/<latest>/readme.txt for the change.
