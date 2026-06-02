# Changelog

All notable changes to this project will be documented in this file. See [commit-and-tag-version](https://github.com/absolute-version/commit-and-tag-version) for commit guidelines.

## [0.0.11](https://github.com/projek-xyz/wp-env/compare/v0.0.10...v0.0.11) (2026-06-02)


### Features

* **blank-option:** add plugin action links registration hook ([#37](https://github.com/projek-xyz/wp-env/issues/37)) ([062485d](https://github.com/projek-xyz/wp-env/commit/062485d47ded4a6e638209272e00cbd59fd30d06))
* **blank-option:** init `html-element` helper class ([#42](https://github.com/projek-xyz/wp-env/issues/42)) ([0a20966](https://github.com/projek-xyz/wp-env/commit/0a20966f0090b84b359eeddbe9268825b6852ccb))
* **blank-option:** init `updater` class ([#44](https://github.com/projek-xyz/wp-env/issues/44)) ([fc7dd6f](https://github.com/projek-xyz/wp-env/commit/fc7dd6f19a503f06bd4c413eb48c5dbe98b24179))
* **blank-option:** initialize new starter plugin ([#33](https://github.com/projek-xyz/wp-env/issues/33)) ([cec1d50](https://github.com/projek-xyz/wp-env/commit/cec1d507c7bd5a5041f212c9d71aaa1510e0690b))
* **init-wp:** add ability to skip media import & set option, also import media from `init-assets.txt` file ([#38](https://github.com/projek-xyz/wp-env/issues/38)) ([5965c23](https://github.com/projek-xyz/wp-env/commit/5965c236a13b7cc30aa0e823970b1503f18c4c04))
* **init-wp:** ensure `WP_RESET` work as expected, supports for literal `latest` version and WP 7.0 ([3241177](https://github.com/projek-xyz/wp-env/commit/324117710304ae3e8ffe0265861b03e86ba5c18a))
* **make-dist:** inject a blank `index.php` to while creating dist archive ([#36](https://github.com/projek-xyz/wp-env/issues/36)) ([ec92743](https://github.com/projek-xyz/wp-env/commit/ec927438698fc195e4dd144abed39b7fe45d364c))
* **test:** implement integration tests ([#45](https://github.com/projek-xyz/wp-env/issues/45)) ([87b486a](https://github.com/projek-xyz/wp-env/commit/87b486a2d1f5cf885c5bf6543a901a5a1bd75339))
* **tests:** add some utilities for base test case class ([#32](https://github.com/projek-xyz/wp-env/issues/32)) ([fd943e4](https://github.com/projek-xyz/wp-env/commit/fd943e4ffcc18929ffcda02abca9bb39137bbfeb))

## [0.0.10](https://github.com/projek-xyz/wp-env/compare/v0.0.9...v0.0.10) (2026-05-20)


### Features

* add ability to run `make-pot` and `make-dist` to individual package ([e7da2b4](https://github.com/projek-xyz/wp-env/commit/e7da2b4a7c43fba0bc5eadff7f16075d0864b48f))
* **ci:** init usage of `codecov` ([#28](https://github.com/projek-xyz/wp-env/issues/28)) ([4758bde](https://github.com/projek-xyz/wp-env/commit/4758bded8e6f6dcc8cdaa84f443dafc684318b5c))
* **ci:** make use of repo variables for `RELEASE_URL` ([08e2c30](https://github.com/projek-xyz/wp-env/commit/08e2c307ea98ba956c3d239616b23da646312d74))
* **ci:** update `dependabot` config ([4b899b5](https://github.com/projek-xyz/wp-env/commit/4b899b52a7bf124b4c909b18b5a4b16a5b968309))


### Bug Fixes

* **ci:** no need to "wrap" `curl` command here ([5ba303c](https://github.com/projek-xyz/wp-env/commit/5ba303c0a324cef9d591f6383e0f79b80a5268a4))
* **ci:** prevent failing when the asset not exists ([4ebc7a2](https://github.com/projek-xyz/wp-env/commit/4ebc7a2812a17d9eede31d194e60f0eb6daed61c))
* **ci:** release availability check should against `0` as it initial value ([a6de6e6](https://github.com/projek-xyz/wp-env/commit/a6de6e6fe5c90157b71652a1b213505dc2247e55))
* unbound `GITHUB_OUTPUT` variable when running `make-dist` locally ([683278b](https://github.com/projek-xyz/wp-env/commit/683278b75401f15a79fb4a69d3d6432f1131e64c))

## [0.0.9](https://github.com/projek-xyz/wp-env/compare/v0.0.8...v0.0.9) (2026-05-19)


### Features

* **ci:** early manifest check and skip entire setup when no new release available ([0d75eca](https://github.com/projek-xyz/wp-env/commit/0d75eca3b9481b53d5e5530b19d0010cd45d6b2c))


### Bug Fixes

* **ci:** incorrect `release-version` outputs from Archive job ([e51b0e5](https://github.com/projek-xyz/wp-env/commit/e51b0e5c835320393f6ce5efa257beaaa65a238e))
* **ci:** incorrect download-artifact `workflow.id` ([9bf8733](https://github.com/projek-xyz/wp-env/commit/9bf8733c44ec1a72fa1192d884cba5a8afe7eaa4))
* **ci:** make sure `dist.yml` workflow always has artifact ([17b0086](https://github.com/projek-xyz/wp-env/commit/17b0086185836f500c7f7cc7808098a95122a01f))
* **ci:** missing `v` in distribution upload job ([b958017](https://github.com/projek-xyz/wp-env/commit/b95801730a7b15771e6a674765465c457d896cd9))
* **ci:** optimized release distribution workflow ([#24](https://github.com/projek-xyz/wp-env/issues/24)) ([0ae0229](https://github.com/projek-xyz/wp-env/commit/0ae0229b440ea6e08defba7173aed80b15af0a6a))

## [0.0.8](https://github.com/projek-xyz/wp-env/compare/v0.0.7...v0.0.8) (2026-05-18)


### Features

* **ci:** init static assets deployment using `github-pages` ([#22](https://github.com/projek-xyz/wp-env/issues/22)) ([383ecfd](https://github.com/projek-xyz/wp-env/commit/383ecfded3ed54657be8353d3398ab72197812e7))
* **custom-theme:** add `Update URI` and streamlined distribution and release packaging ([#23](https://github.com/projek-xyz/wp-env/issues/23)) ([82a9811](https://github.com/projek-xyz/wp-env/commit/82a9811b842213155ce7b9a055218348e7673b4e))


### Bug Fixes

* **ci:** streamline a test suites ([5c806c5](https://github.com/projek-xyz/wp-env/commit/5c806c5fb5f275a0a73c2191944f58ea0c940d0d))
* **custom-theme:** fix theme uri ([adf3d76](https://github.com/projek-xyz/wp-env/commit/adf3d76d9e3fd8b427e6a54e4b34a0613397175a))
* missing the whole purpose of [#23](https://github.com/projek-xyz/wp-env/issues/23) ([c9a6d2f](https://github.com/projek-xyz/wp-env/commit/c9a6d2f3fa2efcff8c26d182af2e1772a7b5700e))

## [0.0.7](https://github.com/projek-xyz/wp-env/compare/v0.0.6...v0.0.7) (2026-05-17)


### Features

* make use of `projek-xyz/actions` uploads workflow ([cb80220](https://github.com/projek-xyz/wp-env/commit/cb8022001d5bd336eae55e862fc8bf462431b6b1))

## [0.0.6](https://github.com/projek-xyz/wp-env/compare/v0.0.5...v0.0.6) (2026-05-17)

## [0.0.5](https://github.com/projek-xyz/wp-env/compare/v0.0.4...v0.0.5) (2026-05-17)


### Bug Fixes

* **ci:** verify that release check is working ([a851dab](https://github.com/projek-xyz/wp-env/commit/a851dab3ad1287e68325c3d592f73d85ac5905bc))

## [0.0.4](https://github.com/projek-xyz/wp-env/compare/v0.0.3...v0.0.4) (2026-05-17)


### Bug Fixes

* **ci:** make sure `main` workflow always creates an artifact ([70c6751](https://github.com/projek-xyz/wp-env/commit/70c6751cd965255d27fa1d78a938787babc30a80))

## [0.0.3](https://github.com/projek-xyz/wp-env/compare/v0.0.2...v0.0.3) (2026-05-17)


### Bug Fixes

* **ci:** don't throw an error when there's no release available ([9f7486b](https://github.com/projek-xyz/wp-env/commit/9f7486b309810cd00ff573418ed84c6956b6715f))

## [0.0.2](https://github.com/projek-xyz/wp-env/compare/v0.0.1...v0.0.2) (2026-05-17)


### Features

* add ability to install more `plugins` and `themes` without activating them ([#14](https://github.com/projek-xyz/wp-env/issues/14)) ([4e2df49](https://github.com/projek-xyz/wp-env/commit/4e2df493132c5226c58a62c87d2d2f1c7cf3a275))
* **ci:** automate distribution through github releases ([#21](https://github.com/projek-xyz/wp-env/issues/21)) ([82ef0af](https://github.com/projek-xyz/wp-env/commit/82ef0af59bf75a81d254545c32f09ea6d4125ee9))
* **custom-theme:** rearrange `custom-theme` assets ([#13](https://github.com/projek-xyz/wp-env/issues/13)) ([6b08e07](https://github.com/projek-xyz/wp-env/commit/6b08e07233fed0db90a05edee9b85132e5a7188f))
* ensure `woocommerce` options are applicable for multisite setup ([#16](https://github.com/projek-xyz/wp-env/issues/16)) ([75a5efd](https://github.com/projek-xyz/wp-env/commit/75a5efd86901310bee1540324a8fb7ce4a38fe86))
* improve what's missing in [#9](https://github.com/projek-xyz/wp-env/issues/9) ([#11](https://github.com/projek-xyz/wp-env/issues/11)) ([d851b9c](https://github.com/projek-xyz/wp-env/commit/d851b9c1d0be8a52f16a1e2521cffe5e65294535))
* rearrange default media assets ([#12](https://github.com/projek-xyz/wp-env/issues/12)) ([4d8325e](https://github.com/projek-xyz/wp-env/commit/4d8325ee2715ce60e886354a38cbd819b4f48181))


### Bug Fixes

* don't install dev-dependencies during `make-dist.sh` ([5035b05](https://github.com/projek-xyz/wp-env/commit/5035b058a632cf5077737c98924b9e1648ffda41))
* make sure local `wp-cli` could work properly ([#19](https://github.com/projek-xyz/wp-env/issues/19)) ([33fa3a4](https://github.com/projek-xyz/wp-env/commit/33fa3a40e688f5f0ccaa49e89781f9675ee7a6f7))
* make sure to run `make-pot` during `make-dist` ([#20](https://github.com/projek-xyz/wp-env/issues/20)) ([55c8980](https://github.com/projek-xyz/wp-env/commit/55c8980a04b86b3bf3fd21617ca5c20e12034ec9))

## 0.0.1 (2026-05-04)


### Features

* **dev:** add config to remove plugins on initiation ([#7](https://github.com/projek-xyz/wp-env/issues/7)) ([8dde8c2](https://github.com/projek-xyz/wp-env/commit/8dde8c2f71a84f9ea702a72c3d290874f0dbccf5))
* **dev:** skip `pot` generation on un-distributable packages ([ffbbcdc](https://github.com/projek-xyz/wp-env/commit/ffbbcdcfa6c59e304172d7dff8531b37fa970b2c))
* init `commit-and-tag-version` to manage releases ([#8](https://github.com/projek-xyz/wp-env/issues/8)) ([87c4aa4](https://github.com/projek-xyz/wp-env/commit/87c4aa4d78d6c27414fac5ff7a34ce0cf0db83b2))
* initialize `mu-plugins` ([#3](https://github.com/projek-xyz/wp-env/issues/3)) ([f6729dd](https://github.com/projek-xyz/wp-env/commit/f6729dda5172f6c42056655ffd676e3b0196dde6))


### Bug Fixes

* improve multisite setup ([#4](https://github.com/projek-xyz/wp-env/issues/4)) ([f1f4b5f](https://github.com/projek-xyz/wp-env/commit/f1f4b5fcf1006c4ce783c676a99255c1d8e34a67))
