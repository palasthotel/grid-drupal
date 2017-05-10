<?php

/**
 * @var array $content
 */

$classes = $this->classes;
array_push($classes, 'grid-box');

if ($this->style) {
	array_push($classes, $this->style);
}

if (!empty($this->title)) {
	array_push($classes, 'has-title');
}

?>
<div class="<?php echo implode($classes, ' '); ?>">
	<?php if (!empty($this->title)): ?>
		<?php if (!empty($this->titleurl)): ?>
			<h2 class="grid-box-title b-title"><a class="grid-box-title-link grid-box-title-text" href="<?php echo $this->titleurl; ?>"><?php echo $this->title; ?></a></h2>
		<?php else: ?>
			<h2 class="grid-box-title grid-box-title-text b-title"><?php echo $this->title; ?></h2>
		<?php endif; ?>
	<?php endif; ?>

	<?php if (!empty($this->prolog)): ?>
		<div class="grid-box-prolog b-prolog">
			<?php echo $this->prolog; ?>
		</div>
	<?php endif; ?>


	<div class="grid-box__gallery--images">

		<?php
		foreach ($content as $item){
			?>
			<figure class="grid-box__gallery--image">
				<img src="<?php echo $item->src; ?>" />

				<?php if(isset($item->description) && "" != $item->description): ?>
					<figcaption><?php echo $item->description; ?></figcaption>
				<?php endif; ?>

			</figure>
			<?php
		}
		?>
	</div>



	<?php if (!empty($this->epilog)): ?>
		<div class="grid-box-epilog b-epilog">
			<?php echo $this->epilog; ?>
		</div>
	<?php endif; ?>

	<?php if (!empty($this->readmore)): ?>
		<a href="<?php echo $this->readmoreurl; ?>" class="grid-box-readmore-link b-readmore-link"><?php echo $this->readmore; ?></a>
	<?php endif; ?>
</div>
