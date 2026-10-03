#!/usr/bin/env python3
"""Dependency-free consistency checks for the plugin release files."""

from pathlib import Path
import hashlib
import re
import sys

ROOT = Path(__file__).resolve().parents[1]
plugin = (ROOT / "tsar-hotel-updates.php").read_text(encoding="utf-8")
readme = (ROOT / "readme.txt").read_text(encoding="utf-8")
handover = (ROOT / "ADMIN-HANDOVER.md").read_text(encoding="utf-8")
report = (ROOT / "TEST-REPORT.md").read_text(encoding="utf-8")
css = (ROOT / "assets/front.css").read_text(encoding="utf-8")

checks = []


def check(condition, message):
    if not condition:
        raise AssertionError(message)
    checks.append(message)


header_version = re.search(r"^ \* Version: ([0-9.]+)$", plugin, re.MULTILINE)
code_version = re.search(r"const VERSION = '([0-9.]+)'", plugin)
readme_version = re.search(r"^Stable tag: ([0-9.]+)$", readme, re.MULTILINE)
report_version = re.search(r"^\*\*Version:\*\* ([0-9.]+)", report, re.MULTILINE)
handover_version = re.search(r"^\*\*Version:\*\* ([0-9.]+)", handover, re.MULTILINE)
redesign = (ROOT / "includes/class-homepage-redesign.php").read_text(encoding="utf-8")
redesign_version = re.search(r"const VERSION = '([0-9.]+)'", redesign)
check(
    all((header_version, code_version, readme_version, report_version, handover_version, redesign_version))
    and len({m.group(1) for m in (header_version, code_version, readme_version, report_version, handover_version, redesign_version)}) == 1,
    "release version is consistent across plugin source, redesign assets and documentation",
)
check(
    re.search(r"^Tested up to:", readme, re.MULTILINE) is None,
    "readme does not claim an unverified WordPress test version",
)

block = re.search(
    r"public static function defaults\(\)\s*\{\s*return array\((.*?)\n\t\t\);",
    plugin,
    re.DOTALL,
)
check(block is not None, "defaults array exists")
default_flags = dict(re.findall(r"'([a-z_]+)'\s*=>\s*([01])", block.group(1)))
enabled = {key for key, value in default_flags.items() if value == "1"}
check(enabled == {"menu_repair", "hide_empty_socials"}, "only the two documented repairs are enabled by default")
check(default_flags.get("mobile_layout_fixes") == "0", "mobile layout changes require explicit opt-in")
check(default_flags.get("redesign_enabled") == "0", "new homepage redesign is off until an administrator opts in")

redesign_css = (ROOT / "assets/redesign.css").read_text(encoding="utf-8")
redesign_js = (ROOT / "assets/redesign.js").read_text(encoding="utf-8")
check(
    "unset( $saved['khotel_design'] );" in plugin
    and "add_action( 'plugins_loaded', array( $this, 'migrate_legacy_settings' ), 1 );" in plugin
    and "$saved['redesign_enabled'] = 0;" in plugin
    and "$saved['language'] = 'en';" in plugin
    and "$output['language'] = 'en';" in plugin,
    "upgrade migration keeps the redesign disabled and forces the panel language to English",
)
check(
    not any(token in plugin + redesign for token in ("inject_khotel_sections", "render_khotel_footer_js", "tsar-kh-", "XAF ", "tel:+237", "wp_add_inline_style")),
    "unreviewed injected content, hard-coded rates/phone, and duplicate inline CSS are absent",
)
check(
    all(label in redesign for label in ("Rooms & Suites", "Restaurant", "Snack Lounge", "Events", "Gallery", "Contact"))
    and "array( 'label' =>" in redesign,
    "redesigned desktop and hamburger menus include all six requested destinations",
)
check(
    "'post_type'              => 'mphb_room_type'" in redesign
    and "'post_status'            => 'publish'" in redesign
    and "'posts_per_page'         => -1" in redesign
    and "'lang'                   => 'en'" in redesign
    and "'suppress_filters'       => false" in redesign
    and "get_permalink( $room_type )" in redesign,
    "room cards use only published English MotoPress room records and link to their detail pages",
)
check(
    not any(name in redesign for name in ("Studio Suite", "Standard Room", "Executive Suite", "Double Room", "Single Room")),
    "room categories are not hard-coded in the redesign",
)
check(
    "private function language()" in redesign
    and "return 'en';" in redesign
    and "private function t( $english )" in redesign
    and "render_language_switcher" not in redesign
    and not any(token in redesign for token in ("pll_the_languages", "wpml_active_languages", "trp_language_switcher", "?lang=")),
    "redesign copy is English-only and contains no language-switcher integration",
)
check(
    "'language' => 'en'" in plugin
    and "lang=\"en\"" in plugin
    and "Website language" in plugin
    and "English only" in plugin,
    "defaults, settings UI and contact panel use English",
)
check(
    all(token in redesign_css for token in (".wpml-ls", ".pll-parent-menu-item", "#trp-floater-ls", "#gtranslate_wrapper"))
    and "body.tsar-redesign-active" in redesign_css,
    "common third-party language-switcher controls are hidden only while the redesign is active",
)
check(
    "redesign_enabled" in plugin and "wp_body_open" in redesign and "render_floating_booking" in redesign
    and "page_url( 'reservations', '/reservations/' )" in redesign,
    "opt-in custom site header and floating CTA use the existing reservation page",
)
check(
    "tsar_hotel_offer" in redesign and "tsar_hotel_event" in redesign,
    "offers and events are editor-managed and absent until staff publish them",
)
check(
    "google_reviews_url" in redesign and "Read reviews on Google" in redesign and "Guest reviews" in redesign,
    "Google review content remains blank until an official link is configured",
)
check(
    "#masthead" in redesign_css and "prefers-reduced-motion" in redesign_css
    and "aria-expanded" in redesign_js and "Escape" in redesign_js,
    "custom layout includes header replacement, reduced-motion CSS, and keyboard-operable mobile navigation",
)
build_script = (ROOT / "build-zip.sh").read_text(encoding="utf-8")
check(
    all(path in build_script for path in ("class-homepage-redesign.php", "assets/redesign.css", "assets/redesign.js")),
    "ZIP builder includes the redesign PHP, stylesheet and interaction script",
)
check(
    "plugins_url( 'assets/front.css', __FILE__ )" in plugin
    and (ROOT / "assets/front.css").is_file()
    and "@import" not in css.lower()
    and "@import" not in redesign_css.lower(),
    "local stylesheets are used without external CSS imports",
)
check(css.count("{") == css.count("}"), "stylesheet braces are balanced")
check(
    "tsar-hotel-updates-v1.3.1.zip" in handover
    and "./build-zip.sh" in handover
    and "Stable tag: 1.3.1" in readme,
    "handover describes the buildable release archive",
)
check(
	"published `mphb_room_type`" in report
	and "English-only" in report
	and "no language switcher" in readme,
    "release docs describe MotoPress-only rooms and the English-only redesign",
)

hash_records = {filename: digest for digest, filename in re.findall(r"^([0-9a-f]{64})  (.+)$", report, re.MULTILINE)}
expected_hashes = {
    "tsar-hotel-updates.php": hashlib.sha256((ROOT / "tsar-hotel-updates.php").read_bytes()).hexdigest(),
    "uninstall.php": hashlib.sha256((ROOT / "uninstall.php").read_bytes()).hexdigest(),
    "assets/front.css": hashlib.sha256((ROOT / "assets/front.css").read_bytes()).hexdigest(),
    "includes/class-homepage-redesign.php": hashlib.sha256((ROOT / "includes/class-homepage-redesign.php").read_bytes()).hexdigest(),
    "assets/redesign.css": hashlib.sha256((ROOT / "assets/redesign.css").read_bytes()).hexdigest(),
    "assets/redesign.js": hashlib.sha256((ROOT / "assets/redesign.js").read_bytes()).hexdigest(),
}
check(
    all(hash_records.get(filename) == digest for filename, digest in expected_hashes.items()),
    "reported runtime SHA-256 hashes match the current files",
)

print(f"{len(checks)} release consistency checks passed:")
for message in checks:
    print(f"- {message}")

if __name__ == "__main__":
    sys.exit(0)
