---
name: publish-docs
description: Publish the JobPress user docs (docs/) to pluginizelab.com/docs/jobpress after a release. Use when releasing a new JobPress version, after the release tag is pushed, or when asked to publish, sync or deploy the docs to pluginizelab.com.
---

# Publish the docs to pluginizelab.com

The user docs live in this repo, laid out exactly as the site serves them:

- `docs/content/docs/jobpress/*.mdx` plus `meta.json` (sidebar `title`, page order, `sourceUrl`)
- `docs/public/docs/jobpress/images/*`, referenced as `/docs/jobpress/images/<name>`
- links between pages are `/docs/jobpress/<page>`

`publish.sh` (next to this file) mirrors those two folders into the pluginizelab.com repo, stamps `version` into the site's copy of `meta.json`, runs the site build, then commits and pushes to the site's `main` branch, which Cloudflare Pages deploys. It does no rewriting, so the source must already be in that layout.

## Steps

1. Run `.claude/skills/publish-docs/publish.sh` from the repo root. It refuses unless HEAD is tagged `v<JOBPRESS_VERSION>`, because docs from any other commit can describe features that haven't shipped.
2. If it stopped:
   - **Not a release commit.** Tell the user, and offer to check out the tag (`git checkout v<version>`, then switch back afterwards). Run with `--force` only when the user confirms that they want to publish docs from this commit anyway, typically a doc-only fix between releases. A forced publish keeps the version that's already live.
   - **Links outside `/docs/jobpress`.** Usually a page written by the `plugin-docs` skill, which defaults to bare `/docs/...` paths. Rewrite them to `/docs/jobpress/...`, move any images into `docs/public/docs/jobpress/images/`, commit, and run again. On a release tag, that fix needs the user's go-ahead to publish with `--force`.
   - **Missing images, a dirty tree, the site repo not on a clean `main`, or the build failed.** Report the script's message. Don't clean or reset the site repo yourself: anything uncommitted there is someone's work.
3. Report the pushed commit and https://pluginizelab.com/docs/jobpress.

`--dry-run` stops before committing: the changes stay in the site repo, where `pnpm preview` shows them.

The site repo defaults to `../../../../pluginizelab.com` from the plugin. On another machine, set `PLUGINIZELAB_SITE_PATH`.
