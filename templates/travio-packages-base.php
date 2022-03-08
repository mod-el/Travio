<?php
$form->renderLangSelector();
?>

<div class="pt-3">
	<b>SEO</b>
</div>

<div class="flex-fields-wrap">
	<div>
		Title<br/>
		<?php $form['title']->render(); ?>
	</div>
</div>

<div class="flex-fields-wrap">
	<div>
		Description<br/>
		<?php $form['description']->render(); ?>
	</div>
	<div>
		Keywords<br/>
		<?php $form['keywords']->render(); ?>
	</div>
</div>
