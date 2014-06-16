<?php
/**
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
?>
<!DOCTYPE html>
<html>
<head>
	<?php echo $this->Html->charset(); ?>
	<title>
		<?php echo $title_for_layout; ?> &ndash; EPUBster
	</title>
	<?php
    echo $this->Html->css(array(
      '/assets/bootstrap/css/bootstrap.min',
      '/assets/font-awesome/css/font-awesome.min',
      '/assets/font-awesome/css/font-awesome.min',
      '/assets/markitup/skins/simple/style',
      '/assets/markitup/sets/markdown/style',
      '/assets/medium-editor/css/medium-editor.min.css',
      '/assets/medium-editor/css/themes/bootstrap.min.css',
      '/css/global'
    ));
    echo $this->Html->script(array(
      '/assets/jquery.min',
      '/assets/jquery-ui.min',
      '/assets/bootstrap/js/bootstrap.min',
      '/assets/spin.min',
      '/assets/jstorage.min',
      '/assets/rangy/rangy-core',
      '/assets/rangy/rangy-cssclassapplier',
      '/assets/markitup/jquery.markitup',
      '/assets/markitup/sets/markdown/set',
      '/assets/medium-editor/js/medium-editor.min',
      '/scripts/global.min'
    ));

    if ($this->request->controller === 'editions' && $this->view === 'edit') {
      echo $this->Html->script(array(
        'http://bp.yahooapis.com/2.4.21/browserplus-min.js',
        '/assets/plupload/plupload.js',
        '/assets/plupload/plupload.gears.js',
        '/assets/plupload/plupload.silverlight.js',
        '/assets/plupload/plupload.flash.js',
        '/assets/plupload/plupload.browserplus.js',
        '/assets/plupload/plupload.html4.js',
        '/assets/plupload/plupload.html5.js',
        '/scripts/media-manager.min.js'
      ));
    }
    
		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
    echo $this->Html->scriptBlock(sprintf('var SITE_URL = "%s"; var UPLOAD_URL = "%s";', SITE_URL, SITE_URL.BASE_UPLOAD_PATH));
	?>
  <link rel="shortcut icon" href="<?php echo $this->Html->url('/images/icons/favicon.png'); ?>">
  <link rel="apple-touch-icon" href="<?php echo $this->Html->url('/images/icons/apple-touch-icon.png'); ?>">
  <link rel="apple-touch-icon" sizes="72x72" href="<?php echo $this->Html->url('/images/icons/apple-touch-icon-72x72.png'); ?>">
  <link rel="apple-touch-icon" sizes="114x114" href="<?php echo $this->Html->url('/images/icons/apple-touch-icon-114x114.png'); ?>
">

</head>
<body>
	<div id="application" class="container">
	  <?php if ($this->view !== 'markdown_preview') : ?>
	  <header id="header">
  		<nav id="navigation" class="navbar navbar-default" role="navigation">
        <div class="navbar-header">
          <?php echo $this->Html->link(__('EPUBster'), '/', array('class' => 'navbar-brand')); ?>
        </div>
        <?php if (isset($currentUser)) : ?>
        <div class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li<?php echo ($this->request->controller === 'editions') ? ' class="active"': ''; ?>><?php echo $this->Html->link('<span class="fa fa-book"></span> '.__('Editions'), array('controller' => 'editions', 'action' => 'index'), array('escape' => false)); ?></li>
            <?php if ($currentUser['id'] == 1) : ?>
            <li<?php echo ($this->request->controller === 'users') ? ' class="active"': ''; ?>><?php echo $this->Html->link('<span class="fa fa-users"></span> '.__('Users'), array('controller' => 'users', 'action' => 'index'), array('escape' => false)); ?></li>
            <?php endif; ?>
          </ul>
          <ul class="nav navbar-nav navbar-right">
            <li><?php echo $this->Html->link('<i class="fa fa-sign-out"></i> '.__('Log out'), array('controller' => 'users', 'action' => 'logout'), array('escape' => false)); ?></li>
          </ul>
          
        </div>
        <?php endif; ?>
  		</nav>
	  </header>
	  <?php endif; ?>
		<div id="main">
			<?php echo $this->Session->flash(); ?>
			<?php echo $this->fetch('content'); ?>
			<?php echo $this->element('sql_dump'); ?>
		</div>
	  <?php if ($this->view !== 'markdown_preview') : ?>
		<footer id="footer">
		  Version <?php echo EPUBSTER_VERSION; ?>
		</footer>
		<?php endif; ?>
	</div>
</body>
</html>
