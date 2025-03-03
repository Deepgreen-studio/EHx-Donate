<div class="wrap">
    <h1><?php esc_html_e('Members', 'ehx-donate') ?></h1>
    <form method="get">
        
        <?php 
            $custom_table->search_box('Search Users', 'user_search');
            $custom_table->display(); 
        ?>
    </form>
</div>