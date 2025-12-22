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
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">Policy List</h3>
                  <div class="card-tools">
                    <?php echo $this->Html->link(
                      '<i class="fas fa-plus"></i> Add Policy',
                      array('controller' => 'policies', 'action' => 'add'),
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
                      <th>Title</th>
                      <th>Description</th>
                      <th>Status</th>
                      <th class="text-center">Active</th>
                      <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($policies) && is_array($policies)): ?>
                      <?php foreach ($policies as $policy): ?>
                        <tr>
                          <td><?php echo h($policy['Policy']['id']); ?></td>
                          <td><?php echo h($policy['Policy']['title']); ?></td>
                          <td><?php echo h($policy['Policy']['description']); ?></td>
                          <td><?php echo h($policy['Policy']['status']); ?></td>
                          <td class="text-center">
                            <?php
                            $isActive = ($policy['Policy']['status'] === 'active');
                            echo $this->Html->link(
                              $isActive ? 'Active' : 'Inactive',
                              array('controller' => 'policies', 'action' => 'toggle_status', $policy['Policy']['id']),
                              array('class' => 'btn btn-sm ' . ($isActive ? 'btn-success' : 'btn-secondary'))
                            );
                            ?>
                          </td>
                          <td>
                            <div class="btn-group" role="group" aria-label="policy-actions">
                              <?php echo $this->Html->link(
                                '<i class="bi bi-eye"></i>',
                                array('controller' => 'policies', 'action' => 'view', $policy['Policy']['id']),
                                array('class' => 'btn btn-outline-primary btn-sm', 'escape' => false, 'title' => 'View')
                              ); ?>

                              <?php echo $this->Html->link(
                                '<i class="bi bi-pencil"></i>',
                                array('controller' => 'policies', 'action' => 'edit', $policy['Policy']['id']),
                                array('class' => 'btn btn-outline-warning btn-sm', 'escape' => false, 'title' => 'Edit')
                              ); ?>

                              <?php echo $this->Form->postLink(
                                '<i class="bi bi-trash"></i>',
                                array('controller' => 'policies', 'action' => 'delete', $policy['Policy']['id']),
                                array('class' => 'btn btn-outline-danger btn-sm', 'escape' => false, 'title' => 'Delete'),
                                'Are you sure you want to delete this policy?'
                              ); ?>

                              <?php if ($policy['Policy']['status'] === 'active'): ?>
                                <?php echo $this->Form->postLink(
                                '<i class="bi bi-toggle-on"></i>',
                                array('controller' => 'policies', 'action' => 'toggle_status', $policy['Policy']['id']),
                                array('class' => 'btn btn-outline-danger btn-sm', 'escape' => false, 'title' => 'Deactivate'),
                                'Are you sure you want to deactivate this policy?'
                                ); ?>
                              <?php else: ?>
                                <?php echo $this->Form->postLink(
                                '<i class="bi bi-toggle-off"></i>',
                                array('controller' => 'policies', 'action' => 'toggle_status', $policy['Policy']['id']),
                                array('class' => 'btn btn-outline-success btn-sm', 'escape' => false, 'title' => 'Activate'),
                                'Are you sure you want to activate this policy?'
                                ); ?>
                              <?php endif; ?>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="6" class="text-center">No policies found.</td>
                      </tr>
                    <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>


            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!-- Add/Edit Policy Modal -->
<div class="modal fade" id="policyModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="policyModalTitle">Add Policy</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span>&times;</span>
        </button>
      </div>

      <?php
      echo $this->Form->create('Policy', array(
          'id' => 'policyForm',
          'url' => array('controller' => 'policies', 'action' => 'add')  // default, changed in JS for edit
      ));
      ?>

      <div class="modal-body">
        <div class="form-row">
          <div class="form-group col-md-6">
            <?php echo $this->Form->input('policy_no', array(
                'label' => 'Policy Number',
                'class' => 'form-control'
            )); ?>
          </div>
          <div class="form-group col-md-6">
            <?php echo $this->Form->input('customer_name', array(
                'label' => 'Customer Name',
                'class' => 'form-control'
            )); ?>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-md-6">
            <?php echo $this->Form->input('sum_insured', array(
                'label' => 'Sum Insured',
                'class' => 'form-control'
            )); ?>
          </div>
          <div class="form-group col-md-6">
            <?php echo $this->Form->input('premium_amount', array(
                'label' => 'Premium Amount',
                'class' => 'form-control'
            )); ?>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-md-4">
            <?php echo $this->Form->input('status', array(
                'label' => 'Status',
                'class' => 'form-control',
                'type'  => 'select',
                'options' => array(
                    'draft' => 'Draft',
                    'active' => 'Active',
                    'archived' => 'Archived'
                )
            )); ?>
          </div>
          <div class="form-group col-md-4">
            <?php echo $this->Form->input('start_date', array(
                'label' => 'Start Date',
                'class' => 'form-control',
                'type'  => 'date'
            )); ?>
          </div>
          <div class="form-group col-md-4">
            <?php echo $this->Form->input('end_date', array(
                'label' => 'End Date',
                'class' => 'form-control',
                'type'  => 'date'
            )); ?>
          </div>
        </div>

        <?php echo $this->Form->hidden('id', array('id' => 'policyIdField')); ?>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <?php echo $this->Form->button('Save', array('class' => 'btn btn-primary')); ?>
      </div>

      <?php echo $this->Form->end(); ?>
    </div>
  </div>
</div>

        <!--end::App Content-->
      </main>
      <?php $this->start('script');?>
      <script>
function openPolicyModal(
    id,
    policyNo,
    customerName,
    sumInsured,
    premiumAmount,
    status,
    startDate,
    endDate
) {
    var isEdit = !!id;

    // Set title
    document.getElementById('policyModalTitle').innerText =
        isEdit ? 'Edit Policy' : 'Add Policy';

    // Set form action (add or edit)
    var form = document.getElementById('policyForm');
    if (isEdit) {
        form.action = '<?php echo $this->Html->url(array(
            "controller" => "policies",
            "action" => "edit"
        )); ?>/' + id;
    } else {
        form.action = '<?php echo $this->Html->url(array(
            "controller" => "policies",
            "action" => "add"
        )); ?>';
    }

    // Fill fields
    document.getElementById('policyIdField').value     = id || '';
    document.getElementById('PolicyPolicyNo').value    = policyNo || '';
    document.getElementById('PolicyCustomerName').value= customerName || '';
    document.getElementById('PolicySumInsured').value  = sumInsured || '';
    document.getElementById('PolicyPremiumAmount').value = premiumAmount || '';
    document.getElementById('PolicyStatus').value      = status || 'draft';
    document.getElementById('PolicyStartDate').value   = startDate || '';
    document.getElementById('PolicyEndDate').value     = endDate || '';
}
</script>
<?php $this->end();?>
 <?php echo $this->element('footer');?>