<?php
include('config.php');
include(zoop_dir . '/Zoop.php');

Zoop::loadLib('zone');
Zoop::loadLib('db');
Zoop::registerDomain('PersonStuff');

ZoneApplication::handleRequest();
