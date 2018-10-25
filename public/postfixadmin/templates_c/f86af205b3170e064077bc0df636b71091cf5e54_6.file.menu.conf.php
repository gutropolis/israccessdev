<?php /* Smarty version 3.1.29, created on 2018-07-09 15:27:20
         compiled from "/home/sites/culturaccess/public/postfixadmin/configs/menu.conf" */ ?>
<?php
/* Smarty version 3.1.29, created on 2018-07-09 15:27:20
  from "/home/sites/culturaccess/public/postfixadmin/configs/menu.conf" */

if ($_smarty_tpl->smarty->ext->_validateCompiled->decodeProperties($_smarty_tpl, array (
  'has_nocache_code' => false,
  'version' => '3.1.29',
  'unifunc' => 'content_5b4362b87dad28_55204056',
  'file_dependency' => 
  array (
    'f86af205b3170e064077bc0df636b71091cf5e54' => 
    array (
      0 => '/home/sites/culturaccess/public/postfixadmin/configs/menu.conf',
      1 => 1498412972,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5b4362b87dad28_55204056 ($_smarty_tpl) {
$_smarty_tpl->smarty->ext->configLoad->_loadConfigVars($_smarty_tpl, array (
  'sections' => 
  array (
    'adminlistadmin' => 
    array (
      'vars' => 
      array (
        'url_edit_admin' => 'edit.php?table=admin',
      ),
    ),
  ),
  'vars' => 
  array (
    'url_main' => 'main.php',
    'url_editactive' => 'editactive.php?table=',
    'url_list_admin' => 'list.php?table=admin',
    'url_create_admin' => 'edit.php?table=admin',
    'url_list_domain' => 'list.php?table=domain',
    'url_edit_domain' => 'edit.php?table=domain',
    'url_list_virtual' => 'list-virtual.php',
    'url_create_mailbox' => 'edit.php?table=mailbox',
    'url_create_alias' => 'edit.php?table=alias',
    'url_create_alias_domain' => 'edit.php?table=aliasdomain',
    'url_fetchmail' => 'list.php?table=fetchmail',
    'url_fetchmail_new_entry' => 'edit.php?table=fetchmail',
    'url_sendmail' => 'sendmail.php',
    'url_broadcast_message' => 'broadcast-message.php',
    'url_password' => 'edit.php?table=adminpassword',
    'url_backup' => 'backup.php',
    'url_viewlog' => 'viewlog.php',
    'url_logout' => 'login.php',
    'url_user_main' => 'main.php',
    'url_user_edit_alias' => 'edit-alias.php',
    'url_user_vacation' => 'vacation.php',
    'url_user_password' => 'password.php',
    'url_user_logout' => 'login.php',
    'tr_header' => '<tr class="header">',
    'tr_hilightoff' => '<tr class="hilightoff" onmouseover="className=\'hilighton\';" onmouseout="className=\'hilightoff\';">',
    'url_delete' => 'delete.php',
    'url_search' => 'list-virtual.php',
    'form_search' => '<form name="search" method="post" action="list-virtual.php"><input name="search[_]" size="10" /></form>',
  ),
));
}
}
