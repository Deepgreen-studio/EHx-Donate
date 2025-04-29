<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <form method="get">
        
        <?php 
            $table_class->search_box('Search Users', 'user_search');
            $table_class->display(); 
        ?>
    </form>
</div>