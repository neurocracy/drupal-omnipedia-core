This contains the source files for the "*Omnipedia - Core*" Drupal module, which
provides the wiki node framework for [Omnipedia](https://omnipedia.app/).

⚠️⚠️⚠️ ***Here be potential spoilers. Proceed at your own risk.*** ⚠️⚠️⚠️

----

# Why open source?

We're dismayed by how much knowledge and technology is kept under lock and key
in the videogame industry, with years of work often never seeing the light of
day when projects are cancelled. We've gotten to where we are by building upon
the work of countless others, and we want to keep that going. We hope that some
part of this codebase is useful or will inspire someone out there.

----

# Description

This contains the framework for managing our simulated wiki pages (Drupal nodes)
and their revisions. This includes various services to find and interact with
them, and to query and track what simulated revisions they have (one per
in-universe day). It provides a custom Drupal node class that we extend with
various convenience methods, and related event subscribers and cache contexts.

Note that this does not contain the framework to manage the simulated date
system itself; that can be found in the [`omnipedia_date`
module](https://github.com/neurocracy/drupal-omnipedia-date).

This module is named `omnipedia_core` for historical reasons, as it used to
contain more than the wiki node framework. In the future, this module may be
discontinued in favour of a more accurately named module, e.g.
`omnipedia_node`, or `omnipedia_wiki_node`, etc.

----

# Planned improvements

* [Move the `omnipedia.wiki_node_access` service to the `omnipedia_access` module](https://github.com/neurocracy/drupal-omnipedia-core/issues/3).

* [Move the `field_hide_from_search` configuration to the `omnipedia_search` module if possible](https://github.com/neurocracy/drupal-omnipedia-core/issues/2).

* [Refactor the Node class as an entity bundle class](https://github.com/neurocracy/drupal-omnipedia-core/issues/4).

----

# Requirements

* [Drupal 9.5 or 10](https://www.drupal.org/download) ([Drupal 8 is end-of-life](https://www.drupal.org/psa-2021-11-30))

* PHP 8

* [Composer](https://getcomposer.org/)

----

# Installation

## Composer

### Set up

Ensure that you have your Drupal installation set up with the correct Composer
installer types such as those provided by [the `drupal/recommended-project`
template](https://www.drupal.org/docs/develop/using-composer/starting-a-site-using-drupal-composer-project-templates#s-drupalrecommended-project).
If you're starting from scratch, simply requiring that template and following
[the Drupal.org Composer
documentation](https://www.drupal.org/docs/develop/using-composer/starting-a-site-using-drupal-composer-project-templates)
should get you up and running.

### Repository

In your root `composer.json`, add the following to the `"repositories"` section:

```json
"drupal/omnipedia_core": {
  "type": "vcs",
  "url": "https://github.com/neurocracy/drupal-omnipedia-core.git"
}
```

### Installing

Once you've completed all of the above, run `composer require
"drupal/omnipedia_core:4.x-dev@dev"` in the root of your project to have
Composer install this and its required dependencies for you.

----

# Major breaking changes

The following major version bumps indicate breaking changes:

* 4.x:

  * Requires Drupal 9.5 or [Drupal 10](https://www.drupal.org/project/drupal/releases/10.0.0).

  * Increases minimum version of [Hook Event Dispatcher](https://www.drupal.org/project/hook_event_dispatcher) to 3.1, removes deprecated code, and adds support for 4.0 which supports Drupal 10.
