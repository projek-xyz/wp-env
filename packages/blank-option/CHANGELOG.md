# Changelog

All notable changes to this project will be documented in this file. See [commit-and-tag-version](https://github.com/absolute-version/commit-and-tag-version) for commit guidelines.

## 0.0.2 (2026-06-04)


### fix

* **blank-option:** don't throw exception if the metadata is actually empty ([04f889e](https://github.com/projek-xyz/wp-env/commit/04f889e92ab09c813fc39094c6cf65e84a341962))
* **blank-option:** error when passing an empty array as unnamed `atts` to element ([c85d4ed](https://github.com/projek-xyz/wp-env/commit/c85d4edc8d058eefbe42360fddf43b9ec475fdd1)), closes [#48](https://github.com/projek-xyz/wp-env/issues/48)


### chore

* **blank-option:** add missing param types ([f5a8a49](https://github.com/projek-xyz/wp-env/commit/f5a8a4963376555e081603616f2631ccf2cb1076))


### feat

* **blank-option:** add `clear` and `whitespace` helper to `html-element` class ([4c4bda4](https://github.com/projek-xyz/wp-env/commit/4c4bda46e6ce8e960671ce432724ae8925c3a6e1))
* **blank-option:** add list of banned tags from `html-element` class ([c3c9fc7](https://github.com/projek-xyz/wp-env/commit/c3c9fc7a6f9b2ee59b2e9d64348afdc00e33a4ca))
* **blank-option:** allow `Html_Element::call` method to return itself and ignore echoed content ([1716b7a](https://github.com/projek-xyz/wp-env/commit/1716b7a7c3a4e46ab9604fce0e62993d3d32cafc))
* **blank-option:** allow admin pages to define their own priority for `admin_menu` registration ([c93680c](https://github.com/projek-xyz/wp-env/commit/c93680c05c39cbd9bec3203815306dbaa47a05df))
* **blank-option:** allow to create assign html attributes using variadic named arguments (#48) ([f187129](https://github.com/projek-xyz/wp-env/commit/f1871297a57abbbabf825146a52cb720ce57f8a7)), closes [#48](https://github.com/projek-xyz/wp-env/issues/48)
* **blank-option:** make sure child elements of `table` and `form` are rendered properly ([eca9b9c](https://github.com/projek-xyz/wp-env/commit/eca9b9cc7db5d96dc878a5b9adc6b385298951f7))


### refactor

* **blank-option:** let `Admin_Page` children to decide whether they need to enqueue assets or not ([e059741](https://github.com/projek-xyz/wp-env/commit/e059741e7643171ace93cc1b3d72e4cc5c917892))
* **blank-option:** rename `directory_path` and `directory_url` method of `Plugin` class ([6b4f829](https://github.com/projek-xyz/wp-env/commit/6b4f829172bf91bbe83d8f017fd9f9d38b83964c))

## 0.0.1 (2026-06-02)


### feat

* **blank-option:** add plugin action links registration hook (#37) ([062485d](https://github.com/projek-xyz/wp-env/commit/062485d47ded4a6e638209272e00cbd59fd30d06)), closes [#37](https://github.com/projek-xyz/wp-env/issues/37)
* **blank-option:** init `html-element` helper class (#42) ([0a20966](https://github.com/projek-xyz/wp-env/commit/0a20966f0090b84b359eeddbe9268825b6852ccb)), closes [#42](https://github.com/projek-xyz/wp-env/issues/42)
* **blank-option:** init `updater` class (#44) ([fc7dd6f](https://github.com/projek-xyz/wp-env/commit/fc7dd6f19a503f06bd4c413eb48c5dbe98b24179)), closes [#44](https://github.com/projek-xyz/wp-env/issues/44)
* **blank-option:** initialize new starter plugin (#33) ([cec1d50](https://github.com/projek-xyz/wp-env/commit/cec1d507c7bd5a5041f212c9d71aaa1510e0690b)), closes [#33](https://github.com/projek-xyz/wp-env/issues/33)


### refactor

* **blank-option:** full rewrite the `blank-option` plugin (#41) ([11e4e72](https://github.com/projek-xyz/wp-env/commit/11e4e7244983e0f78e125309d061f0af53a6cf11)), closes [#41](https://github.com/projek-xyz/wp-env/issues/41)
