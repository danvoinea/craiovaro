### A1. Local News Scraper

**Purpose**
Fetch local news articles so craiova.ro can aggregate them, group them into topics, summarize them, and build the daily newsletter.

**Key Features**

* Admin-configurable sources:
  * Source name
  * URL / RSS / sitemap
  * CSS or XPath selectors for title/body/date/image
  * Fetch frequency (cron expression or preset: 5m / 15m / hourly / daily)
  * Active / inactive flag
  * Start with the following websites as sources for news: https://gds.ro, https://cvlpress.ro, https://stiricraiova.ro, https://dj.politiaromana.ro/ro/stiri-si-media/stiri, https://jurnalulolteniei.ro/, https://www.editie.ro/, https://tvr-craiova.ro/, https://jurnaldecraiova.ro/, https://www.craiovaforum.ro
  * Keywords to search, separated by comma (this way, we can also add national news websites and filter by local issues)
* Scheduled cron jobs:
  * Fetch new articles
  * Extract data:
    * `title`
    * `body_html` (raw HTML)
    * `body_text` (cleaned text)
    * `published_at` (original source timestamp)
    * `source_name`
    * `source_url` (canonical link to original article)
    * `cover_image_url` (if available)
  * Deduplicate by URL hash
  * Save record into `news_raw`
* Logging:
  * Log last successful fetch per source
  * Log errors (to admin dashboard)

**Legal / attribution**
* Store `source_name` + `source_url` for each article.
* Frontend must clearly attribute; no pretending content is ours.

**Input**
* List of configured sources from admin.

**Output**
* Rows in `news_raw` (fresh articles)

**Dependencies**
* Core Platform for DB, cron, admin auth.
