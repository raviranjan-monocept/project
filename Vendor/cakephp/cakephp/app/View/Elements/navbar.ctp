<?php
// Read logged-in user from Auth session
$authUser = $this->Session->read('Auth.User');

// Avatar image: DB stores e.g. 'users/user_1_...jpg' or null
$avatarPath = !empty($authUser['image'])
    ? $authUser['image']
    : 'user2-160x160.jpg'; // default avatar in webroot/img/

$displayName = !empty($authUser['full_name']) ? $authUser['full_name'] : 'User';
$displayRole = !empty($authUser['role']) ? ucwords(str_replace('_', ' ', $authUser['role'])) : 'Guest';
$memberSince = !empty($authUser['created']) ? date('M Y', strtotime($authUser['created'])) : 'N/A';
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title_for_layout) ? $title_for_layout : 'Dashboard'; ?> - Care Health Insurance</title>
    <?php echo $this->Html->css('adminlte'); ?>
    <?php echo $this->Html->css('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css'); ?>
    <?php echo $this->Html->css('https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css'); ?>
</head>

<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
        <!--begin::Header-->
        <nav class="app-header navbar navbar-expand bg-body">
            <!--begin::Container-->
            <div class="container-fluid">
                <!--begin::Start Navbar Links-->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                            <i class="bi bi-list"></i>
                        </a>
                    </li>
                    <li class="nav-item d-none d-md-block">
                        <?php echo $this->Html->link('Home', array('controller' => 'users', 'action' => 'dashboard'), array('class' => 'nav-link')); ?>
                    </li>
                    <li class="nav-item d-none d-md-block">
                        <?php echo $this->Html->link('Contact', '#', array('class' => 'nav-link')); ?>
                    </li>
                </ul>
                <!--end::Start Navbar Links-->

                <!--begin::End Navbar Links-->
                <ul class="navbar-nav ms-auto">
                    <!--begin::Navbar Search-->
                    <li class="nav-item">
                        <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                            <i class="bi bi-search"></i>
                        </a>
                    </li>
                    <!--end::Navbar Search-->

                    <!--begin::Messages Dropdown Menu-->
                    <li class="nav-item dropdown">
                        <a class="nav-link" data-bs-toggle="dropdown" href="#">
                            <i class="bi bi-chat-text"></i>
                            <span class="navbar-badge badge text-bg-danger">3</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                            <a href="#" class="dropdown-item">
                                <!--begin::Message-->
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <?php echo $this->Html->image('user1-128x128.jpg', array(
                                            'alt' => 'User Avatar',
                                            'class' => 'img-size-50 rounded-circle me-3'
                                        )); ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h3 class="dropdown-item-title">
                                            Brad Diesel
                                            <span class="float-end fs-7 text-danger">
                                                <i class="bi bi-star-fill"></i>
                                            </span>
                                        </h3>
                                        <p class="fs-7">Call me whenever you can...</p>
                                        <p class="fs-7 text-secondary">
                                            <i class="bi bi-clock-fill me-1"></i> 4 Hours Ago
                                        </p>
                                    </div>
                                </div>
                                <!--end::Message-->
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
                        </div>
                    </li>
                    <!--end::Messages Dropdown Menu-->

                    <!--begin::Notifications Dropdown Menu-->
                    <li class="nav-item dropdown">
                        <a class="nav-link" data-bs-toggle="dropdown" href="#">
                            <i class="bi bi-bell-fill"></i>
                            <span class="navbar-badge badge text-bg-warning">15</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                            <span class="dropdown-item dropdown-header">15 Notifications</span>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <i class="bi bi-envelope me-2"></i> 4 new messages
                                <span class="float-end text-secondary fs-7">3 mins</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <i class="bi bi-people-fill me-2"></i> 8 friend requests
                                <span class="float-end text-secondary fs-7">12 hours</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <i class="bi bi-file-earmark-fill me-2"></i> 3 new reports
                                <span class="float-end text-secondary fs-7">2 days</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
                        </div>
                    </li>
                    <!--end::Notifications Dropdown Menu-->

                    <!--begin::Fullscreen Toggle-->
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-lte-toggle="fullscreen">
                            <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                            <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none"></i>
                        </a>
                    </li>
                    <!--end::Fullscreen Toggle-->

                    <!--begin::User Menu Dropdown-->
                    <li class="nav-item dropdown user-menu">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <?php
                            echo $this->Html->image(
                                $avatarPath,
                                array(
                                    'class' => 'user-image rounded-circle shadow',
                                    'alt'   => 'User Avatar'
                                )
                            );
                            ?>
                            <span class="d-none d-md-inline">Welcome, <?php echo h($displayName); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                            <!--begin::User Image-->
                            <li class="user-header text-bg-primary">
                                <?php
                                echo $this->Html->image(
                                    $avatarPath,
                                    array(
                                        'class' => 'rounded-circle shadow',
                                        'alt'   => 'User Avatar',
                                        'style' => 'width: 90px; height: 90px;'
                                    )
                                );
                                ?>
                                <p>
                                    <span><?php echo h($displayName); ?> (<?php echo h($displayRole); ?>)</span>
                                    <small>Member since <?php echo h($memberSince); ?></small>
                                </p>
                            </li>
                            <!--end::User Image-->

                            <!--begin::Menu Body-->
                            <li class="user-body">
                                <!--begin::Row-->
                                <div class="row">
                                    <div class="col-4 text-center">
                                        <a href="#">Followers</a>
                                    </div>
                                    <div class="col-4 text-center">
                                        <a href="#">Sales</a>
                                    </div>
                                    <div class="col-4 text-center">
                                        <a href="#">Friends</a>
                                    </div>
                                </div>
                                <!--end::Row-->
                            </li>
                            <!--end::Menu Body-->

                            <!--begin::Menu Footer-->
                            <li class="user-footer">
                                <?php echo $this->Html->link('Profile', array('controller' => 'users', 'action' => 'profile'), array('class' => 'btn btn-default btn-flat')); ?>
                                <?php echo $this->Html->link('Logout', array('controller' => 'users', 'action' => 'logout'), array('class' => 'btn btn-default btn-flat float-end')); ?>
                            </li>
                            <!--end::Menu Footer-->
                        </ul>
                    </li>
                    <!--end::User Menu Dropdown-->
                </ul>
                <!--end::End Navbar Links-->
            </div>
            <!--end::Container-->
        </nav>
        <!--end::Header-->
