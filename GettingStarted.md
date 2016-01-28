**If you have any problems with this tutorial, please post them in the comments section below or at the Zoop-users group at http://groups.google.com/group/zoop-users**

### Summary ###

Are you setting up Zoop for the first time or has it been so long since you started a Zoop project that you can't remember how?  This page will help you do both.  The first section is a pitch for the Zoop mailing list.  The second section outlines how to get Zoop installed and running on your box.  The third section shows you how to set up a new Zoop website/application and start using the power of the Zoop framework.

### Getting Started TOC: ###
  1. **Joining the Zoop-users mailing list**
  1. **Setting up the Zoop framework**
  1. **Setting up a new Zoop project (website/web application)**
  1. **What to do next?**

### 1. Joining the Zoop-users mailing list ###
This is your main source for all things Zoop.  If you need help, have questions, or have suggestions for improvement...this is the place.  The [Zoop-users](http://groups.google.com/group/zoop-users) group is tightly knit with the Zoop project, and often your questions will be directly answered by the developers of the Zoop framework. The link is: http://groups.google.com/group/zoop-users


### 2. Setting up the Zoop framework ###
Zoop is stored in a directory on your webserver.  It doesn't really matter where it's stored, as long as it is readable to the webserver.  There are two ways to get Zoop on your server.
#### Good ####
Download the tar of of Zoop.  [Get the latest tarball here](http://code.google.com/p/zoop/downloads/list).  Expand the tarball and move it into the directory in which you've decided to hold the code.  (Example: /home/disco/code/lib/zoop)

#### Best ####
Checkout the subversion (SVN) repository of the code.  This is the best choice because you get the latest bug fixes and feature enhancements whenever they are pushed out.  To do this option, create a directory called "zoop" and stick it in the directory which you decided will host the Zoop framework.  Navigate to the directory containing the "zoop" directory you created and run the following command:
```
svn checkout http://zoop.googlecode.com/svn/trunk/
```
This will start the checkout process.  Once it's all downloaded, you'll have the latest Zoop framework.  Whenever you want updated code, just run the update command:
```
svn update zoop/
```
and you will get the latest changes.


### 3. Setting up a new Zoop project (website / web application) ###

#### Stationary ####
The Zoop framework has a "stationary" folder which contains starter projects for different project types.  The most common stationary to use is "basic."  After you get used to Zoop, you might find that another stationary suits your needs for starting future projects.

To export the **basic** stationary, navigate to the directory where you store pages served up by the web server (Example:/var/www) and run the following command:
```
svn export http://zoop.googlecode.com/svn/trunk/stationary/basic
```
This will create a directory called "basic."

#### Configuration ####
In your favorite text editor (TextMate, anyone?), go into **basic** and open config.php.  For the line that says 'zoop\_dir', put the path to where you stored the Zoop framework (Step 2).  From the example above, the line would end up being:
```
define('zoop_dir', '/home/disco/code/lib/zoop/framework');
```

Next you need to give your webserver (for this example we're using Apache) write permissions to the tmp/gui directory.  This lets the Smarty engine create cache templates of your site to cut down on processing time.  To do this, navigate to the **basic** directory so you can see the directory **tmp** listed.  Usually Apache's username is either apache or www-data.  Our server uses "apache" as the username.  If yours uses www-data, replace it in the commands.  Run the following two commands to give tmp and gui the correct permissions:
```
sudo chgrp -R apache tmp
chmod -R 770 tmp
```
These commands do two things.  The first command recursively goes through the tmp directory and adds it to the webserver group.  The second command recursively goes through and gives full access to the owner and group of the tmp dir, but no access to anyone else.  This way, you as the owner and the webserver group both have complete control over creating content here.  You can use this process for other directories as well, like a photo upload folder where you let users upload profile pictures.


### 4. Examples ###
Now you have the Zoop framework and a blank project ready to go.  From here you can look at examples in the Zoop directory of how to set up a database, user login, or many other things.

Here is a brief explanation of topics that can be learned from the examples:
  * [HelloWorld](http://code.google.com/p/zoop/source/browse/trunk/examples/HelloWorld/): Do you really need an explanation for this one?
  * [Couch](http://code.google.com/p/zoop/source/browse/trunk/examples/couch): Connecting the controller to the view using smarty templates
  * [Zone](http://code.google.com/p/zoop/source/browse/trunk/examples/zone): Quick example of how to use the zone convention employed in zoop.

### 5. Find Help and Get Involved ###
Look around the Wiki for more explanations and always ALWAYS...go to the [Zoop-users mailing list.](http://groups.google.com/group/zoop-users)  Once again, that link is: http://groups.google.com/group/zoop-users   Join the group and start participating.