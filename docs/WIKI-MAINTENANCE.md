# Wiki maintenance

The Markdown under `docs/wiki` is the reviewable source for the public GitHub Wiki. Do not edit the public wiki and this directory independently.

## Source order

Resolve documentation claims in this order:

1. Behavior observed on the exact module commit in a representative Mage-OS or Magento Open Source runtime
2. Current module code, configuration, tests, and acceptance plan
3. Current official Magento, Mage-OS, Google, and GitHub documentation
4. Historical Rocket Web documentation as migration source material only

## Page convention

Every user-facing page should contain:

* One clear purpose
* A documentation baseline and review date
* Prerequisites or safety boundaries where relevant
* Exact Admin paths, commands, and setting names
* A verification step
* Links to related pages instead of duplicated procedures

Use `_Sidebar.md` for navigation and `_Footer.md` for repository links.

Use `docs/WIKI-CONTENT-MAP.md` to confirm that historical subjects have a current destination or an explicit reason for exclusion.

## Screenshots

Add a screenshot only when it clarifies a stable module-owned interface. Capture it from the exact release candidate, crop it to the relevant control, add useful alt text, and remove store names, domains, credentials, customer data, product data, and internal paths.

Prefer text for third-party interfaces such as Google Merchant Center because their navigation changes independently of this module. Store image source files alongside the reviewable documentation rather than only in the public wiki checkout.

## Publication workflow

1. Update the pages in `docs/wiki` on a review branch.
2. Run `php dev/tests/validate-wiki.php`.
3. Review the rendered Markdown and any new screenshots.
4. Confirm the baseline commit named on every page resolves in the public repository and matches the reviewed source tree.
5. Merge the reviewed source change into the module repository.
6. If the Wiki feature is unavailable, obtain approval to enable it in repository settings.
7. Initialize the GitHub Wiki with `Home.md` if it does not exist.
8. Clone `https://github.com/mage-os-lab/module-shopping-feed.wiki.git`.
9. Copy the reviewed page set into the wiki checkout.
10. Review the wiki diff and commit it separately.
11. Obtain approval for the exact wiki commit and destination.
12. Push the wiki default branch, then verify the live Home page, sidebar, footer, links, and images.

Publishing wiki content does not imply permission to commit or push changes to the module repository, and a module push does not imply permission to publish the wiki.

## Drift checks

Review the wiki for every release that changes:

* Composer or platform requirements
* Admin paths, tabs, fields, or ACL resources
* Feed types, directives, or default columns
* Output files, logs, locks, commands, or cron behavior
* Upload modes or credential handling
* Google data specifications or storefront event formats
* Migration behavior

At minimum, recheck Google-owned links and terminology at release time. Keep Merchant Center navigation brief because its interface changes independently of this module.
