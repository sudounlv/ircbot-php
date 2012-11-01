**Phergie**

Phergie is an IRC bot written for PHP 5.2.

Main project web site: http://phergie.org

Instructions for running your own instance of Phergie: http://phergie.org/users/

Architectural overview for plugin developers: http://phergie.org/developers/

Support: http://phergie.org/support/

Bug reports/feature requests: http://github.com/phergie/phergie/issues

---------------------------------------

**Sudo Plugins**

*SudoHelp*
<br/>!help &lt;botName&gt;

*SudoSayHello*
<br/>hello <botName>
<br/>/msg hello &lt;botName&gt;

*SudoLastFive*
<br/>!last5 &lt;nick&gt;

*SudoLurkers*
<br/>!lurkers

---------------------------------------

**Persistence**

Plug-ins that currently support persistence to maintain state: SudoLastFive, SudoLurkers

1. Create this MySQL table:

CREATE TABLE `plugin_state` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_name` varchar(128) DEFAULT NULL,
  `variable_name` varchar(128) DEFAULT NULL,
  `variable_value` text,
  PRIMARY KEY (`id`)
)

2. Set up your database properties in Settings.php:

'database' => array(
    'name' => '**your-database-name**',
    'host' => '**your-database-host**',
    'username' => '**username**',
    'password' => '**password**root'
)

3. Configure plug-ins that support persistence in Settings.php:

'sudolurkers.persistence' => true,

'sudolastfive.persistence' => true,

That should be it! :)
