# Introduction #

Each module is either
  * Experimental - API is very likely to change.  It is probably unrefined and code may not yet be generalized enough to make it useful for a wide variety of cases.
  * Stable - **Relatively** stable in that we have **more or less** settled on an API for the module and it most likely works pretty good.
  * 2nd Generation - This module should work quite well as it is a rewrite of a previously stable module and should be an improvement over that module.
  * 3rd Generation, etc - 2nd Generation++
  * 3rd Party.  Code that is more or less just imported right into Zoop.  We don't generally use outside projects that aren't already very stable.

These designations are given so you have some idea of the relative stability of each module just to provide some perspective.  As always however Zoop 1.50 is an unstable project and we may change anything at any time without warning and without any concern for backwards compatibility.

# Details #

List of modules
  * App - 2nd Generation.  It is quite small and doesn't do much.  You also don't generally need interact with it much directly.
  * CLI - Experimental.  Not much there yet.  Hopefully someday it will make it much easier to create cli apps.
  * DB:Sql functions - 3rd Generation.  These work quite well are are very useful.  They are based on code that has been evolving for about 5 or 6 years.  The current implementation has some big improvements and thus might also have some bugs.
  * DB:DbObject - Experimental.  It is actually based on some decent previously used code that has been well tested.  But ORM is hard and this is where PHP most shows it's weaknesses.  The gold standard here, I feel, is ActiveRecord.  We are not trying to be ActiveRecord but we would someday like to be about as useful and have some advantages over it.
  * gui - 2nd Generation.  Gui is very solid.  All the real work is done by Smarty which is very stable code.  We are working on some additions to the module to help with validating and processing forms that are still very experimental though.
  * mail - 2nd Generation.  It is based on some earlier code and we are learning from mistakes made in it. It isn't however very robust and needs a lot of work.
  * migration - Experimental.  It doesn't actually exist at the time of this writing.
  * permission - Experimental.  It doesn't actually exist at the time of this writing.
  * phpmailer - 3rd party.  Used for the mail module
  * session - Experimental.  It will add a lot of value to the traditional PHP ways of handling sessions.  At this point though it doesn't really do very much.
  * smarty - 3rd party.  Used by the gui module.
  * spyc - 3rd party.  Used for yaml config parsing.
  * utils - This should probably go away so we can just have a utils file in each module.
  * zone - 2nd Generation.  It is a rewrite of some very solid code that had been in use for several years.  This rewrite has been very stable and easy to work with but it hasn't been tested nearly as much as the previous version.


