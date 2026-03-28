# Hate Speech Detector — WordPress Plugin

A WordPress plugin that automatically screens blog comments for hate speech using the [NLP Hate Speech Detection API](https://github.com/KishanHapani/NLP-Project) before they are published.

[![CI](https://github.com/KishanHapani/hate-speech-detector-wp/actions/workflows/ci.yml/badge.svg)](https://github.com/KishanHapani/hate-speech-detector-wp/actions/workflows/ci.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![WordPress](https://img.shields.io/badge/WordPress-5.6%2B-blue)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple)](https://php.net)

---

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [How it works](#how-it-works)
- [Development](#development)
- [Changelog](#changelog)
- [License](#license)

---

## Features

- 🔍 Sends every new blog comment to the NLP `/predict` endpoint before it is saved.
- 🚫 Automatically **holds**, **trashes**, or **marks as spam** comments classified as hate speech.
- 🎚️ Configurable **confidence threshold** (0.0 – 1.0) to tune sensitivity.
- 📧 Optional **admin email notification** when a comment is flagged.
- ✅ Fails **open** — if the API is unreachable, comments pass through normally.
- 🔒 Uses WordPress's built-in HTTP API (`wp_remote_post`) — no extra libraries required.

---

## Requirements

| Component | Minimum version |
|---|---|
| WordPress | 5.6 |
| PHP | 7.4 |
| NLP Hate Speech Detection API | running instance of [KishanHapani/NLP-Project](https://github.com/KishanHapani/NLP-Project) |

---

## Installation

### From source (recommended for development)

1. Start the NLP API:

   ```bash
   # In KishanHapani/NLP-Project
   conda activate hate
   python app.py          # starts FastAPI on port 8080 by default
   ```

2. Copy (or symlink) this repository into your WordPress plugins folder:

   ```bash
   cp -r hate-speech-detector  /path/to/wordpress/wp-content/plugins/
   # or
   ln -s "$(pwd)" /path/to/wordpress/wp-content/plugins/hate-speech-detector
   ```

3. In the WordPress admin dashboard navigate to **Plugins → Installed Plugins** and activate **Hate Speech Detector**.

4. Go to **Settings → Hate Speech Detector** and configure the plugin (see [Configuration](#configuration)).

### Via WordPress Admin upload

1. Zip the plugin directory:

   ```bash
   zip -r hate-speech-detector.zip hate-speech-detector/
   ```

2. In WordPress admin go to **Plugins → Add New → Upload Plugin**, choose the zip file and click **Install Now**.

3. Activate the plugin and configure it as above.

---

## Configuration

Navigate to **Settings → Hate Speech Detector** after activating the plugin.

| Setting | Default | Description |
|---|---|---|
| **API Base URL** | `http://localhost:8080` | Base URL of the running FastAPI hate-speech detection service. |
| **Confidence Threshold** | `0.5` | Minimum confidence score (0.0 – 1.0) to flag a comment. Lower = more sensitive. |
| **Action on Flagged Comments** | Hold for moderation | Choose between *Hold*, *Trash*, or *Mark as spam*. |
| **Email Notifications** | Enabled | Send an email to the site administrator when a comment is flagged. |

---

## How it works

```
New comment submitted
        │
        ▼
pre_comment_approved filter (priority 99)
        │
        ▼
POST /predict  ──► NLP API (FastAPI)
        │
   ┌────┴────┐
   │ hate?   │
   └────┬────┘
        │ yes
        ▼
Hold / Trash / Spam  +  optional admin email
        │ no / API down
        ▼
Comment approved normally
```

The plugin hooks into WordPress's `pre_comment_approved` filter. For each new comment it calls the `/predict` endpoint with the comment text. If the API returns a `hate` label with a confidence score at or above the configured threshold, the comment is routed to the configured action. If the API is unreachable, the comment is allowed through (fail-open policy).

---

## Development

### Prerequisites

- PHP ≥ 7.4
- [Composer](https://getcomposer.org/) (optional, for dev tooling)
- A local WordPress installation (e.g. [Local](https://localwp.com/) or [wp-env](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/))

### Coding standards

The plugin follows the [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/). All functions are prefixed with `hsd_` to avoid naming conflicts.

### PHP syntax check

```bash
find . -name "*.php" | xargs -I{} php -l {}
```

### Running the CI locally

Install [act](https://github.com/nektos/act) and run:

```bash
act -j lint
```

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

---

## License

[MIT](LICENSE) © KishanHapani
