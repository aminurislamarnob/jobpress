#!/usr/bin/env bash
# Publishes the JobPress user docs (docs/) to pluginizelab.com/docs/jobpress:
# mirrors them into the pluginizelab.com repo, stamps the version, builds the
# site, then commits and pushes to its main branch (Cloudflare Pages deploys it).
#
#   publish.sh             publish from a release commit (HEAD tagged v<JOBPRESS_VERSION>)
#   publish.sh --force     publish from any commit; keeps the version already live
#   publish.sh --dry-run   do everything except commit and push (changes stay in
#                          the site repo for a look; undo with `git checkout -- . && git clean -fd`)
#
# The site repo is $PLUGINIZELAB_SITE_PATH, else ../../../../pluginizelab.com
# relative to the plugin (the Sites folder that holds the WordPress install).
set -euo pipefail

SLUG=jobpress
FORCE=0
DRY_RUN=0
for arg in "$@"; do
	case "$arg" in
		--force) FORCE=1 ;;
		--dry-run) DRY_RUN=1 ;;
		*) echo "Unknown option: $arg" >&2; exit 2 ;;
	esac
done

fail() { echo "✗ $*" >&2; exit 1; }

PLUGIN_DIR=$(git -C "$(dirname "$0")" rev-parse --show-toplevel)
SRC_CONTENT="$PLUGIN_DIR/docs/content/docs/$SLUG"
SRC_IMAGES="$PLUGIN_DIR/docs/public/docs/$SLUG/images"
[ -f "$SRC_CONTENT/meta.json" ] || fail "No docs at docs/content/docs/$SLUG/meta.json."

# 1. Which release this is. Only a commit tagged v<JOBPRESS_VERSION> is a
#    release; anything else could carry docs for features that haven't shipped.
VERSION=$(sed -nE "s/.*define\( *'JOBPRESS_VERSION', *'([^']+)'.*/\1/p" "$PLUGIN_DIR/jobpress.php")
[ -n "$VERSION" ] || fail "Couldn't read JOBPRESS_VERSION from jobpress.php."
RELEASE=1
if ! git -C "$PLUGIN_DIR" tag --points-at HEAD | grep -qx "v$VERSION"; then
	RELEASE=0
	[ "$FORCE" = 1 ] || fail "HEAD isn't tagged v$VERSION, so it isn't a release. Check out the release tag, or pass --force to publish these docs anyway."
	echo "! HEAD isn't tagged v$VERSION; publishing anyway (--force), keeping the version that's live."
fi
[ -z "$(git -C "$PLUGIN_DIR" status --porcelain -- docs)" ] \
	|| fail "docs/ has uncommitted changes. Commit them first, so what's published is in the repo."

# 2. Links and images. The site serves these files as they are, so every link
#    must already use the /docs/$SLUG/ namespace (the plugin-docs skill writes
#    bare /docs/... paths) and every screenshot must exist.
BAD_LINKS=$(grep -rnoE "(\]\(|(href|src)=\")/docs(/[^)\"]*)?" "$SRC_CONTENT" --include='*.mdx' \
	| grep -vE "(\]\(|=\")/docs/$SLUG([/#)\"]|$)" || true)
[ -z "$BAD_LINKS" ] || fail "Links outside /docs/$SLUG (they'd point at another plugin's docs, or nowhere):
$BAD_LINKS"
MISSING=""
while IFS= read -r img; do
	[ -f "$PLUGIN_DIR/docs/public$img" ] || MISSING+="$img"$'\n'
done < <(grep -rhoE "/docs/$SLUG/images/[^)\" ]+" "$SRC_CONTENT" --include='*.mdx' | sort -u)
[ -z "$MISSING" ] || fail "Images referenced but missing from docs/public:
$MISSING"

# 3. The site repo: clean, on main, up to date.
SITE_DIR=${PLUGINIZELAB_SITE_PATH:-"$PLUGIN_DIR/../../../../pluginizelab.com"}
[ -d "$SITE_DIR/content/docs" ] || fail "pluginizelab.com repo not found at $SITE_DIR. Set PLUGINIZELAB_SITE_PATH to its checkout."
SITE_DIR=$(cd "$SITE_DIR" && pwd)
[ "$(git -C "$SITE_DIR" branch --show-current)" = main ] || fail "$SITE_DIR isn't on main."
[ -z "$(git -C "$SITE_DIR" status --porcelain)" ] || fail "$SITE_DIR has uncommitted changes."
git -C "$SITE_DIR" pull --ff-only --quiet || fail "Couldn't fast-forward $SITE_DIR to origin/main."

# 4. Mirror. --delete drops pages and images removed here; it's confined to
#    this plugin's two folders, so other plugins' docs are never touched.
LIVE_META="$SITE_DIR/content/docs/$SLUG/meta.json"
LIVE_VERSION=""
[ -f "$LIVE_META" ] && LIVE_VERSION=$(node -p "require('$LIVE_META').version ?? ''")
mkdir -p "$SITE_DIR/content/docs/$SLUG" "$SITE_DIR/public/docs/$SLUG/images"
rsync -a --delete --exclude .DS_Store "$SRC_CONTENT/" "$SITE_DIR/content/docs/$SLUG/"
rsync -a --delete --exclude .DS_Store "$SRC_IMAGES/" "$SITE_DIR/public/docs/$SLUG/images/"

# 5. Stamp the version the docs describe. A forced publish isn't a release, so
#    it keeps whatever version was live (none, before the first release).
STAMP=$([ "$RELEASE" = 1 ] && echo "$VERSION" || echo "$LIVE_VERSION")
node -e '
	const fs = require("fs");
	const [file, version] = process.argv.slice(1);
	const { title, version: _, ...rest } = JSON.parse(fs.readFileSync(file, "utf8"));
	const meta = version ? { title, version, ...rest } : { title, ...rest };
	fs.writeFileSync(file, JSON.stringify(meta, null, 2) + "\n");
' "$LIVE_META" "$STAMP"

if [ -z "$(git -C "$SITE_DIR" status --porcelain)" ]; then
	echo "✓ Nothing to publish: pluginizelab.com already has these docs."
	exit 0
fi
git -C "$SITE_DIR" status --short

# 6. Build, the same command Cloudflare Pages runs, so a broken page fails here
#    rather than in production.
command -v pnpm >/dev/null && PNPM=(pnpm) || PNPM=(npx -y pnpm@10)
(
	cd "$SITE_DIR"
	[ -d node_modules ] || "${PNPM[@]}" install --frozen-lockfile
	"${PNPM[@]}" build >/dev/null
) || fail "The site build failed; the changes are left in $SITE_DIR. Run \`pnpm build\` there for the error."
echo "✓ Site builds."

if [ "$DRY_RUN" = 1 ]; then
	echo "✓ Dry run: changes left uncommitted in $SITE_DIR (preview with \`pnpm preview\` there)."
	exit 0
fi

# 7. Ship.
if [ "$RELEASE" = 1 ]; then
	MESSAGE="docs($SLUG): publish v$VERSION"
else
	MESSAGE="docs($SLUG): update docs from $(git -C "$PLUGIN_DIR" rev-parse --short HEAD)"
fi
git -C "$SITE_DIR" add "content/docs/$SLUG" "public/docs/$SLUG"
git -C "$SITE_DIR" commit --quiet -m "$MESSAGE"
git -C "$SITE_DIR" push --quiet origin main
echo "✓ Pushed \"$MESSAGE\" ($(git -C "$SITE_DIR" rev-parse --short HEAD)) to pluginizelab.com main."
echo "  Live at https://pluginizelab.com/docs/$SLUG once Cloudflare Pages finishes deploying."
