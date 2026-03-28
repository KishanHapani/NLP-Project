# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2024-03-28

### Added
- Initial release.
- Hook into `pre_comment_approved` (priority 99) to screen every new WordPress comment.
- POST comment text to the NLP FastAPI `/predict` endpoint.
- Configurable actions for flagged comments: hold for moderation, move to trash, or mark as spam.
- Configurable confidence threshold (0.0 – 1.0).
- Optional admin email notification when a comment is flagged.
- Fail-open behaviour — comments are allowed through when the API is unreachable.
- Admin settings page under **Settings → Hate Speech Detector**.
- Clean uninstall hook that removes all plugin options from the database.
