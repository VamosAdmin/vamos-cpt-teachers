<?php

if ($showText=='excerpt'){
  $showContent='"'.$aTeacher->post_excerpt.'"';
} else {
  $showContent='"'.$aTeacher->post_content.'"';
}
$col='';
if( $aTeacher->imageURL) {
	$col="gdlr-core-column-45";
}
$postLink1='<a href="'.get_post_permalink($aTeacher->ID).'">';
$postLink2='</a>';
?>
<div id="teacher-<?php echo $aTeacher->ID;?>">
	<div class="course-teacher-content <?php echo $col;?>"><div class="gdlr-core-pbf-column-content-margin">
		<h3 class="course-teacher-title">Your Teacher</h3>
		<div class="course-teacher-name">
			<?php echo $postLink1;?>
			<?php echo $aTeacher->post_title;?>
			<?php echo $postLink2;?>
		</div>
		<div class="course-teacher-excerpt">
			<?php echo wpautop($showContent); ?>
		</div>
	</div></div>
	<?php if( $aTeacher->imageURL) : ?>
	<div class="course-teacher-image gdlr-core-column-15"><div class="gdlr-core-pbf-column-content-margin">
        <?php echo $postLink1;?>
        <div class="imgframe" style="text-align:center;"><img class="clsCPTImg" alt="" src="<?php echo $aTeacher->imageURL;?>" ></div>
        <?php echo $postLink2;?>
         <br />
    </div></div>
	<?php endif; ?>
</div>
