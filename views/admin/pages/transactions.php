<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<div class="wrap">
    <h1><?php esc_html_e('Transactions', 'ehx-donate') ?></h1>
    <form method="get">
        <?php $table_class->display(); ?>
    </form>
</div>