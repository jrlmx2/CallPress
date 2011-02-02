<?php
global $ninespot_admin;

// build a list of friendlier variables so this html is easier to read.  Bleh.
$new = $this->layout_new == $selected_layout;
$layout_new =& $this->layout_new;
$short_slug =& $this->$selected_layout->short_slug;
$name =& $this->$selected_layout->name;
$default =& $this->layout_default;
$spots =& $this->$selected_layout->spots;
$copy_spots =& $this->$selected_layout->copy_spots;
$is_x_opts =& $this->is_x_opts;
$rules =& $this->$selected_layout->rules;

$page =& $this->layout_page;

?>
<form class="ninespot-block <?php echo $selected_layout; ?>" action="?page=<?php echo $page; ?>&layout=<?php echo $selected_layout; ?>" method="post">
	<table class="form-table">
		<tr valign="top">
			<th scope="row">
				<label for="name">Layout Name</label>
			</th>
			<td>
				<input type="<?php echo ($short_slug == 'default') ? 'hidden' : 'text'; ?>" name="name" value="<?php echo $name; ?>"/><?php if($short_slug == 'default') echo $name; ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="name">Layout Slug</label>
			</th>
			<td>
				<input type="<?php echo ($short_slug == 'default' || $selected_layout == $layout_new) ? 'hidden' : 'text'; ?>" name="slug" value="<?php echo $selected_layout; ?>"/><?php if($short_slug == 'default') echo $default; elseif($selected_layout == $layout_new) echo $new_slug; ?>
			</td>
		</tr>
	</table>
	<table cellspacing="5" cellpadding="0">
		<tr>
			<th colspan="2"></th>
			<th>Column<br/>A</th>
			<th>Column<br/>B</th>
			<th>Column<br/>C</th>
			<th>Column<br/>D</th>
<?php
	if( $selected_layout <> 'nines-layout-default' ):
?>
			<th>Copy settings<br />from layout</th>
<?php
	endif;
?>
		</tr>
<?php foreach($spots as $key => $spot):  ?>
<tr class="row <?php echo $spot['short_id']; ?> <?php echo (empty($spot['size']) || array_sum($spot['size']) == 0) ? 'empty' : ''; ?>">
	<th scope="row" align="left"><?php echo $spot['name']; ?></th>
	<td>
		<div class="tiny_container_16">
			<div id="<?php echo $spot['short_id']; ?>">
				<?php
				$total_slots = 0;
				for($i = 1; $i <= 4; $i++):
					$column_id = $spot['short_id'].'_'.$i;							
												
					$spot['size'][$i] = $this->$selected_layout->blockSize($spot['short_id'], $i, false);

					$total_slots += $spot['size'][$i];
					
					if($total_slots > 16) $spot['size'][$i] = 0;
					?><div class="grid_<?php echo $spot['size'][$i]; ?> block block_<?php echo $i; ?>">	
						<div class="sub-block"><?php echo strtoupper($this->$selected_layout->numberToColumn($i)); ?></div>
					</div><?php 
				endfor; 
				?>
				<div class="clear">&nbsp;</div>
			</div>
		</div>
	</td>
	<?php 
	
	// spit out the columns
	for($i = 1; $i < 5; $i++): 
	
	?>
		<td>
			<select name="<?php echo $spot['short_id'].'['.$i.']'; ?>" class="column-<?php echo $i; ?>">
			<?php 
			
			// spit out the column options
			for($j = 0; $j <= 16; $j++ ): 
			
			?>
				<option value="<?php echo $j; ?>" <?php if($spot['size'][$i] == $j) echo 'selected="selected"'; ?>>
					<?php echo ($j == 0) ? '' : $j; ?>
				</option>
			<?php 
			
			endfor; 
			
			?>
			</select>

			<?php 
			if( $selected_layout <> 'nines-layout-default' && 'body' == $spot['short_id'] )
			{
			?>
				<br /><select name="<?php echo 'copy_spots['. $spot['short_id'] .']['. $i .']'; ?>" class="column-<?php echo $i; ?>">
					<option value="0" <?php selected( $copy_spots[ $spot['short_id'] ][$i], '0' ); ?>></option>
				<?php 
				
				// spit out the column options
				foreach( $ninespot_admin->layouts as $other_layout ):
					if( $other_layout == $selected_layout )
						continue;
				?>
	
					<option value="<?php echo $other_layout; ?>" <?php if( (string) $copy_spots[ $spot['short_id'] ][$i] === (string) $other_layout ) echo 'selected="selected"' ?>>
						<?php echo $other_layout; ?>
					</option>
				<?php 
				
				endforeach; 
				
				?>
				</select>
			<?php
			}
			?>

		</td>
	<?php 
	
	endfor; 
	
	if( $selected_layout <> 'nines-layout-default' && 'body' <> $spot['short_id'] ):
	?>

		<td>
			<select name="<?php echo 'copy_spots['. $spot['short_id'] .']'; ?>" class="column-<?php echo $i; ?>">
				<option value="0" <?php selected( $copy_spots[ $spot['short_id'] ], '0' ); ?>></option>
			<?php 
			
			// spit out the column options
			foreach( $ninespot_admin->layouts as $other_layout ):
				if( $other_layout == $selected_layout )
					continue;
			?>

				<option value="<?php echo $other_layout; ?>" <?php if( (string) $copy_spots[ $spot['short_id'] ] === (string) $other_layout ) echo 'selected="selected"' ?>>
					<?php echo $other_layout; ?>
				</option>
			<?php 
			
			endforeach; 
			
			?>
			</select>
		</td>
	<?php 
	
	endif; 
	
	?>
</tr>
<?php	endforeach; ?>
</table>
<div class="clear">&nbsp;</div>

<?php
if( $selected_layout <> 'nines-layout-default' ):
?>
<ul>
<?php
foreach( $is_x_opts as $is_x_opt )
{
	echo '<li><label for="rule_'. $is_x_opt .'"><input id="rule_'. $is_x_opt .'" name="rule_is_x['. $is_x_opt .']" type="checkbox" value="1" '. ( in_array( $is_x_opt , (array) $rules[ 'is_x' ] ) ? 'checked="checked"' : '' ) .'/> '. $is_x_opt .'</label></li>';
}
?>
<li><label for="rule_post_id">Post IDs: <input type="text" name="rule_post_id" value="<?php echo implode( ',', $rules[ 'id' ] ); ?>" /></label></li>
<li><label for="rule_parent_id">Parent IDs: <input type="text" name="rule_parent_id" value="<?php echo implode( ',', $rules[ 'parent_id' ] ); ?>" /></label></li>
</ul>
<?php
endif;
?>

<p class="submit">
	<input type="submit" name="Submit" class="button-primary" value="Save Changes"/>
	<?php if($selected_layout != $default && $selected_layout != $layout_new): ?>
	<a href="?page=<?php echo $page; ?>&layout=<?php echo $selected_layout; ?>&action=delete" class="delete">Delete Layout</a>
	<?php endif; ?>
</p>
</form>
