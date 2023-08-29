This plugin requires the [Moodle Atto Translations](https://github.com/andrewhancox/moodle-atto_translations) plugin.

Full documentation is available at: https://docs.moodle.org/311/en/Content_translation_plugin_set

# To Install it manually #
- Unzip the plugin in the moodle .../filter/translations directory.
- Also install the Atto plugin from: https://github.com/andrewhancox/moodle-atto_translations

# To Enable it #
- Go to "Site Administration &gt;&gt; Plugins &gt;&gt; Filters &gt;&gt; Manage filters" and enable the 'Content translations' plugin there.
- Go to "Site Administration &gt;&gt; Plugins &gt;&gt; Filters &gt;&gt; Content translations" and choose a caching level appropriate to your site.

# Cacheing #
Since the filter makes database calls and, if Google Translate is enabled, web service calls, it is advisable to enable caching by working with the cachingmode setting. If you have a small volume of course material in active use then Application mode caching is advised, if you have a large volume then Session. In any case, the default of Request is rarely the optimal choice.

# How it chooses translations #

- Get a prioritised list of the languages we could translate into - starting with the user's preferred language, then working through parent languages.
- Get the translation(s) that fit the highest priority language.


- If a span tag with an MD5 key in the data-translationhash property and a translation with a matching md5key can be found then use that one.
- Otherwise, if a translation can be found which has an MD5 key matching the MD5 hash of the content then use that.
- Finally, if a translation has a 'last generated hash' (meaning the MD5 hash of the content it was last updated in reference to) which matches the MD5 hash of the content then use that.

# To Use it #
In order to use this plugin, you have to first install and enable the plugin. If you don't know how to install or how to enable the plugin, the instructions can be found at the beginning of this page.

After enabling the plugin, you can set the course content language, and enable or disable widget/popup translations that typically pops up when a user selects text from the course content. You can also set the target language for the widget/popup translations.

Let's take a look at how we are going to do that.

## Set the course content language ##
You can set the course content language using **one** of the following ways:
1. The easy way to do this is to use the top navigation bar. That navigation bar can only be accessed when you are on a course page.
<img src="images/Set course content language using the navigation bar.png" />
2. The other way is to set it in the settings
<img src="images/Set course content language via settings.png" />

## Enable or disable widget/popup translations ##
You can enable or disable widget/popup translations using **one** of the following ways:
1. The easy way to do this is to use the top navigation bar. That navigation bar can only be accessed when you are on a course page.
<img src="images/Enable or disable widget using the navigation bar.png" />
2. The other way is to set it in the settings
<img src="images/Enable or disable widget via settings.png" />

## Set the target language for the widget/popup translations ##
You can enable or disable widget/popup translations using **one** of the following ways:
1. You can set this in settings
<img src="images/Set target widget language via settings.png" />
2. Or you can set it by changing the target language using the widget/popup.
<img src="images/Set target widget language using the widget.png" />

**N.B**: Users with the 'filter/translations:edittranslations' capability will see an icon in the top right hand corner of the screen to enable the translator view of the course. At this point all translatable text will have an icon injected next to it to allow it to be translated.

# To migrate from filter_fulltranslate #
A CLI tool is available to migrate all translations across from the filter_fulltranslate.

It is recommended that you clean out any unwanted translations that may have been generated as follows:
````
delete from mdl_filter_fulltranslate where sourcetext like '%{mlang%';
````

You can then copy the translations from filter_fulltranslate into filter_translations as follows:
````
php cli/migrate_filter_fulltranslate.php --confirm
````

# To add translation span tags to existing data #
A CLI tool is available to automatically add span tags to existing data. Please use with extreme caution.

You can run the tool as follows which will show help text:
````
php cli/insert_spans.php
````

Author
------

The module has been written and is currently maintained by Andrew Hancox on behalf of [Open Source Learning](https://opensourcelearning.co.uk).

Useful links
------------

* [Open Source Learning](https://opensourcelearning.co.uk)
* [Bug tracker](https://github.com/andrewhancox/moodle-filter_translations/issues)

License
-------

This program is free software: you can redistribute it and/or modify it under the
terms of the GNU General Public License as published by the Free Software Foundation,
either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this
program. If not, see <http://www.gnu.org/licenses/>.
