# Introduction #

When the browser tries to load a page that was generated via a POST request they get that awful warning.  Users should never have to try to figure out what happens if they hit OK.

# Details #

The answer here is to never, ever, ever generate output on a post.  Instead always redirect.

## Simple Example ##

### Bad: Outputting data on a POST request ###
_formpage.tpl_
```
<html>
  <body>
    <form action="postpage.php" method="POST">
      <input type="text" name="somefield">
    </form>
  </body>
</html>
```

_postpage.php
```
// code to save the somefield somewhere

$template = new Smarty();
$template->display('postpage.tpl');
```_

### Good: Redirect on every POST request ###
_formpage.tpl_ : _Same as above_

_postpage.php
```
// code to save the somefield somewhere

Redirect('nextpage.php');
```_

Redirect($location) is defined in Zoop to call header("location: $location).

Nextpage can do whatever needs to be done next, but you don't have the logic for saving the data muddled up with the logic for creating the next page.  Also the user doesn't get those nasty warnings about re-posting the data that they don't understand.