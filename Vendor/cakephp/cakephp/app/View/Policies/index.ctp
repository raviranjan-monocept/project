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
              <div class="col-sm-6"><h3 class="mb-0">All Policies</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="#">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">All Policies</li>
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
                    <span class="info-box-text">Total Policies</span>
                    <span class="info-box-number">
                       <h3><?php echo $stats['total']; ?></h3>
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
                    <span class="info-box-text">Active Policies</span>
                    <span class="info-box-number"><h3><?php echo $stats['active']; ?></h3>
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
                    <span class="info-box-text"> Draft Policies</span>
                    <span class="info-box-number"> <h3><?php echo $stats['draft']; ?></h3>
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
                    <span class="info-box-text">Archived Policies</span>
                    <span class="info-box-number"> <h3><?php echo $stats['archived']; ?></h3>
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
                            <h3 class="card-title">Policy List</h3>
                            <div class="card-tools">
                                <?php echo count($policies); ?>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Created On</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                 <tbody>
                            <?php foreach ($policies as $policy): ?>
                                <tr>
                                    <td><?php echo h($policy['Policy']['id']); ?></td>
                                    <td>
                                        <strong>
                                            <?php echo $this->Html->link(
                                                h($policy['Policy']['title']),
                                                array('action' => 'view', $policy['Policy']['id'])
                                            ); ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <?php 
                                            $desc = h($policy['Policy']['description']);
                                            echo strlen($desc) > 80 ? substr($desc, 0, 80) . '...' : $desc;
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                            $statusClass = '';
                                            switch($policy['Policy']['status']) {
                                                case 'active':
                                                    $statusClass = 'success';
                                                    break;
                                                case 'draft':
                                                    $statusClass = 'warning';
                                                    break;
                                                case 'archived':
                                                    $statusClass = 'default';
                                                    break;
                                            }
                                        ?>
                                        <span class="label label-<?php echo $statusClass; ?>">
                                            <?php echo strtoupper(h($policy['Policy']['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo $this->Time->format('M d, Y h:i A', $policy['Policy']['created']); ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <?php echo $this->Html->link(
                                                '<i class="fa fa-eye"></i>',
                                                array('action' => 'view', $policy['Policy']['id']),
                                                array(
                                                    'class' => 'btn btn-sm btn-info',
                                                    'escape' => false,
                                                    'title' => 'View'
                                                )
                                            ); ?>
                                            
                                            <?php echo $this->Html->link(
                                                '<i class="fa fa-edit"></i>',
                                                array('action' => 'edit', $policy['Policy']['id']),
                                                array(
                                                    'class' => 'btn btn-sm btn-warning',
                                                    'escape' => false,
                                                    'title' => 'Edit'
                                                )
                                            ); ?>
                                            
                                            <?php echo $this->Form->postLink(
                                                '<i class="fa fa-trash"></i>',
                                                array('action' => 'delete', $policy['Policy']['id']),
                                                array(
                                                    'class' => 'btn btn-sm btn-danger',
                                                    'escape' => false,
                                                    'title' => 'Delete'
                                                ),
                                                __('Are you sure you want to delete policy: %s?', $policy['Policy']['title'])
                                            ); ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
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