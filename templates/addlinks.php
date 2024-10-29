<div class="wrap">

<div class="icon32"><img src='<?php echo plugins_url('/images/icon-32.png', dirname(__FILE__))?>' /></div>

        

<h2><?php echo __('Settings', self::CLASS_NAME)?> <?php echo self::PLUGIN_NAME?>:</h2>





<p><?php _e('Set up the plugin Options at this page',self::CLASS_NAME)?></p>

    

  		<table width="100%"><tr>

        <td style="vertical-align:top">

 

 		<form action="" method="post">

        
				<?php
                 wp_nonce_field('add',self::CLASS_NAME);
				?>
        <div class="metabox-holder">         

		<div class="postbox" >

        

        	<h3><?php _e('Add new Affiliate Links',self::CLASS_NAME);?></h3>

        

        	<div class="inside">


                 <p>

                <label><?php _e('Enter some affiliate url and its respective keyword',self::CLASS_NAME);?>:</label>

                <table>
                <tr>
                <td>
                
                <?php _e('Affiliate url',self::CLASS_NAME)?>: 
                
                </td>
                <td>
				<?php _e('Keyword',self::CLASS_NAME)?>: (<small><?php _e('Just numbers an letters',self::CLASS_NAME)?></small>)
                </td>
                </tr>
                <tr>
             
                <td>
                	<input type="text" name="url_afiliado" class="regular-text" value="<?php if(isset($_POST['url_afiliado'])) echo $_POST['url_afiliado']?>" /> 
                </td>
                
                <td>
                	<input type="text" name="palavra_chave" class="regular-text" value="<?php if(isset($_POST['palavra_chave'])) echo $_POST['palavra_chave']?>" />
                </td>
                </tr>
                
                <tr>
                <td colspan="2">
                <?php _e('Description',self::CLASS_NAME)?>:  <textarea name="descricao" class="large-text code"></textarea>
                </td>
                
                </tr>
                
                </table>

               

                </p> 
                            

                <p>

                <input type="submit" name="submit" value="<?php _e('Add', self::CLASS_NAME);?>" class="button-primary" />

				</p>



			</div>

		</div>

        </div>
 		</form>

          

   		</td>


       </tr>

       </table>

</div>