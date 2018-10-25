<?php
/* Smarty version 3.1.29, created on 2018-07-09 15:26:01
  from "/home/sites/culturaccess/public/postfixadmin/templates/footer.tpl" */

if ($_smarty_tpl->smarty->ext->_validateCompiled->decodeProperties($_smarty_tpl, array (
  'has_nocache_code' => false,
  'version' => '3.1.29',
  'unifunc' => 'content_5b4362696205d3_04279906',
  'file_dependency' => 
  array (
    'cd391cb04dc082d22dc39b2f743cfe5e6c57bb7d' => 
    array (
      0 => '/home/sites/culturaccess/public/postfixadmin/templates/footer.tpl',
      1 => 1498412972,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5b4362696205d3_04279906 ($_smarty_tpl) {
if (!is_callable('smarty_modifier_replace')) require_once '/home/sites/culturaccess/public/postfixadmin/smarty/libs/plugins/modifier.replace.php';
?>
<!-- <?php echo basename($_smarty_tpl->source->filepath);?>
 -->
<div id="footer">
	<a target="_blank" href="http://postfixadmin.sf.net/">Postfix Admin <?php echo $_smarty_tpl->tpl_vars['version']->value;?>
</a>
	<span id="update-check">&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
	<a target="_blank" href="http://postfixadmin.sf.net/update-check.php?version=<?php echo rawurlencode($_smarty_tpl->tpl_vars['version']->value);?>
"><?php echo $_smarty_tpl->tpl_vars['PALANG']->value['check_update'];?>
</a></span>
    <?php if (isset($_SESSION['sessid'])) {?>
        <?php if ($_SESSION['sessid']['username']) {?>
            &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;	
            <?php echo smarty_modifier_replace($_smarty_tpl->tpl_vars['PALANG']->value['pFooter_logged_as'],"%s",$_SESSION['sessid']['username']);?>

        <?php }?>
    <?php }?>
	<?php if ($_smarty_tpl->tpl_vars['CONF']->value['show_footer_text'] == 'YES' && $_smarty_tpl->tpl_vars['CONF']->value['footer_link']) {?>
		&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
		<a href="<?php echo $_smarty_tpl->tpl_vars['CONF']->value['footer_link'];?>
"><?php echo $_smarty_tpl->tpl_vars['CONF']->value['footer_text'];?>
</a>
	<?php }?>
</div>
</div>
</body>
</html>
<?php }
}
