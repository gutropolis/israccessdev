<?php
/* Smarty version 3.1.29, created on 2018-07-09 15:26:01
  from "/home/sites/culturaccess/public/postfixadmin/templates/flash_error.tpl" */

if ($_smarty_tpl->smarty->ext->_validateCompiled->decodeProperties($_smarty_tpl, array (
  'has_nocache_code' => false,
  'version' => '3.1.29',
  'unifunc' => 'content_5b436269608556_25614017',
  'file_dependency' => 
  array (
    '620cd34674d76ba0ef4168a251a6676c4f0d66db' => 
    array (
      0 => '/home/sites/culturaccess/public/postfixadmin/templates/flash_error.tpl',
      1 => 1498412972,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5b436269608556_25614017 ($_smarty_tpl) {
?>
<!-- <?php echo basename($_smarty_tpl->source->filepath);?>
 -->
<br clear="all"/><br />
<?php if (isset($_SESSION['flash'])) {
if (isset($_SESSION['flash']['info'])) {?><ul class="flash-info"><?php
$_from = $_SESSION['flash']['info'];
if (!is_array($_from) && !is_object($_from)) {
settype($_from, 'array');
}
$__foreach_msg_0_saved_item = isset($_smarty_tpl->tpl_vars['msg']) ? $_smarty_tpl->tpl_vars['msg'] : false;
$_smarty_tpl->tpl_vars['msg'] = new Smarty_Variable();
$_smarty_tpl->tpl_vars['msg']->_loop = false;
foreach ($_from as $_smarty_tpl->tpl_vars['msg']->value) {
$_smarty_tpl->tpl_vars['msg']->_loop = true;
$__foreach_msg_0_saved_local_item = $_smarty_tpl->tpl_vars['msg'];
?><li><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['msg']->value, ENT_QUOTES, 'UTF-8', true);?>
</li><?php
$_smarty_tpl->tpl_vars['msg'] = $__foreach_msg_0_saved_local_item;
}
if ($__foreach_msg_0_saved_item) {
$_smarty_tpl->tpl_vars['msg'] = $__foreach_msg_0_saved_item;
}
?></ul><?php }
if (isset($_SESSION['flash']['error'])) {?><ul class="flash-error"><?php
$_from = $_SESSION['flash']['error'];
if (!is_array($_from) && !is_object($_from)) {
settype($_from, 'array');
}
$__foreach_msg_1_saved_item = isset($_smarty_tpl->tpl_vars['msg']) ? $_smarty_tpl->tpl_vars['msg'] : false;
$_smarty_tpl->tpl_vars['msg'] = new Smarty_Variable();
$_smarty_tpl->tpl_vars['msg']->_loop = false;
foreach ($_from as $_smarty_tpl->tpl_vars['msg']->value) {
$_smarty_tpl->tpl_vars['msg']->_loop = true;
$__foreach_msg_1_saved_local_item = $_smarty_tpl->tpl_vars['msg'];
?><li><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['msg']->value, ENT_QUOTES, 'UTF-8', true);?>
</li><?php
$_smarty_tpl->tpl_vars['msg'] = $__foreach_msg_1_saved_local_item;
}
if ($__foreach_msg_1_saved_item) {
$_smarty_tpl->tpl_vars['msg'] = $__foreach_msg_1_saved_item;
}
?></ul><?php }
}
}
}
