# Introduction #

One of the biggest problems with much of the PHP software out there is that it does not adhere to the [MVC paradigm](ModelViewController.md).  That is, it does not separate out the domain logic (the data model) from the presentation or display logic (the view or user interface).  This page is about how to separate out the display logic from the domain logic.


# Details #

So, to separate your display logic from the rest of your application you generally use a [web templating system](http://en.wikipedia.org/wiki/Web_template_system).  The templating system we chose for Zoop is called [Smarty](http://smarty.php.net).  The idea is basically this:  You put all of your HTML into a separate file.  Then you pass in the variables needed to display the dynamic content for that page.  You access those variables through special tags that are specific to the template engine.  Smarty tags are by default delimited with {}.  See the below examples:

## Simple Example ##

### Bad: Without a templating system ###
```
<html>
  <body>
     <? echo "Hello world!"; ?>
  </body>
</html>
```

### Good: With a templating system ###
_hello.php_
```
<?
include("Smarty.class.php");
$message = "Hello world!";
$template = new Smarty();
$template->assign('message', $message);
$template->display('hello.tpl');
?>
```

_hello.tpl_
```
<html>
  <body>
    {$message}
  </body>
</html>
```

Now, if separating the display from the program logic is old hat to you, then you are looking at this and saying, "ok, so that's how you do it in Zoop."  But if this is a new idea for you then you might be thinking, "now my simple one file program is now a two file program with twice as many lines.  How is that any simpler?"

Now imagine a scenario where you have lots of complex program logic in hello.php and lots of complex display logic in hello.tpl.  Now let's say you want to change how the program logic works but leave the display logic exactly the same.  As long as you adhere to the interface you've defined between them this is very simple.  Now let's say you want to completely change how you are displaying the information but the information doesn't change.  This is much easier when it is separated out from the program logic.  Also it makes it easier to have a designer work on the display and a programmer work on the program logic.

This sort of layering is a common example of implementing the concept of [Separation of Concerns](http://en.wikipedia.org/wiki/Separation_of_concerns).  It helps to keep the complexity of the system down by breaking the code into distinct units and defining clear interfaces between those units.  Then each unit can be modified without having to take into account every detail of how the rest of the system is implemented.

Many open source PHP projects began by mixing program and display logic because of it's initial convenience.  As a software project becomes larger however it is quickly apparent how something initially so convenient becomes a major obstacle to maintainability and extensibility.  See the [roadmap for the Mantis bug tracking system](http://www.mantisbt.org/roadmap.php) for an example of a project trying to make the switch.  Mantis is a fantastic piece of software but will be unable to make certain kinds of changes until a template system is put into place.

The moral of the story is: don't mix program logic with display logic.  Zoop uses the Smarty template system to achieve this end.  In order to ease the configuration of Smarty and integrate it into Zoop there is a Zoop module called gui that extends Smarty to accomplish these goals.  See later articles for an explanation of how to use gui.