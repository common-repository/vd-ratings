<div class="wrap">
    <h2><?php echo esc_html__('VD Ratings setting','vd_rate'); ?></h2>
    <form method="post" action="options.php"> 
        <?php @settings_fields('wp_plugin_template-group'); ?>
        <?php @do_settings_fields('wp_plugin_template-group'); ?>		
        <table class="form-table">  
            <tr valign="top">
                <th scope="row"><label for="_vd_user_login"><?php echo __('Only registered user can rate','vd_rate'); ?></label></th>
                <td>
					<input type="checkbox"  name="_vd_user_login"  id="_vd_user_login" value="1" <?php checked(1,get_option('_vd_user_login')); ?> />
					</td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="select_color_txt"><?php echo __('Rating Color:','vd_rate'); ?></label></th>
                <td>					
					<select class="select_color" id="select_color" onchange="get_val(this.value);">
						<option value="#ff0000" <?php echo ((get_option('_vd_rate_color') == '#ff0000')? 'selected' : ''); ?>><?php echo __('Red','vd_rate'); ?></option>
						<option value="#0000ff" <?php echo ((get_option('_vd_rate_color') == '#0000ff')? 'selected' : ''); ?>><?php echo __('Blue','vd_rate'); ?></option>
						<option value="#00ff00" <?php echo ((get_option('_vd_rate_color') == '#00ff00')? 'selected' : ''); ?>><?php echo __('Green','vd_rate'); ?></option>
						<option value="#ffff00" <?php echo ((get_option('_vd_rate_color') == '#ffff00')? 'selected' : ''); ?>><?php echo __('Yellow','vd_rate'); ?></option>
						<option value="#FF5910" <?php echo ((get_option('_vd_rate_color') == '#FF5910')? 'selected' : ''); ?>><?php echo __('Orange','vd_rate'); ?></option>
					</select>
					<input type="text" name="_vd_rate_color" id="_vd_rate_color" value="<?php echo esc_attr( get_option('_vd_rate_color') ); ?>" />&nbsp;&nbsp;&nbsp;&nbsp;<?php echo esc_html__('you can add hexacode directly here.','vd_rate'); ?>
				</td>
            </tr>
        </table>
        <?php @submit_button(); ?>
    </form>
</div>