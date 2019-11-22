<?php
global $homey_local;
$nav_login = homey_option('nav_login');
$nav_register = homey_option('nav_register');
$become_host_btn = homey_option('become_host_btn');
$become_host_link = homey_option('become_host_link');
$become_host_label = homey_option('become_host_label');

if($nav_login || $nav_register || $become_host_btn) { ?>
<nav id="user-nav" class="nav-dropdown main-nav-dropdown collapse navbar-collapse">
    <ul>

        <?php if($nav_login) { ?>
        <li>
            <a href="#" data-toggle="modal" data-target="#modal-login">
                <span data-toggle="collapse" data-target="#user-nav"><?php echo esc_attr($homey_local['login_text']); ?></span>
            </a>
        </li>
        <?php } ?>

        <?php if($nav_register) { ?>
        <li>
            <a href="#" data-toggle="modal" data-target="#modal-register">
                <span data-toggle="collapse" data-target="#user-nav"><?php echo esc_attr($homey_local['register_text']); ?></span>
            </a>
        </li>
        <?php } ?>

        <?php if($become_host_btn) { ?>
        <li><a href="<?php echo get_permalink($become_host_link); ?>"><?php echo esc_html($become_host_label); ?></a></li>
        <?php } ?>

        </ul>
</nav><!-- nav-collapse -->
<?php } ?>