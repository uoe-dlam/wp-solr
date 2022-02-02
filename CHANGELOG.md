# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Fixed
- Move re-indexing to the CLI to avoid PHP timeouts and update solarium version.  (PR #14)

### Changed
- Updated the plugin link to point to GitHub instead of GitLab (PR #11)

## [1.0.3]
### Fixed
- Changed Solarium client to use post instead of get to stop URI Too Long errors (PR #10)

## [1.0.2] - 2020-05-07
### Fixed
- When indexing the full site, trashed blogs are no longer included in the search index (PR #9)

## [1.0.1] - 2020-04-30
### Fixed
- Trashed blog posts are now deleted from the search results (PR #8)

### Security
- Updating dependencies to fix vulnerabilities in symfony/cache and symfony/var-exporter (PR #7)

## [1.0.0] - 2020-04-29
### Added
- Initial release

[Unreleased]: https://github.com/uoe-dlam/wp-solr/compare/v1.0.1...HEAD
[1.0.1]: https://github.com/uoe-dlam/wp-solr/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/uoe-dlam/wp-solr/releases/tag/v1.0.0
