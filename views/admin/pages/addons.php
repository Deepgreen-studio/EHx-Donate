<?php
    use EHxDonate\Addons\ManageAddons;
    $addons = ManageAddons::getAvailableAddons();
    $installed_addons = ManageAddons::getInstalledAddons();
?>

<div class="wrap edp-addons-wrap">
    <h1 class="edp-addons-title">
        <?php esc_html_e('EHx Donate Add-ons', 'ehx-donate'); ?>
        <span class="title-count"><?php echo count($addons); ?></span>
    </h1>

    <div class="edp-addons-filters">
        <!-- <div class="search-box">
            <input type="search" id="edp-addons-search" placeholder="<?php esc_attr_e('Search add-ons...', 'ehx-donate'); ?>">
        </div> -->
        <ul class="filter-links">
            <li><a href="#all" class="current"><?php esc_html_e('All', 'ehx-donate'); ?></a></li>
            <li><a href="#active"><?php esc_html_e('Active', 'ehx-donate'); ?></a></li>
            <li><a href="#premium"><?php esc_html_e('Premium', 'ehx-donate'); ?></a></li>
            <li><a href="#free"><?php esc_html_e('Free', 'ehx-donate'); ?></a></li>
        </ul>
    </div>

    <div class="edp-addons-grid wp-clearfix">
        <?php foreach ($addons as $slug => $addon) : 
            $is_installed = array_key_exists($slug, $installed_addons);
            $is_active = $is_installed && $installed_addons[$slug]['active'];
        ?>
            <div class="edp-addon-card <?php echo $addon['premium'] ? 'premium' : 'free'; ?> <?php echo $is_active ? 'active' : ''; ?>">
                
                <div class="addon-header">
                    <img src="<?php echo esc_url($addon['icon']); ?>" class="addon-icon">
                    <h3><?php echo esc_html($addon['name']); ?></h3>
                    <?php if ($addon['premium']) : ?>
                        <span class="premium-badge"><?php esc_html_e('Premium', 'ehx-donate'); ?></span>
                    <?php endif; ?>
                </div>

                <div class="addon-body">
                    <div class="addon-description">
                        <?php echo wp_kses_post($addon['description']); ?>
                    </div>
                    
                    <div class="addon-meta">
                        <span class="version"><?php printf(esc_html__('Version: %s', 'ehx-donate'), $addon['version']); ?></span>
                        <span class="updated"><?php printf(esc_html__('Updated: %s', 'ehx-donate'), date_i18n(get_option('date_format'), strtotime($addon['updated']))); ?></span>
                    </div>
                </div>

                <div class="addon-footer">
                    <?php if ($addon['premium'] && !$is_installed) : ?>
                        <div class="license-field">
                            <input type="text" class="license-key" placeholder="<?php esc_attr_e('Enter license key', 'ehx-donate'); ?>">
                            <button class="button button-primary activate-license" data-addon="<?php echo esc_attr($slug); ?>">
                                <?php esc_html_e('Activate', 'ehx-donate'); ?>
                            </button>
                        </div>
                    <?php elseif ($is_installed) : ?>
                        <?php if ($is_active) : ?>
                            <button class="button deactivate-addon" data-addon="<?php echo esc_attr($slug); ?>">
                                <?php esc_html_e('Deactivate', 'ehx-donate'); ?>
                            </button>
                        <?php else : ?>
                            <button class="button activate-addon" data-addon="<?php echo esc_attr($slug); ?>">
                                <?php esc_html_e('Activate', 'ehx-donate'); ?>
                            </button>
                        <?php endif; ?>
                        <span class="installed-badge"><?php esc_html_e('Installed', 'ehx-donate'); ?></span>
                    <?php else : ?>
                        <button class="button button-primary install-addon" data-addon="<?php echo esc_attr($slug); ?>">
                            <?php esc_html_e('Install', 'ehx-donate'); ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    </div>