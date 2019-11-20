<?php

$values = ! empty( $instance['fvalues'] ) ? $instance['fvalues'] : array();
$iterator = new ArrayIterator( $values );

do{ ?>
    <div class="homey-clone">
        <div class="toclone">
            <input placeholder="<?php esc_html_e( 'Enter Value', 'homey-core' ); ?>" type="text" name="hz_fbuilder[fvalues][]" value="<?php echo $values[ $iterator->key() ]; ?>"/>
            <a href="#" class="delete"><span class="dashicons dashicons-trash"></span></a>
            <a href="#" class="clone"><span class="dashicons dashicons-plus"></span></a>
        </div>
    </div>

<?php $iterator->next(); } while( $iterator->valid() );