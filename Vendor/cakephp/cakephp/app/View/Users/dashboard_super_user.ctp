 <?php echo $this->element('navbar'); ?>
      <!--end::Header-->
      <!--begin::Sidebar-->
      <?php echo $this->element('sidebar'); ?>
      <!--end::Sidebar-->
      <!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-sm-6"><h3 class="mb-0">Super Admin Dashboard</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="#">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Super Admin Dashboard</li>
                </ol>
              </div>
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <div class="app-content">
          <!--begin::Container-->
          <div class="container-fluid">
            <!-- Info boxes -->
            <div class="row">
              <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                  <span class="info-box-icon text-bg-primary shadow-sm">
                    <i class="bi bi-gear-fill"></i>
                  </span>
                  <div class="info-box-content">
                    <span class="info-box-text">Total Admins</span>
                    <span class="info-box-number">
                       <h3><?php echo $totalAdmins; ?></h3>
                    </span>
                  </div>
                  <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
              </div>
              <!-- /.col -->
              <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                  <span class="info-box-icon text-bg-danger shadow-sm">
                    <i class="bi bi-hand-thumbs-up-fill"></i>
                  </span>
                  <div class="info-box-content">
                    <span class="info-box-text">Active Admins</span>
                    <span class="info-box-number"><h3><?php echo $activeAdmins; ?></h3>
</span>
                  </div>
                  <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
              </div>
              <!-- /.col -->
              <!-- fix for small devices only -->
              <!-- <div class="clearfix hidden-md-up"></div> -->
              <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                  <span class="info-box-icon text-bg-success shadow-sm">
                    <i class="bi bi-cart-fill"></i>
                  </span>
                  <div class="info-box-content">
                    <span class="info-box-text"> Inactive Admins</span>
                    <span class="info-box-number"> <h3><?php echo $inactiveAdmins; ?></h3>
</span>
                  </div>
                  <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
              </div>
              <!-- /.col -->
              <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                  <span class="info-box-icon text-bg-warning shadow-sm">
                    <i class="bi bi-people-fill"></i>
                  </span>
                  <div class="info-box-content">
                    <span class="info-box-text">Super Admin</span>
                    <span class="info-box-number"> <h3><?php echo $superAdmins; ?></h3>
</span>
                  </div>
                  <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
              </div>
              <!-- /.col -->
            </div>
              <!-- Administrator List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Administrator List</h3>
                            <div class="card-tools">
                                <?php echo $this->Html->link(
                                    '<i class="fas fa-plus"></i> Add Admin',
                                    array('controller' => 'users', 'action' => 'add_admin'),
                                    array('class' => 'btn btn-primary btn-sm', 'escape' => false)
                                ); ?>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Full Name</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Last Login</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($admins)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center">No administrators found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($admins as $admin): ?>
                                            <tr>
                                                <td><?php echo h($admin['User']['id']); ?></td>
                                                <td><?php echo h($admin['User']['full_name']); ?></td>
                                                <td><?php echo h($admin['User']['username']); ?></td>
                                                <td><?php echo h($admin['User']['email']); ?></td>
                                                <td>
                                                  <?php if ($admin['User']['role'] === 'super_user'): ?>
                                                    <span class="text-dark">Super Admin</span>
                                                  <?php else: ?>
                                                    <span class="text-dark">Admin</span>
                                                  <?php endif; ?>
                                                </td>
                                                <td><?php echo h($admin['User']['modified']); ?></td>
                                                <td>
                                                  <?php if ($admin['User']['status'] == 1): ?>
                                                    <span class="text-success">Active</span>
                                                  <?php else: ?>
                                                    <span class="text-danger">Inactive</span>
                                                  <?php endif; ?>
                                                </td>
                                                <td>
                                                  <div class="btn-group" role="group" aria-label="admin-actions">
                                                    <?php echo $this->Html->link(
                                                      '<i class="bi bi-eye"></i>',
                                                      array('controller' => 'users', 'action' => 'view', $admin['User']['id']),
                                                      array('class' => 'btn btn-outline-primary btn-sm', 'escape' => false, 'title' => 'View')
                                                    ); ?>

                                                    <?php echo $this->Html->link(
                                                      '<i class="bi bi-pencil"></i>',
                                                      array('controller' => 'users', 'action' => 'edit', $admin['User']['id']),
                                                      array('class' => 'btn btn-outline-warning btn-sm', 'escape' => false, 'title' => 'Edit')
                                                    ); ?>

                                                    <?php if ($admin['User']['id'] != $this->Session->read('Auth.User.id')): ?>
                                                      <?php echo $this->Form->postLink(
                                                        '<i class="bi bi-trash"></i>',
                                                        array('controller' => 'users', 'action' => 'delete', $admin['User']['id']),
                                                        array('class' => 'btn btn-outline-danger btn-sm', 'escape' => false, 'title' => 'Delete'),
                                                        'Are you sure you want to delete this administrator?'
                                                      ); ?>
                                                    <?php endif; ?>

                                                    <?php if ($admin['User']['status'] == 1): ?>
                                                      <?php echo $this->Form->postLink(
                                                        '<i class="bi bi-toggle-on"></i>',
                                                        array('controller' => 'users', 'action' => 'toggle_status', $admin['User']['id']),
                                                        array('class' => 'btn btn-outline-danger btn-sm', 'escape' => false, 'title' => 'Deactivate'),
                                                        'Are you sure you want to deactivate this administrator?'
                                                      ); ?>
                                                    <?php else: ?>
                                                      <?php echo $this->Form->postLink(
                                                        '<i class="bi bi-toggle-off"></i>',
                                                        array('controller' => 'users', 'action' => 'toggle_status', $admin['User']['id']),
                                                        array('class' => 'btn btn-outline-success btn-sm', 'escape' => false, 'title' => 'Activate'),
                                                        'Are you sure you want to activate this administrator?'
                                                      ); ?>
                                                    <?php endif; ?>
                                                  </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                
                    </div>
         
                </div>
            </div>
            </div>

        </div>
        <!--end::App Content-->
      </main>
      <!--end::App Main-->
  <?php echo $this->element('footer'); ?>