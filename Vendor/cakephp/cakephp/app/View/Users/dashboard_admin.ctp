<?php echo $this->element('navbar');?>
      <!--end::Header-->
      <!--begin::Sidebar-->
     <?php echo $this->element('sidebar');?>
      <!--end::Sidebar-->
      <!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-sm-6"><h3 class="mb-0">Admin Dashboard</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="#">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Admin Dashboard</li>
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
                </div>
                <!-- /.info-box -->
              </div>
              <!-- /.col -->
            </div>
            <!-- /.row -->
            <!--begin::Row-->
           
            <!--end::Row-->
            <!--begin::Row-->
        <h3 class="d-flex justify-content-between align-items-center">
    <span>Policy List</span>
    <a href="<?php echo $this->Html->url(array(
        'controller' => 'policies',
        'action' => 'add'
    )); ?>" class="btn btn-sm btn-primary">
        + Add New Policy
    </a>
</h3>

<?php if (!empty($policies) && is_array($policies)): ?>
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th>id</th>
            <th>title</th>
            <th>Description</th>
            <th>Status</th>
            <th class="text-center">Active</th>
            <th class="text-end">Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($policies as $policy): ?>
            <tr>
                <td><?php echo h($policy['Policy']['id']); ?></td>
                <td><?php echo h($policy['Policy']['title']); ?></td>
                 <td><?php echo h($policy['Policy']['description']); ?></td>
                <td><?php echo h($policy['Policy']['status']); ?></td>
               

                <!-- Toggle active/deactive (example uses status = active/inactive) -->
                <td class="text-center">
                    <?php
                    $isActive = ($policy['Policy']['status'] === 'active');
                    echo $this->Html->link(
                        $isActive ? 'Active' : 'Inactive',
                        array(
                            'controller' => 'policies',
                            'action' => 'toggle_status',   // create this in PoliciesController
                            $policy['Policy']['id']
                        ),
                        array(
                            'class' => 'btn btn-xs ' . ($isActive ? 'btn-success' : 'btn-secondary')
                        )
                    );
                    ?>
                </td>

                <!-- Action buttons -->
                <td class="text-end">
                    <?php
                    echo $this->Html->link(
                        'View',
                        array('controller' => 'policies', 'action' => 'view', $policy['Policy']['id']),
                        array('class' => 'btn btn-xs btn-info')
                    );

                    echo ' ' . $this->Html->link(
                        'Edit',
                        array('controller' => 'policies', 'action' => 'edit', $policy['Policy']['id']),
                        array('class' => 'btn btn-xs btn-warning')
                    );

                    echo ' ' . $this->Form->postLink(
                        'Delete',
                        array('controller' => 'policies', 'action' => 'delete', $policy['Policy']['id']),
                        array(
                            'class' => 'btn btn-xs btn-danger',
                            'confirm' => 'Are you sure you want to delete this policy?'
                        )
                    );
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No policies found.</p>
<?php endif; ?>


            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content-->
      </main>
 <?php echo $this->element('footer');?>